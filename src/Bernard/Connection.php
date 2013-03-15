<?php

namespace Bernard;

/**
 * @package Bernard
 */
interface Connection
{
    /**
     * @param  string  $set
     * @return integer
     */
    public function count($set);

    /**
     * @param  string  $set
     * @return mixed[]
     */
    public function all($set);

    /**
     * @param  string  $set
     * @param  integer $index
     * @param  integer $limit
     * @return mixed[]
     */
    public function slice($set, $index = 0, $limit = 20);

    /**
     * @param  string $set
     * @return mixed
     */
    public function get($set);

    /**
     * @param  string     $set
     * @param  integer    $interval
     * @return mixed|null
     */
    public function pop($set, $interval = 5);

    /**
     * @param string $set
     * @param mixed  $member
     */
    public function push($set, $member);

    /**
     * @param  string  $set
     * @param  mixed   $member
     * @return boolean
     */
    public function contains($set, $member);

    /**
     * @param string $set
     */
    public function delete($set);

    /**
     * @param string $set
     * @param mixed  $member
     */
    public function insert($set, $member);

    /**
     * @param string $set
     * @param mixed  $member
     */
    public function remove($set, $member);

    /**
     * @return array
     */
    public function info();
}
