<?php

namespace Chottvn\AutoApprovedReview\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\Review;
use Psr\Log\LoggerInterface;

class ReviewSaveBeforeObserver implements ObserverInterface
{
    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Review $review */
        $review = $observer->getDataByKey('object');
        $review->setStatusId(Review::STATUS_APPROVED);
    }
}