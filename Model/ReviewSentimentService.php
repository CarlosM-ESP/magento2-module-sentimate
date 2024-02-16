<?php declare(strict_types=1);

namespace Macademy\Sentimate\Model;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Macademy\Sentimate\Model\ReviewSentimentFactory;
use Macademy\Sentimate\Model\ResourceModel\ReviewSentiment as ReviewSentimentResourceModel;

class ReviewSentimentService
{
    /**
     * ReviewSentimentService constructor
     *
     * @param ReviewSentimentFactory $reviewSentimentFactory
     * @param ReviewSentimentResourceModel $reviewSentimentResourceModel
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ReviewSentimentFactory $reviewSentimentFactory,
        private readonly ReviewSentimentResourceModel $reviewSentimentResourceModel,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Save review Sentiment analysis results
     *
     * @param ReviewSentiment $reviewSentiment
     * @return void
     */
    public function save(ReviewSentiment $reviewSentiment):void {
        try {
            $this->reviewSentimentResourceModel->save($reviewSentiment);
        } catch (Exception $e) {
            $this->logger->error(__('Failed to save sentiment analysis: %1', $e->getMessage()));
        }
    }

    /**
     * Get Sentiment analysis by Review ID.
     *
     * @param int $reviewId
     * @return ReviewSentiment
     * @throws NoSuchEntityException
     */
    public function getByReviewId(int $reviewId): ReviewSentiment{
        $reviewSentiment = $this->reviewSentimentFactory->create();
        $this->reviewSentimentResourceModel->load($reviewSentiment, $reviewId, 'review_id');

        if(!$reviewSentiment->getId()){
            throw new NoSuchEntityException(
                __('The review sentiment with %1 review ID does noot exist.', $reviewId));
        }

        return $reviewSentiment;
    }
}
