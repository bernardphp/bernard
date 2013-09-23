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
    private $baseDirectory;

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
        $it = new \DirectoryIterator($this->baseDirectory);

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

        mkdir($queueName, 0664, true);
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

        $filename = $this->getJobFilename();

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

        unlink($this->getQueueDirectory($queueName));
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
     * Generates a v4 GUID.
     *
     * Copied from https://github.com/doctrine/oxm/blob/master/lib/Doctrine/OXM/Id/UuidGenerator.php
     *
     * @return string
     */
    private function getJobFilename()
    {
        return sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)).'.job';
    }
}