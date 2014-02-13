<?ph

namespace Bernard\EventListener;

use Bernard\Event\EnvelopeEvent;
use Bernard\Event\RejectEnvelopeEvent;
use Bernard\Batch\Storage;
use Bernard\Envelope;

class BatchSubscriber implements \Symfony\Component\EventDispatcher\EventDispatcherInterface
{
    public function onProduce(EnvelopeEvent $event)
    {
        $envelope = $event->getEnvelope();

        if ($batch = $envelope->getStamp('batch')) {
            $this->storage->register($envelope->getStamp('batch'));
        }
    }

    public function onAcknowledgeReject(EnvelopeEvent $event)
    {
        $envelope = $event->getEnvelope();

        if (false == $batch = $envelope->getStamp('batch')) {
            return;
        }

        $type = $event instanceof RejectEnvelopeEvent ? 'failed' : 'successful';
        $this->storage->increment($batch, $type);
    }

    public static function getSubscribedEvents()
    {
        return array(
            'bernard.produce'     => 'onProduce',
            'bernard.acknowledge' => 'onAcknowledgeReject',
            'bernard.reject'      => 'onAcknowledgeReject',
        );
    }
}
