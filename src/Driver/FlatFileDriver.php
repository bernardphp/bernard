<?php

namespace Bernard\Driver;

/**
 * Flat file driver to provide a simple job queue without any
 * database.
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class FlatFileDriver implements \Bernard\Driver
{
    private $baseDirectory;

    private $permissions;

    /**
     * @param string $baseDirectory The base directory
     * @param int    $permissions   Permissions to create the file with.
     */
    public function __construct($baseDirectory, $permissions = 0740)
    {
        $this->baseDirectory = $baseDirectory;
        $this->permissions = $permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function listQueues()
    {
        $it = new \FilesystemIterator($this->baseDirectory, \FilesystemIterator::SKIP_DOTS);

        $queues = [];

        foreach ($it as $file) {
            if (!$file->isDir()) {
                continue;
            }

            array_push($queues, $file->getBasename());
        }

        return $queues;
    }

    /**
     * {@inheritdoc}
     */
    public function createQueue($queueName)
    {
        $queueDir = $this->getQueueDirectory($queueName);

        if (is_dir($queueDir)) {
            return;
        }

        mkdir($queueDir, 0755, true);
    }

    /**
     * {@inheritdoc}
     */
    public function countMessages($queueName)
    {
        $iterator = new \RecursiveDirectoryIterator(
            $this->getQueueDirectory($queueName),
            \FilesystemIterator::SKIP_DOTS
        );
        $iterator = new \RecursiveIteratorIterator($iterator);
        $iterator = new \RegexIterator($iterator, '#\.job$#');

        return iterator_count($iterator);
    }

    /**
     * {@inheritdoc}
     */
    public function pushMessage($queueName, $message)
    {
        $queueDir = $this->getQueueDirectory($queueName);

        $filename = $this->getJobFilename($queueName);

        file_put_contents($queueDir.DIRECTORY_SEPARATOR.$filename, $message);
        chmod($queueDir . DIRECTORY_SEPARATOR . $filename, $this->permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function popMessage($queueName, $duration = 5)
    {
        $runtime = microtime(true) + $duration;
        $queueDir = $this->getQueueDirectory($queueName);

        $it = new \GlobIterator($queueDir.DIRECTORY_SEPARATOR.'*.job', \FilesystemIterator::KEY_AS_FILENAME);
        $files = array_keys(iterator_to_array($it));

        natsort($files);

        while (microtime(true) < $runtime) {
            if ($files) {
                $id = array_pop($files);
                if (@rename($queueDir.DIRECTORY_SEPARATOR.$id, $queueDir.DIRECTORY_SEPARATOR.$id.'.proceed')) {
                    return array(file_get_contents($queueDir.DIRECTORY_SEPARATOR.$id.'.proceed'), $id);
                }
            }

            usleep(1000);
        }

        return array(null, null);
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $queueDir = $this->getQueueDirectory($queueName);
        $path = $queueDir.DIRECTORY_SEPARATOR.$receipt.'.proceed';

        if (!is_file($path)) {
            return;
        }

        unlink($path);
    }

    /**
     * {@inheritdoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        $queueDir = $this->getQueueDirectory($queueName);

        $it = new \GlobIterator($queueDir.DIRECTORY_SEPARATOR.'*.job', \FilesystemIterator::KEY_AS_FILENAME);
        $files = array_keys(iterator_to_array($it));

        natsort($files);
        $files = array_reverse($files);

        $files = array_slice($files, $index, $limit);

        $messages = [];

        foreach ($files as $file) {
            array_push($messages, file_get_contents($queueDir.DIRECTORY_SEPARATOR.$file));
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function removeQueue($queueName)
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

    /**
     * {@inheritdoc}
     */
    public function info()
    {
        return [];
    }

    /**
     * @param string $queueName
     *
     * @return string
     */
    private function getQueueDirectory($queueName)
    {
        return $this->baseDirectory.DIRECTORY_SEPARATOR.str_replace(array('\\', '.'), '-', $queueName);
    }

    /**
     * Generates a uuid.
     *
     * @param string $queueName
     *
     * @return string
     */
    private function getJobFilename($queueName)
    {
        $path = $this->baseDirectory.'/bernard.meta';

        if (!is_file($path)) {
            touch($path);
            chmod($path, $this->permissions);
        }

        $file = new \SplFileObject($path, 'r+');
        $file->flock(LOCK_EX);

        $meta = unserialize($file->fgets());

        $id = isset($meta[$queueName]) ? $meta[$queueName] : 0;
        $id++;

        $filename = sprintf('%d.job', $id);
        $meta[$queueName] = $id;

        $content = serialize($meta);

        $file->fseek(0);
        $file->fwrite($content, strlen($content));
        $file->flock(LOCK_UN);

        return $filename;
    }
}
