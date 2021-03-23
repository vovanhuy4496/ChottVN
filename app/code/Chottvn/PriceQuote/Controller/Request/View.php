<?php
/**
 * Copyright © (c) chotructuyen.vn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PriceQuote\Controller\Request;

class View extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {     
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Price Quote'));

        $priceQuoteRequest  = $this->getPriceQuoteRequest();
        //
        if($priceQuoteRequest->getFilePath()){ //Exported Pdf
            //$this->_redirect($priceQuoteRequest->getFilePath());
            header("Location: /".$priceQuoteRequest->getFilePath(), true, 301);
            exit();
        }else{ // Not export PDF
            $blockView = $resultPage->getLayout()->getBlock('request.view');
            $data = [
                'requestId' => $priceQuoteRequest->getId()
            ];
            $blockView->setData($data);
            
            return $resultPage;
        }        
    }

    /**
     * Get Request Key from params
     *
     * @return string
     */
    public function getRequestKey()
    {
        return $this->getRequest()->getParam("key");
    }  

    /**
     * Get Request Key from params
     *
     * @return \Chottvn\PriceQuote\Model\Request
     */
    public function getPriceQuoteRequest(){
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$priceQuoteRequest = $objectManager->get('Chottvn\PriceQuote\Model\Request')->load();
        $priceQuoteRequest = $objectManager->create('Chottvn\PriceQuote\Model\ResourceModel\Request\CollectionFactory')->create()->addFieldToFilter("url_key",$this->getRequestKey())->getFirstItem();
        return $priceQuoteRequest;
    } 
}

