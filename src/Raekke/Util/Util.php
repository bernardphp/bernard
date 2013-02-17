<?php

namespace Raekke\Util;

/**
 * Utility functions
 *
 * @author Benjamin Eberlei
 */
class Util
{
    /**
     * Create a now date with microseconds precision.
     *
     * By default DateTime does not create times with microseconds, this
     * workaround does, and also prevents certain rounding/typing failures.
     *
     * @link http://stackoverflow.com/questions/169428/php-datetime-microseconds-always-returns-0#comment12220584_6604836
     */
    static public function createMicrosecondsNow()
    {
        return date_create_from_format('U.u', sprintf('%.f', microtime(true)));
    }

    /**
     * Generate a UUID v4
     *
     * @link http://en.wikipedia.org/wiki/Universally_unique_identifier#Version_4_.28random.29
     * @link http://www.php.net/manual/en/function.uniqid.php#94959
     * @return string
     */
    static public function generateUuid()
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
}

