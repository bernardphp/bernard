<?php

declare(strict_types=1);

namespace Bernard\Driver\FlatFile;

use Bernard\Driver\Message;

/**
 * Flat file driver to provide a simple job queue without any
 * database.
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
final class Driver implements \Bernard\Driver
{
    private string $baseDirectory;

    private int $permissions;

    public function __construct(string $baseDirectory, int $permissions = 0740)
    {
        $this->baseDirectory = $baseDirectory;
        $this->permissions = $permissions;
    }

    public function listQueues(): array
    {
        $it = new \FilesystemIterator($this->baseDirectory, \FilesystemIterator::SKIP_DOTS);

        $queues = [];

        foreach ($it as $file) {
            if (!$file->isDir()) {
                continue;
            }

            $queues[] = $file->getBasename();
        }

        return $queues;
    }

    public function createQueue(string $queueName): void
    {
        $queueDir = $this->getQueueDirectory($queueName);

        if (is_dir($queueDir)) {
            return;
        }

        mkdir($queueDir, 0755, true);
    }

    public function removeQueue(string $queueName): void
    {
        $iterator = new \RecursiveDirectoryIterator(
            $this->getQueueDirectory($queueName),
            \FilesystemIterator::SKIP_DOTS
        );
        $iterator = new \RecursiveIteratorIterator($iterator);
        $iterator = new \RegexIterator($iterator, '#\.job(.proceed)?$#');

        foreach ($iterator as $file) {
            /* @var $file \DirectoryIterator */
            unlink($file->getRealPath());
        }

        rmdir($this->getQueueDirectory($queueName));
    }

    public function pushMessage(string $queueName, string $message): void
    {
        $queueDir = $this->getQueueDirectory($queueName);

        $filename = $this->getJobFilename($queueName);

        file_put_contents($queueDir.\DIRECTORY_SEPARATOR.$filename, $message);
        chmod($queueDir.\DIRECTORY_SEPARATOR.$filename, $this->permissions);
    }

    public function popMessage(string $queueName, int $duration = 5): ?Message
    {
        $runtime = microtime(true) + $duration;
        $queueDir = $this->getQueueDirectory($queueName);

        $files = $this->getJobFiles($queueName);

        while (microtime(true) < $runtime) {
            if ($files) {
                $id = array_pop($files);
                if (@rename($queueDir.\DIRECTORY_SEPARATOR.$id, $queueDir.\DIRECTORY_SEPARATOR.$id.'.proceed')) {
                    return new Message(file_get_contents($queueDir.\DIRECTORY_SEPARATOR.$id.'.proceed'), $id);
                }

                return $this->processFileOrFail($queueDir, $id);
            } else {
                // In order to notice that a new message received, update the list.
                $files = $this->getJobFiles($queueName);
            }

            usleep(1000);
        }
    }

    private function processFileOrFail(string $queueDir, string $id): Message
    {
        $name = $queueDir.\DIRECTORY_SEPARATOR.$id;
        $newName = $name.'.proceed';

        if (!@rename($name, $newName)) {
            throw new InsufficientPermissionsException('Unable to process file: '.$name);
        }

        return new Message(file_get_contents($newName), $id);
    }

    public function acknowledgeMessage(string $queueName, mixed $receipt): void
    {
        $queueDir = $this->getQueueDirectory($queueName);
        $path = $queueDir.\DIRECTORY_SEPARATOR.$receipt.'.proceed';

        if (!is_file($path)) {
            return;
        }

        unlink($path);
    }

    public function info(): array
    {
        return [];
    }

    public function countMessages(string $queueName): int
    {
        $iterator = new \RecursiveDirectoryIterator(
            $this->getQueueDirectory($queueName),
            \FilesystemIterator::SKIP_DOTS
        );
        $iterator = new \RecursiveIteratorIterator($iterator);
        $iterator = new \RegexIterator($iterator, '#\.job$#');

        return iterator_count($iterator);
    }

    public function peekQueue(string $queueName, int $index = 0, int $limit = 20): array
    {
        $queueDir = $this->getQueueDirectory($queueName);

        $it = new \GlobIterator($queueDir.\DIRECTORY_SEPARATOR.'*.job', \FilesystemIterator::KEY_AS_FILENAME);
        $files = array_keys(iterator_to_array($it));

        natsort($files);
        $files = array_reverse($files);

        $files = \array_slice($files, $index, $limit);

        $messages = [];

        foreach ($files as $file) {
            $messages[] = file_get_contents($queueDir.\DIRECTORY_SEPARATOR.$file);
        }

        return $messages;
    }

    private function getQueueDirectory(string $queueName): string
    {
        return $this->baseDirectory.\DIRECTORY_SEPARATOR.str_replace(['\\', '.'], '-', $queueName);
    }

    private function getJobFilename(string $queueName): string
    {
        $path = $this->baseDirectory.'/bernard.meta';

        if (!is_file($path)) {
            touch($path);
            chmod($path, $this->permissions);
        }

        $file = new \SplFileObject($path, 'r+');
        $file->flock(\LOCK_EX);

        $meta = unserialize($file->fgets());

        $id = $meta[$queueName] ?? 0;
        ++$id;

        $filename = sprintf('%d.job', $id);
        $meta[$queueName] = $id;

        $content = serialize($meta);

        $file->fseek(0);
        $file->fwrite($content, \strlen($content));
        $file->flock(\LOCK_UN);

        return $filename;
    }

    private function getJobFiles(string $queueName): array
    {
        $it = new \GlobIterator(
            $this->getQueueDirectory($queueName).\DIRECTORY_SEPARATOR.'*.job',
            \FilesystemIterator::KEY_AS_FILENAME
        );
        $files = array_keys(iterator_to_array($it));
        natsort($files);

        return $files;
    }
}
