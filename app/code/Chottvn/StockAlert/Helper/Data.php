<?php
namespace Chottvn\StockAlert\Helper;
 
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
class Data extends AbstractHelper
{
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
     * @var LoggerInterface
     */

    private $logger;
        /**
     * @var  resolverInterface 
     */
    private  $resolverInterface;
   
    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        LoggerInterface $logger,
        SenderResolverInterface $resolverInterface
    )
    {
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->logger = $logger;
        $this->resolverInterface = $resolverInterface;
        parent::__construct($context);
    }
    /**
     * Send Mail
     *
     * @return $this
     *
     * @throws LocalizedException
     * @throws MailException
     */
    public function sendMail($post)
    {
        // store id
        $this->inlineTranslation->suspend();
        $store_id = $this->getStoreId();
        if(!$store_id){
            $store_id = 1;
        }
        //set receiver mail
        $sentToName = $this->scopeConfig ->getValue('sales_email/stock_alert/stock_alert_name_receivers',ScopeInterface::SCOPE_STORE,$store_id);
        $sentToEmailStaff = $this->scopeConfig ->getValue('sales_email/stock_alert/stock_alert_email_receivers',ScopeInterface::SCOPE_STORE,$store_id); 
        $sentToEmailCustomer = $post['contact_email'];
        $arrsentToemail = [];
        if($sentToEmailStaff || $sentToEmailCustomer){
            $arrsentToemail = [$sentToEmailStaff,$sentToEmailCustomer];
        }
        /* email template */
        $identity = $this->resolverInterface->resolve($this->scopeConfig ->getValue('sales_email/stock_alert/identity',ScopeInterface::SCOPE_STORE,$store_id));
        $template = $this->scopeConfig ->getValue('sales_email/stock_alert/template',ScopeInterface::SCOPE_STORE,$store_id);
        $template_price_contact = $this->scopeConfig ->getValue('sales_email/stock_alert/template_price_contact',ScopeInterface::SCOPE_STORE,$store_id);
        // set flag template
        $flag_form = $post['flag_form'];
        $custom_template = "sales_email_stock_alert_template";
        // contactwhenstock --> san pham het hang
        if($flag_form == 'contactwhenstock'){
            if($template){
                $custom_template = $template;
            }else{
                $custom_template = "sales_email_stock_alert_template";
            }
        }else{
            if($template_price_contact){
                $custom_template = $template_price_contact;
            }else{
                $custom_template = "sales_email_stock_alert_template_price_contact";
            }
        }
        // set data template
        $vars = $post;

        if($vars['selectedOptions']){
            $selectedOptions = (array)json_decode($vars['selectedOptions']);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $eavModel = $objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute');

            $arrSelectedOptions = array();
            foreach ($selectedOptions as $key => $value) {
                $attr = $eavModel->load($key);
                $attributeCode = $attr->getStoreLabel();
                $optionSelectedLabel = $attr->getSource()->getOptionText($value);
                $arrSelectedOptions[] = $attr->getStoreLabel()." (".$optionSelectedLabel.")";
            }
            
            $vars['selectedOptions'] = implode(',', $arrSelectedOptions);
        }
        
        // set from email
        $sentFromName = $identity['name'];
        $sentFromEmail = $identity['email'];
        try {
            if($arrsentToemail){
                foreach($arrsentToemail as $item){
                    $this->configureEmailTemplate($custom_template,$vars, $sentFromEmail,$sentFromName,$item);
                }
            }else{
                return $this;
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        $this->inlineTranslation->resume();
 
        return $this;
    }
    /**
     * Configure email template
     *
     * @return void
     */
    protected function configureEmailTemplate($custom_template,$vars, $sentFromEmail,$sentFromName,$item)
    {
        $transport = $this->transportBuilder->setTemplateIdentifier(
            $custom_template
        )->setTemplateOptions(
            [
                'area' => Area::AREA_FRONTEND,
                'store' => $this->getStoreId()
            ]
        )->setTemplateVars(
            $vars
        )->setFromByScope(
            [
                'email' =>  $sentFromEmail,
                'name' => $sentFromName
            ]
        );
        $transport->addTo($item)->getTransport()->sendMessage();
        return $this;
    }
    /*
     * get Current store id
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
 
    /*
     * get Current store Info
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }
}