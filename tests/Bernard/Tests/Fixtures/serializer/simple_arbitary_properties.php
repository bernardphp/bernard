<?php

use Bernard\Envelope;
use Bernard\Message\DefaultMessage;

$message = new DefaultMessage("Import", array(
    'isDefault' => true,
    'users' => array(
        'Henrik',
        'Morten',
        'Nick',
    ),
));

// 590364000 = 16 September 1988
return new Envelope($message, "Bernard\Message\DefaultMessage", 590364000);
