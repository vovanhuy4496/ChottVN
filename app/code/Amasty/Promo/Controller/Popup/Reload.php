<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Controller\Popup;

use Amasty\Promo\Block\Add;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\Result\PageFactory;

class Reload extends \Magento\Framework\App\Action\Action
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Add
     */
    private $add;

    /**
     * Reload constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RawFactory $resultRawFactory
     * @param Add $add
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory,
        Add $add
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->add = $add;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        $returnUrl = $this->getRequest()->getParam(Action::PARAM_NAME_URL_ENCODED);

        if (!$returnUrl) {
            $resultRaw->setHttpResponseCode(403);
            return $resultRaw;
        }

        $resultPage->getLayout()->getBlock('ampromo.items')->setData('current_url', $returnUrl);

        $rawContent = $resultPage->getLayout()->renderElement('ampromo.items');
        $products= \Zend_Json::encode($this->add->getAvailableProductQty());
        $resultRaw->setContents(\Zend_Json::encode(['popup' => $rawContent, 'products' => $products]));

        return $resultRaw;
    }
}
