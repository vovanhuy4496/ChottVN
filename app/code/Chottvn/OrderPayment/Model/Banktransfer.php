<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\OrderPayment\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Directory\Helper\Data as DirectoryHelper;

use \Magento\Cms\Model\BlockRepository;
use Chottvn\OrderPayment\Model\ResourceModel\BankAccount\Collection as BankAccountCollection;


/**
 * Bank Transfer payment method model
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 *
 * @api
 * @since 100.0.2
 */
class Banktransfer extends \Magento\OfflinePayments\Model\Banktransfer // \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_BANKTRANSFER_CODE = 'banktransfer';
    const BLOCK_ID_NOTE_HEADER = 'bank-transfer-note-header';
    const BLOCK_ID_NOTE_FOOTER= 'bank-transfer-note-footer';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_BANKTRANSFER_CODE;

    /**
     * Bank Transfer payment block paths
     *
     * @var string
     */
    protected $_formBlockType = \Magento\OfflinePayments\Block\Form\Banktransfer::class;

    /**
     * Instructions block path
     *
     * @var string
     */
    protected $_infoBlockType = \Magento\Payment\Block\Info\Instructions::class;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    //protected $orderPaymentHelper;
    /**
     * BankAccount
     *
     * @var \Chottvn\OrderPayment\Model\ResourceModel\BankAccount\Collection
     */
    protected $bankAccountCollection;

    //protected $storeManager;
    /**
     * BlockRepository
     *
     * @var \Magento\Cms\Model\BlockRepository
     */
    protected $staticBlockRepository;

    /**@var \Magento\Store\Model\StoreManagerInterface **/
    protected $storeManager;   

    /**
     * BlockRepository
     *
     * @var \Magento\Framework\App\Language\Dictionary
     */
    protected $langDictionary;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,        
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        DirectoryHelper $directory = null,
        //OrderPaymentHelper $orderPaymentHelper
        BankAccountCollection $bankAccountCollection,
        BlockRepository $staticBlockRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,  
        \Magento\Framework\App\Language\Dictionary $langDictionary
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger//,
            // $resource,
            // $resourceCollection,
            // $data,
            // $directory
        );
        $this->_paymentData = $paymentData;
        $this->_scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->directory = $directory ?: ObjectManager::getInstance()->get(DirectoryHelper::class);
        $this->initializeData($data);

        //$this->orderPaymentHelper = $orderPaymentHelper;
        $this->bankAccountCollection = $bankAccountCollection;
        $this->staticBlockRepository = $staticBlockRepository;    

        $this->storeManager = $storeManager; 
        $this->langDictionary = $langDictionary;     
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        //return trim($this->getConfigData('instructions'));

        return $this->getBankAccountsText();
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getBankAccountsHtml(){
        try {        
            $collection = $this->bankAccountCollection->filterActive();
            $data = "";
            // $data .= $this->getStaticContent(self::BLOCK_ID_NOTE_HEADER);
            // $data .= $this->getHtmlBankAccountList($collection);
            // $data .= $this->getStaticContent(self::BLOCK_ID_NOTE_FOOTER);
            return $data;
        } catch (Exception $e) {
            return "";
        }
    }
    public function getBankAccountsText(){
        try { 
            $collection = $this->bankAccountCollection->filterActive();
            $data = "";
            // $data .= "\r\n".strip_tags($this->getStaticContent(self::BLOCK_ID_NOTE_HEADER));
            // $data .= "\r\n\r\n".$this->getTextBankAccountList($collection);
            // $data .= strip_tags($this->getStaticContent(self::BLOCK_ID_NOTE_FOOTER));
            return $data;
        }catch (\Exception $e) {
            return trim($this->getConfigData('instructions'));
        }
    }



    private function translate($string){
        try{            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resolver = $objectManager->get('Magento\Framework\Locale\Resolver');
            $locale = $resolver->getLocale();                              
            $translatedString = $this->langDictionary->getDictionary($locale)[$string];
            return $translatedString;
        }catch(\Exception $e){
            return $string;
        }        
    }

    private function getTextBankAccountList($collection){
        $text = "";
        foreach($collection as $itemBankAccount){
            $text .= 
                $itemBankAccount->getBankName()."\r\n"
                .$itemBankAccount->getBankBranch()."\r\n"
                .$this->translate('account_owner').": ".$itemBankAccount->getAccountOwner()."\r\n"
                .$this->translate('account_number').": ".$itemBankAccount->getAccountNumber()."\r\n"
                ."\r\n";
        }
        return $text;
    }

    private function getHtmlBankAccountList($collection){
        $html = "<ul class='bank-account-list'>";
        foreach($collection as $itemBankAccount){
            $html .= 
                "<li class='bank-account-item'>"
                    ."<div class='bank-image'>"
                        ."<img src='".$itemBankAccount->getBankImage()."'  alt='Bank Image'/>"
                    ."</div>"
                    ."<div class='account-info'>"
                        ."<span class='bank-name'>".$itemBankAccount->getBankName()."</span>"."<br/>"
                        ."<span class='bank-branch'>".$itemBankAccount->getBankBranch()."</span>"."<br/>"
                        ."<span class='title'>".$this->translate('account_owner')."</span> : "."<span class='account-owner'>".$itemBankAccount->getAccountOwner()."</span>"."<br/>"
                        ."<span class='title'>".$this->translate('account_number')."</span> : "."<span class='account-number'>".$itemBankAccount->getAccountNumber()."</span>"
                    ."</div>"
                ."</li>";
        }
        $html .= "</ul>";
        return $html;
    }

    public function getStaticBlock($blockId){
        try {
            $block = $this->staticBlockRepository->getById($blockId);
            return $block;
        }
        catch (\Throwable $t) {
            return false;
        }
        catch(\Magento\Framework\Exception\NoSuchEntityException $e){
            return false;
        }
        catch(\Exception $e){
            //$this->logger->warning($e->getMessage());
            return false;
        }        
    }

    public function getStaticContent($blockId){

        $staticBlock = $this->getStaticBlock($blockId);

        if($staticBlock && $staticBlock->isActive()){           
            return $staticBlock->getContent();
        }
        /* Optional ->setVariables(['number'=>213452345234] Usage in wysiwyfg {{var number}} */
        //return __('Static block content not found');
        return "";
    }

    public function getStaticBlockTitle($blockId){
        if($this->getStaticBlock($blockId)){
            return $this->getStaticBlock()->getTitle();
        };
        //return __('Whoops,');
        return "";
    }
}
