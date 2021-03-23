<?php
/**
 * Copyright © (c) chotructuyen.vn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PriceQuote\Controller\Request;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Controller\ResultFactory;
use Dompdf\Dompdf;
use Dompdf\Options;
use Zend_Mime;

class Pdf extends \Magento\Framework\App\Action\Action
{
    const SEND_EMAIL_REQ_MAX = 2;
    protected $resultPageFactory;

     /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var  ScopeConfigInterface
     */
    private  $scopeConfig;

    /**
     * @var  resolverInterface
     */
    private  $resolverInterface;

    protected $request;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\RequestInterface $request,
        SenderResolverInterface $resolverInterface
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->resolverInterface = $resolverInterface;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try{
            $request_id = $this->request->getParam('id_request');
            $priceQuoteRequest = $this->getPriceQuoteRequest($request_id);
            $this->savePdf($priceQuoteRequest);
            $data = $priceQuoteRequest->getData('file_path');
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($data);
            return $resultJson;

        }catch(\Exception $e){
            $this->writeLog($e);
            $this->_redirect("price_quote");
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
    public function getPriceQuoteRequest($request){
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$priceQuoteRequest = $objectManager->get('Chottvn\PriceQuote\Model\Request')->load();
        $priceQuoteRequest = $objectManager->create('Chottvn\PriceQuote\Model\ResourceModel\Request\CollectionFactory')->create()->addFieldToFilter("request_id",$request)->getFirstItem();
        return $priceQuoteRequest;
    }


    public function getHtmlForPdf($priceQuoteRequest)
    {
        // $resultPage = $this->resultPageFactory->create();
        // $block = $resultPage->getLayout()->createBlock('Chottvn\PriceQuote\Block\Request\Pdf');
        $layout = $this->_view->getLayout();
        $block = $layout->createBlock('Chottvn\PriceQuote\Block\Request\Pdf');
        $block->setTemplate('Chottvn_PriceQuote::request/pdf.phtml');

        $data = [
            'requestId' => $priceQuoteRequest->getId()
        ];
        $block->setData($data);

        return $block->toHtml();
    }


    public function savePdf($priceQuoteRequest){
        // if ($priceQuoteRequest->getFilePath()){
        //     return;
        // }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $pdf = new Dompdf();
        $htmlContent = $this->getHtmlForPdf($priceQuoteRequest);
        $pdf->loadHtml($htmlContent);

        // (Optional) Setup the paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        $pdf->set_option('isHtml5ParserEnabled', true);
        $pdf->set_option('isRemoteEnabled', true);
        $pdf->set_option('isJavascriptEnabled', true);
        $pdf->set_option('dpi', 120);

        $pdf->render();
        $output = $pdf->output();

        $dir = \Magento\Framework\App\Filesystem\DirectoryList::PUB;
        $fileName = "price_quote/".$priceQuoteRequest->getUrlKey().'.pdf';
        // Save file - HTTP >> download after save
        /*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fileFactory = $objectManager->get('Magento\Framework\App\Response\Http\FileFactory');
        $fileFactory->create($fileName,$output,$dir,'application/pdf');*/
        // Save file - File System
        $filesystem = $objectManager->get('Magento\Framework\Filesystem');
        $writer = $filesystem->getDirectoryWrite($dir);
        $file = $writer->openFile($fileName, 'w');
        try {
            $file->lock();
            try {
                $file->write($output);
            }
            finally {
                $file->unlock();
            }
        }
        finally {
            $file->close();
        }
        // Update filePath
        $priceQuoteRequest->setFilePath($dir."/".$fileName);
        $priceQuoteRequest->save();
    }

    /**
    * @param $info
    * @param $type  [error, warning, info]
    * @return
    */
    private function writeLog($info, $type = "info"){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/price_quote.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        switch($type){
            case "error":
                $logger->err($info);
                break;
            case "warning":
                $logger->notice($info);
                break;
            case "info":
                $logger->info($info);
                break;
            default:
                $logger->info($info);
        }
    }

}
