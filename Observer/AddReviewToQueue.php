<?php declare(strict_types=1);

namespace Macademy\Sentimate\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class AddReviewToQueue implements ObserverInterface
{
    /**
     * @param PublisherInterface $publisher
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly PublisherInterface $publisher,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function execute(Observer $observer): void
    {
        $review = $observer->getEvent()->getData('object');

        if ($review->isObjectNew()) {
            $reviewData = $review->getData();
            $serializedReviewData = $this->serializer->serialize($reviewData);
            $this->publisher->publish('macademy.sentimate.reviews', $serializedReviewData);
        }
    }
}
