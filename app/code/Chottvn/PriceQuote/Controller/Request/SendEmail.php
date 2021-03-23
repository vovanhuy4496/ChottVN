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

use Dompdf\Dompdf;
use Dompdf\Options;
use Zend_Mime;

class SendEmail extends \Magento\Framework\App\Action\Action
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
        SenderResolverInterface $resolverInterface
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
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
            $priceQuoteRequest = $this->getPriceQuoteRequest();
            $this->savePdf($priceQuoteRequest);
            $this->sendEmail($priceQuoteRequest);
            // $this->messageManager->addSuccessMessage(__('We sent price quote to your email. Please check your email inbox.'));

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath("price_quote");
            return $resultRedirect;

            /*$resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Price Quote'));
            return $resultPage;*/
        }catch(\Exception $e){
            // $this->messageManager->addErrorMessage(__('Something went wrong. Please check back later.'));
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
    public function getPriceQuoteRequest(){
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$priceQuoteRequest = $objectManager->get('Chottvn\PriceQuote\Model\Request')->load();
        $priceQuoteRequest = $objectManager->create('Chottvn\PriceQuote\Model\ResourceModel\Request\CollectionFactory')->create()->addFieldToFilter("url_key",$this->getRequestKey())->getFirstItem();
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

    public function sendEmail($priceQuoteRequest){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // Check SendEmail
        $sentCount = intval($priceQuoteRequest->getEmailSentCount());
        if($sentCount >= self::SEND_EMAIL_REQ_MAX){
            return;
        }

        /*
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $inlineTranslation = $objectManager->create('\Magento\Framework\Translate\Inline\StateInterface');
        $transportBuilder = $objectManager->get('\Magento\Framework\Mail\Template\TransportBuilder');
        $scopeConfig = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
        $resolverInterface = $objectManager->create('Magento\Framework\Mail\Template\SenderResolverInterface');
        */

        //$priceQuoteRequest = $this->getPriceQuoteRequest();
       

        $this->inlineTranslation->suspend();

        $senderIdentityCode = $this->scopeConfig ->getValue('sales_email/price_quote/identity', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(empty($senderIdentityCode)){
            $senderIdentityCode = "general";
        }
        $identity = $this->resolverInterface->resolve($senderIdentityCode);

        $template = $this->scopeConfig ->getValue('sales_email/price_quote/template',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $senderName = $identity['name'];
        $senderEmail = $identity['email'];

        $adminReceiverName = $this->scopeConfig ->getValue('sales_email/price_quote/name_receivers',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $adminReceiverEmail = $this->scopeConfig ->getValue('sales_email/price_quote/email_receivers',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $adminReceiverEmailArray = explode(",",$adminReceiverEmail);
        $contactName = $priceQuoteRequest->getContactName();
        $contactEmail = $priceQuoteRequest->getContactEmail();
        $bccArray = array();
        $receiverEmailArray = array();
        $receiverName = "";
        if (empty($contactEmail)){
            $receiverEmailArray = array_merge($receiverEmailArray, $adminReceiverEmailArray);
        }else{
            array_push($receiverEmailArray, $contactEmail);
            $bccArray = array_merge($bccArray, $adminReceiverEmailArray);
        }
        if (empty($contactName)){
            $receiverName = $adminReceiverName;
        }else{
            $receiverName = $contactName;
        }

        $store_id = $this->storeManager->getStore()->getId();
        if(!$store_id){
            $store_id = 1;
        }
        if(!$template){
            $template = "sales_email_price_quote_template";
        }

        // Attach
        //$filename = __("Price Quote")." - ".$priceQuoteRequest->getCreatedAt(); //." - ".$priceQuoteRequest->getCompanyName()
        $timezone = $objectManager->get("\Magento\Framework\Stdlib\DateTime\TimezoneInterface");
        $fileDateTime = $timezone->date($priceQuoteRequest->getCreatedAt())->format('Y-m-d-H-i-s');
        $createdate = $timezone->date($priceQuoteRequest->getCreatedAt())->format('Y-m-d H:i:s');
        $filename = "BaoGia"." - ".$fileDateTime.".pdf"; 
        $emailData = [
            "request" => $priceQuoteRequest,
            "createdate" => $createdate,
            "price_quote_url" => $this->_url->getUrl('price_quote/request/view/key/'.$priceQuoteRequest->getUrlKey())
        ];
        $this->transportBuilder
        ->setTemplateIdentifier($template)
        ->setTemplateOptions(
        [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $store_id
        ]
        )
        ->setTemplateVars(
            $emailData
        )
        ->setFrom(
        [
            'email' =>  $senderEmail,
            'name' => $senderName
        ])->addAttachment(file_get_contents($priceQuoteRequest->getFilePath()), $filename,"application/pdf")
        ;
        if (empty($receiverEmailArray)){
            return;
        }
        foreach ($receiverEmailArray as $email) {
            if (!empty($email)){
                $this->transportBuilder->addTo($email);
            }
        }
        foreach ($bccArray as $email) {
            if (!empty($email)){
                $this->transportBuilder->addBcc($email);
            }
        }

        $transport = $this->transportBuilder->getTransport();

        $transport->sendMessage();
        $this->inlineTranslation->resume();
        // Update history

        $priceQuoteRequest->setEmailSentAt(time());
        $priceQuoteRequest->setEmailSentCount($sentCount + 1);
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
