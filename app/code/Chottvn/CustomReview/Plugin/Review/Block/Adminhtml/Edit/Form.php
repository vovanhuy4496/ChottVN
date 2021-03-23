<?php
namespace Chottvn\CustomReview\Plugin\Review\Block\Adminhtml\Edit;

use Amasty\AdvancedReview\Helper\BlockHelper as BlockHelper;
use Amasty\AdvancedReview\Model\Sources\Recommend;
use Amasty\AdvancedReview\Model\Sources\UseDefaultConfig;
use Amasty\AdvancedReview\Model\Repository\VoteRepository;
use Magento\Backend\Block\Widget\Form\Generic as MagentoForm;

class Form
{
    /**
     * @var \Amasty\AdvancedReview\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var VoteRepository
     */
    private $voteRepository;

    /**
     * @var BlockHelper
     */
    private $blockHelper;

    /**
     * @var Recommend
     */
    private $recommendModel;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var UseDefaultConfig
     */
    private $useDefaultConfig;

    public function __construct(
        \Amasty\AdvancedReview\Helper\Config $configHelper,
        BlockHelper $blockHelper,
        \Magento\Framework\Registry $registry,
        VoteRepository $voteRepository,
        Recommend $recommendModel,
        UseDefaultConfig $useDefaultConfig,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->configHelper = $configHelper;
        $this->registry = $registry;
        $this->voteRepository = $voteRepository;
        $this->blockHelper = $blockHelper;
        $this->recommendModel = $recommendModel;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->useDefaultConfig = $useDefaultConfig;
    }

    /**
     * @param MagentoForm $subject
     * @param \Magento\Framework\Data\Form $form
     * @return array
     */
    public function beforeSetForm(
        MagentoForm $subject,
        \Magento\Framework\Data\Form $form
    ) {
        $fieldset = $form->getElement('review_details') ?: $form->getElement('add_review_form');
        $review = $this->registry->registry('review_data') ?: $this->dataObjectFactory->create();

        $fieldset->addField(
            'ctt_updated_by',
            'text',
            [
                'label'    => __('Updated by'),
                'required' => false,
                'name'     => __('ctt_updated_by'),
                'value'    => $review->getData('ctt_updated_by'),
                'disabled' => true
            ]
        );

        return [$form];
    }
}
