<?php

namespace Raekke\Message;

use Raekke\Util\Util;
use JMS\Serializer\Annotation\Type;

/**
 * @author Benjamin Eberlei
 */
class MessageHeader
{
    private $id;
    private $date;

    public function __construct()
    {
        $this->id = Util::generateUuid();
        $this->date = Util::createMicroSecondsNow();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}
