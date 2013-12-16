<?php

use Bernard\Envelope;
use Bernard\Message\DefaultMessage;

// 590364000 = 16 September 1988
return new Envelope(new DefaultMessage("Import"), "Bernard\Message\DefaultMessage", 590364000);
