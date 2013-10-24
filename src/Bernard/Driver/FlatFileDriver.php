<?php

namespace Bernard\Driver;

use Bernard\Driver;

/**
 * Flat file driver to provide a simple job queue without any
 * database.
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class FlatFileDriver implements Driver
{
    /**
     * @var string
     */
    private $baseDirectory;

    /**
     * Constructor
     *
     * @param string $baseDirectory The base directory
     */
    public function __construct($baseDirectory)
    {
        $this->baseDirectory = $baseDirectory;
    }

    /**
     * Returns a list of all queue names.
     *
     * @return array
     */
    public function listQueues()
    {
        $it = new \DirectoryIterator($this->baseDirectory, \FilesystemIterator::SKIP_DOTS);

        $queues = array();

        foreach ($it as $file) {
            /** @var $file \SplFileInfo */
            if (!$file->isDir()) {
                continue;
            }

            array_push($queues, $file->getBasename());
        }

        return $queues;
    }

    /**
     * Create a queue.
     *
     * @param string $queueName
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
     * Count the number of messages in queue. This can be a approximately number.
     *
     * @return integer
     */
    public function countMessages($queueName)
    {
        $iterator = new \RecursiveDirectoryIterator($this->getQueueDirectory($queueName), \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($iterator);
        $iterator = new \RegexIterator($iterator, '#\.job$#');

        return iterator_count($iterator);
    }

    /**
     * Insert a message at the top of the queue.
     *
     * @param string $queueName
     * @param string $message
     */
    public function pushMessage($queueName, $message)
    {
        $queueDir = $this->getQueueDirectory($queueName);

        $filename = $this->getJobFilename($queueName);

        file_put_contents($queueDir.DIRECTORY_SEPARATOR.$filename, $message);
    }

    /**
     * Remove the next message in line. And if no message is available
     * wait $interval seconds.
     *
     * @param  string  $queueName
     * @param  integer $interval
     * @return array   An array like array($message, $receipt);
     */
    public function popMessage($queueName, $interval = 5)
    {
        $runtime = microtime(true) + $interval;
        $queueDir = $this->getQueueDirectory($queueName);

        $it = new \GlobIterator($queueDir.DIRECTORY_SEPARATOR.'*.job', \FilesystemIterator::KEY_AS_FILENAME);
        $files = array_keys(iterator_to_array($it));

        natsort($files);

        while (microtime(true) < $runtime) {
            if ($files) {
                $id = array_pop($files);
                $data = array(file_get_contents($queueDir.DIRECTORY_SEPARATOR.$id), $id);
                rename($queueDir.DIRECTORY_SEPARATOR.$id, $queueDir.DIRECTORY_SEPARATOR.$id.'.proceed');

                return $data;
            }

            usleep(1000);
        }

        return array(null, null);
    }

    /**
     * If the driver supports it, this will be called when a message
     * have been consumed.
     *
     * @param string $queueName
     * @param mixed  $receipt
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $queueDir = $this->getQueueDirectory($queueName);
        $path = $queueDir.DIRECTORY_SEPARATOR.$receipt;

        if (!is_file($path)) {
            return;
        }

        unlink($path);
    }

    /**
     * Returns a $limit numbers of messages without removing them
     * from the queue.
     *
     * @param string  $queueName
     * @param integer $index
     * @param integer $limit
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        $queueDir = $this->getQueueDirectory($queueName);

        $it = new \GlobIterator($queueDir.DIRECTORY_SEPARATOR.'*.job', \FilesystemIterator::KEY_AS_FILENAME);
        $files = array_keys(iterator_to_array($it));

        natsort($files);
        $files = array_reverse($files);

        $files = array_slice($files, $index, $limit);

        $messages = array();

        foreach ($files as $file) {
            array_push($messages, file_get_contents($queueDir.DIRECTORY_SEPARATOR.$file));
        }

        return $messages;
    }

    /**
     * Removes the queue.
     *
     * @param string $queueName
     */
    public function removeQueue($queueName)
    {
        $iterator = new \RecursiveDirectoryIterator($this->getQueueDirectory($queueName), \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($iterator);
        $iterator = new \RegexIterator($iterator, '#\.job$#');

        foreach ($iterator as $file) {
            /** @var $file \SplFileInfo */
            unlink($file->getRealPath());
        }

        rmdir($this->getQueueDirectory($queueName));
    }

    /**
     * @return array
     */
    public function info()
    {
    }

    private function getQueueDirectory($queueName)
    {
        return $this->baseDirectory.DIRECTORY_SEPARATOR.str_replace(array('\\', '.'), '-', $queueName);
    }

    /**
     * Generates a uuid.
     *
     * @return string
     */
    private function getJobFilename($queueName)
    {
        $path = $this->baseDirectory.'/bernard.meta';
        $meta = array();

        if (!is_file($path)) {
            touch($path);
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