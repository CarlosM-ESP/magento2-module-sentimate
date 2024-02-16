<?php declare(strict_types=1);

namespace Macademy\Sentimate\Model;

use Magento\Framework\Model\AbstractModel;
use Macademy\Sentimate\Model\ResourceModel\ReviewSentiment as ReviewSentimentResourceModel;

class ReviewSentiment extends AbstractModel
{
    /**
     * Primary Id.
     *
     * @var string $_idFieldName
     */
    protected $_idFieldName = ReviewSentimentResourceModel::ID_FIELD_NAME;

    /**
     * Initialize with ResourceModel
     *
     * @return void
     */
    protected function _construct():void
    {
        $this->_init(ReviewSentimentResourceModel::class);
    }
}
