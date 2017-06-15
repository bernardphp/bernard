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
    /**
     * @var string
     */
    private $baseDirectory;

    /**
     * @var integer
     */
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
        return iterator_count($this->getJobIterator($queueName));
    }

    /**
     * {@inheritdoc}
     */
    public function pushMessage($queueName, $message)
    {
        $queueDir = $this->getQueueDirectory($queueName);

        $filename = $this->getJobFilename($queueName);

        file_put_contents($queueDir.DIRECTORY_SEPARATOR.$filename, $message);
        chmod($queueDir.DIRECTORY_SEPARATOR.$filename, $this->permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function popMessage($queueName, $duration = 5)
    {
        $runtime = microtime(true) + $duration;
        $queueDir = $this->getQueueDirectory($queueName);
        $files = $this->getJobFiles($queueName);

        natsort($files);

        while (microtime(true) < $runtime) {
            if ($files) {
                $id = array_shift($files);
                $data = array(file_get_contents($queueDir.DIRECTORY_SEPARATOR.$id), $id);
                // Set file hidden (emulating message invisibility)
                rename($queueDir.DIRECTORY_SEPARATOR.$id, $queueDir.DIRECTORY_SEPARATOR.'.'.$id);
                return $data;
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
        // Set path to hidden filename
        $path = $queueDir.DIRECTORY_SEPARATOR.'.'.$receipt;

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

        $files = $this->getJobFiles($queueName);

        natsort($files);

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
        $this->removeDirectoryRecursive($this->getQueueDirectory($queueName));
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

    /**
     * Creates an iterator of all message files in the queue
     * @param string $queueName
     * @return \GlobIterator
     */
    private function getJobIterator($queueName) {
        $queueDir = $this->getQueueDirectory($queueName);
        $iterator = new \GlobIterator($queueDir.DIRECTORY_SEPARATOR.'*.job', \FilesystemIterator::KEY_AS_FILENAME);
        return $iterator;
    }

    /**
     * Retrieves an array of all message files in the queue
     * @param string $queueName
     * @return array
     */
    private function getJobFiles($queueName) {
        $iterator = $this->getJobIterator($queueName);
        $files = array_keys(iterator_to_array($iterator));
        return $files;
    }

    /**
     * Removes a directory recursively
     * @param string $directory
     */
    private function removeDirectoryRecursive($directory)
    {
        foreach (glob("{$directory}/{,.}[!.,!..]*", GLOB_MARK|GLOB_BRACE) as $file)
        {
            if (is_dir($file)) {
                $this->removeDirectoryRecursive($file);
            } else {
                unlink($file);
            }
        }
        rmdir($directory);
    }
}
