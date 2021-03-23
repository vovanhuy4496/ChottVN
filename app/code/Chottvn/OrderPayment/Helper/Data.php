<?php
/**
 * Copyright (c) 2019 2020 ChottVN
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Chottvn\OrderPayment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Chottvn\OrderPayment\Model\BankAccountFactory;
use \Magento\Cms\Model\BlockRepository;
use Chottvn\OrderPayment\Model\BankAccount\DataProvider;
/**
 * Class Data
 *
 * @package Chottvn\OrderPayment\Helper
 */
class Data extends AbstractHelper
{
    const BLOCK_ID_NOTE_HEADER = 'bank-transfer-note-header';
    const BLOCK_ID_NOTE_FOOTER= 'bank-transfer-note-footer';

    /**
     * @var BankAccountFactory
     */
    public $bankAccountFactory;
     /**
     * @var DataProvider
     */
    public $dataProvider;
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

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        BankAccountFactory $bankAccountFactory,
        BlockRepository $staticBlockRepository,
        DataProvider $dataProvider,        
        \Magento\Store\Model\StoreManagerInterface $storeManager,  
        \Magento\Framework\App\Language\Dictionary $langDictionary
    ) {
        parent::__construct($context);
        //$this->bankAccountRepository = $bankAccountRepository;
        $this->bankAccountFactory = $bankAccountFactory;
        $this->dataProvider = $dataProvider;
        $this->staticBlockRepository = $staticBlockRepository; 
        $this->storeManager = $storeManager; 
        $this->langDictionary = $langDictionary; 
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * @param 
     *
     * @return Collection
     */
    public function getBankAccountCollection()
    {
        $collection = $this->bankAccountFactory->create()->getCollection()->filterActive();

        return $collection;
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getBankAccountInstructions(){
        try {        
            $collection = $this->getBankAccountCollection();
            $data = "";
            // $data .= $this->getStaticContent(self::BLOCK_ID_NOTE_HEADER);
            $data .= $this->getHtmlBankAccountList($collection);
            // $data .= $this->getStaticContent(self::BLOCK_ID_NOTE_FOOTER);
            return $data;
        } catch (Exception $e) {
            return "";
        }
    }

    /**
     * @param 
     *
     * @return Array
     */
    public function getBankAccountDict(){
        $collection = $this->getBankAccountCollection();
        $dict = array();
        foreach($collection as $itemBankAccount){
            $dict[$itemBankAccount->getId()] = $itemBankAccount->toArray();
            $instruction = $itemBankAccount->getBankName();
                // .$itemBankAccount->getBankBranch()."\r\n"
                // .$this->translate('account_owner').": ".$itemBankAccount->getAccountOwner()."\r\n"
                // .$this->translate('account_number').": ".$itemBankAccount->getAccountNumber()."\r\n";
            $dict[$itemBankAccount->getId()]["instruction"] = $instruction;
        }
        return json_encode($dict);
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $detectMobile = $objectManager->get('\Chottvn\Frontend\Helper\DetectMobile');
        if ($detectMobile->isMobile() == true && $detectMobile->isTablet() == false) {
            $col_1 = 'col-5';
            $col_2 = 'col-7';
        } else {
            $col_1 = 'col-3';
            $col_2 = 'col-9';
        }
        // $checkoutBankId = null;
        // if (isset($_COOKIE['checkoutBankId'])) {
        //     $checkoutBankId = $_COOKIE['checkoutBankId'];
        // }
        // $this->writeLog($checkoutBankId);
        $getBankBranch = __('Account Name: CHO TRUC TUYEN CORPORATION');
        $html = "<h6 class='bank-branch'>".$getBankBranch."</h6>";
        $html .= "<ul class='bank-account-list'>";
        foreach($collection as $itemBankAccount){
            $checked = '';
            // if ($itemBankAccount->getId() == $checkoutBankId) {
            //     $checked = 'checked="true"';
            //     $this->writeLog($itemBankAccount->getId());
            //     $this->writeLog($checkoutBankId);
            // }
            $html .= 
                "<li class='row bank-account-item' data-id='".$itemBankAccount->getId()."'>"
                    ."<div class='".$col_1." bank-image'>"
                        ."<input type='radio' name='payment-method-choose' class='radio' ".$checked." id='radio_".$itemBankAccount->getId()."'>"
                        ."<img src='".$this->dataProvider->getMediaUrl().$itemBankAccount->getBankImage()."'  alt='".$itemBankAccount->getBankName()."'/>"
                    ."</div>"
                    ."<div class='".$col_2." account-info'>"
                        ."<span class='title'>".$this->translate('account_number')."</span>: "."<span class='account-number'>".$itemBankAccount->getAccountNumber()."</span>"."<br/>"
                        ."<span class='bank-name'>".$itemBankAccount->getBankName()."</span>"
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

    public function url_exists($url) {
        $url_headers = get_headers($url);
        if(!$url_headers || $url_headers[0] == 'HTTP/1.0 404 Not Found') {
            $exists = false;
        }
        else {
            $exists = true;
        }
        return $exists;
    }

    protected function hasProductUrl($product)
    {
        if ($product->isVisibleInSiteVisibility()) {
            return true;
        } else {
            if ($product->hasUrlDataObject()) {
                $data = $product->getUrlDataObject();
                if (in_array($data->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info"){
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/default_config_provider.log');
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

