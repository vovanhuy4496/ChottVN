<?php

namespace Chottvn\Affiliate\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;

class Data extends AbstractHelper
{
    protected $transportBuilder;
    protected $storeManager;
    protected $inlineTranslation;
    protected $directoryList;

    /**
     * @var  SenderResolverInterface
     */
    protected  $resolverInterface;

    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        SenderResolverInterface $resolverInterface
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->scopeConfig = $scopeConfig;
        $this->resolverInterface = $resolverInterface;
        $this->directoryList = $directoryList;
        $this->fileDriver = $fileDriver;
        parent::__construct($context);
    }

    public function sendNewAffiliateEmail()
    {
        $identity = $this->getSenderIdentity();

        $fromName = $identity['name'];
        $fromEmail = $identity['email'];

        $toEmail = $this->scopeConfig->getValue('email_affiliate/new_affiliate/receiver_email');

        $template = "email_affiliate_new_affiliate_template";

        try {
            // template variables pass here
            $templateVars = [];

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($template, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($toEmail)
                ->getTransport();
                
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->writeLog("Chottvn\Affiliate\Helper\Data - Error: " . $e->getMessage());
            $this->_logger->info($e->getMessage());
        }
    }
    public function sendWithdrawalAffiliateEmail($data)
    {
        $identity = $this->getSenderIdentity();

        $fromName = $identity['name'];
        $fromEmail = $identity['email'];

        $ccEmail = $this->scopeConfig->getValue('email_affiliate/general/cc_email');
        
        $template = "email_affiliate_withdrawal_affiliate_template";
        try {
            // template variables pass here
            $templateVars = [
                'message' => $data['message'],
                'fullName' => $data['fullName'],
                'amountRequest' => $data['amountRequest'],
                'dateRequest' => '<span>'.$data['dateRequest'].'</span>'
            ];
            
            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($template, $storeScope)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom($from)
            ->addTo($data['toEmail']);

            if ($ccEmail) {
                $this->transportBuilder->addCc($ccEmail, '');
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->writeLog("Chottvn\Affiliate\Helper\Data - Error: " . $e->getMessage());
            $this->_logger->info($e->getMessage());
        }
    }
    public function sendReRegisterEmail($data)
    {
        $identity = $this->getSenderIdentity();

        $fromName = $identity['name'];
        $fromEmail = $identity['email'];

        $template = "email_affiliate_re_register_affiliate_template";

        try {
            // template variables pass here
            $templateVars = [
                'registerLink' => $data['registerLink'],
                'fullName' => $data['fullName'],
                'homeLink' => $data['homeLink']
            ];

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $this->transportBuilder->setTemplateIdentifier($template, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($data['toEmail']);

            if ($this->scopeConfig->getValue('email_affiliate/general/cc_email')) {
                $this->transportBuilder->addCc($this->scopeConfig->getValue('email_affiliate/general/cc_email'), '');
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->writeLog("Chottvn\Affiliate\Helper\Data - Error: " . $e->getMessage());
            $this->_logger->info($e->getMessage());
        }
    }

    public function sendRequestIdentityCardEmail($data)
    {
        $identity = $this->getSenderIdentity();

        $fromName = $identity['name'];
        $fromEmail = $identity['email'];

        $template = "email_affiliate_request_identity_card_affiliate_template";

        try {
            // template variables pass here
            $templateVars = [
                'fullName' => $data['fullName'],
                'homeLink' => $data['homeLink']
            ];

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $this->transportBuilder->setTemplateIdentifier($template, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($data['toEmail']);

            if ($this->scopeConfig->getValue('email_affiliate/general/cc_email')) {
                $this->transportBuilder->addCc($this->scopeConfig->getValue('email_affiliate/general/cc_email'), '');
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->writeLog("Chottvn\Affiliate\Helper\Data - Error: " . $e->getMessage());
            $this->_logger->info($e->getMessage());
        }
    }

    public function sendRejectEmail($data)
    {
        $identity = $this->getSenderIdentity();

        $fromName = $identity['name'];
        $fromEmail = $identity['email'];

        $template = "email_affiliate_reject_account_affiliate_template";

        try {
            // template variables pass here
            $templateVars = [
                'fullName' => $data['fullName'],
                'homeLink' => $data['homeLink']
            ];

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $this->transportBuilder->setTemplateIdentifier($template, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($data['toEmail']);

            if ($this->scopeConfig->getValue('email_affiliate/general/cc_email')) {
                $this->transportBuilder->addCc($this->scopeConfig->getValue('email_affiliate/general/cc_email'), '');
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->writeLog("Chottvn\Affiliate\Helper\Data - Error: " . $e->getMessage());
            $this->_logger->info($e->getMessage());
        }
    }

    public function sendActiveEmail($data)
    {
        $identity = $this->getSenderIdentity();

        $fromName = $identity['name'];
        $fromEmail = $identity['email'];

        $template = "email_affiliate_active_affiliate_template";

        try {
            // template variables pass here
            $templateVars = [
                'affiliateCode' => $data['affiliateCode'],
                'username' => $data['username'],
                'fullName' => $data['fullName'],
                'homeLink' => $data['homeLink'],
                'linkExpiredPeriod' => $data['linkExpiredPeriod']
            ];

            if (isset($data['activeLink'])) {
                $templateVars['activeLink'] = $data['activeLink'];
            }

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $this->transportBuilder->setTemplateIdentifier($template, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($data['toEmail']);

            if ($this->scopeConfig->getValue('email_affiliate/general/cc_email')) {
                $this->transportBuilder->addCc($this->scopeConfig->getValue('email_affiliate/general/cc_email'), '');
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->writeLog("Chottvn\Affiliate\Helper\Data - Error: " . $e->getMessage());
            $this->_logger->info($e->getMessage());
        }
    }

    public function sendActiveEmailForCustomer($data)
    {
        $identity = $this->getSenderIdentity();

        $fromName = $identity['name'];
        $fromEmail = $identity['email'];

        $template = "email_affiliate_active_affiliate_customer_template";

        try {
            // template variables pass here
            $templateVars = [
                'affiliateCode' => $data['affiliateCode'],
                'username' => $data['username'],
                'fullName' => $data['fullName'],
                'homeLink' => $data['homeLink'],
                'linkExpiredPeriod' => $data['linkExpiredPeriod']
            ];

            if (isset($data['activeLink'])) {
                $templateVars['activeLink'] = $data['activeLink'];
            }

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $this->transportBuilder->setTemplateIdentifier($template, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($data['toEmail']);

            if ($this->scopeConfig->getValue('email_affiliate/general/cc_email')) {
                $this->transportBuilder->addCc($this->scopeConfig->getValue('email_affiliate/general/cc_email'), '');
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->writeLog("Chottvn\Affiliate\Helper\Data - Error: " . $e->getMessage());
            $this->_logger->info($e->getMessage());
        }
    }

    public function sendActiveSuccessEmail($data)
    {
        $identity = $this->getSenderIdentity();

        $fromName = $identity['name'];
        $fromEmail = $identity['email'];

        $template = "email_affiliate_active_affiliate_success_template";

        try {
            // template variables pass here
            $templateVars = [
                'affiliateCode' => $data['affiliateCode'],
                'username' => $data['username'],
                'fullName' => $data['fullName'],
                'homeLink' => $data['homeLink'],
                'pdfLink' => $data['homeLink'].'pub/media/affiliate/affiliate_contract_file.pdf'
            ];

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];

            $this->transportBuilder->setTemplateIdentifier($template, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars);

            // // Attach
            // $filename = __("affiliate_contract_file.pdf");
            // $filePathSub = 'affiliate/' . $filename;
            // $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::PUB)
            //     . "/" . \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            //     . "/" . $filePathSub;
            // // If have file then add attachment
            // if ($this->fileDriver->isExists($filePath)) {
            //     $this->transportBuilder->addAttachment(file_get_contents($filePath), $filename, "application/pdf");
            // }

            $this->transportBuilder->setFrom($from)
                ->addTo($data['toEmail']);

            if ($this->scopeConfig->getValue('email_affiliate/general/cc_email')) {
                $this->transportBuilder->addCc($this->scopeConfig->getValue('email_affiliate/general/cc_email'), '');
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->writeLog("Chottvn\Affiliate\Helper\Data - Error: " . $e->getMessage());
            $this->_logger->info($e->getMessage());
        }
    }

    public function sendFreezedEmail($data)
    {
        $identity = $this->getSenderIdentity();

        $fromName = $identity['name'];
        $fromEmail = $identity['email'];

        $template = "email_affiliate_freezed_unfreezed_affiliate_template_freezed_affiliate";

        try {
            // template variables pass here
            $templateVars = [
                'affiliateCode' => $data['affiliateCode'],
                'username' => $data['username'],
                'fullName' => $data['fullName'],
                'homeLink' => $data['homeLink']
            ];

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];

            $this->transportBuilder->setTemplateIdentifier($template, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars);

            // // Attach
            // $filename = __("affiliate_contract_file.pdf");
            // $filePathSub = 'affiliate/' . $filename;
            // $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::PUB)
            //     . "/" . \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            //     . "/" . $filePathSub;
            // // If have file then add attachment
            // if ($this->fileDriver->isExists($filePath)) {
            //     $this->transportBuilder->addAttachment(file_get_contents($filePath), $filename, "application/pdf");
            // }

            $this->transportBuilder->setFrom($from)
                ->addTo($data['toEmail']);

            if ($this->scopeConfig->getValue('email_affiliate/general/cc_email')) {
                $this->transportBuilder->addCc($this->scopeConfig->getValue('email_affiliate/general/cc_email'), '');
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->writeLog("Chottvn\Affiliate\Helper\Data - Error: " . $e->getMessage());
            $this->_logger->info($e->getMessage());
        }
    }

    public function sendLockedEmail($data)
    {
        $identity = $this->getSenderIdentity();

        $fromName = $identity['name'];
        $fromEmail = $identity['email'];

        $template = "email_affiliate_locked_unlocked_customer_template_locked_customer";

        try {
            // template variables pass here
            $templateVars = [
                'username' => $data['username'],
                'fullName' => $data['fullName'],
                'homeLink' => $data['homeLink']
            ];

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];

            $this->transportBuilder->setTemplateIdentifier($template, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars);

            $this->transportBuilder->setFrom($from)
                ->addTo($data['toEmail']);

            if ($this->scopeConfig->getValue('email_affiliate/general/cc_email')) {
                $this->transportBuilder->addCc($this->scopeConfig->getValue('email_affiliate/general/cc_email'), '');
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->writeLog("Chottvn\Affiliate\Helper\Data - Func sendLockedEmail: " . $e->getMessage());
            $this->_logger->info($e->getMessage());
        }
    }

    public function sendUnFreezedEmail($data)
    {
        $identity = $this->getSenderIdentity();

        $fromName = $identity['name'];
        $fromEmail = $identity['email'];

        $template = "email_affiliate_freezed_unfreezed_affiliate_template_unfreezed_affiliate";

        try {
            // template variables pass here
            $templateVars = [
                'affiliateCode' => $data['affiliateCode'],
                'username' => $data['username'],
                'fullName' => $data['fullName'],
                'homeLink' => $data['homeLink']
            ];

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];

            $this->transportBuilder->setTemplateIdentifier($template, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars);

            // // Attach
            // $filename = __("affiliate_contract_file.pdf");
            // $filePathSub = 'affiliate/' . $filename;
            // $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::PUB)
            //     . "/" . \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            //     . "/" . $filePathSub;
            // // If have file then add attachment
            // if ($this->fileDriver->isExists($filePath)) {
            //     $this->transportBuilder->addAttachment(file_get_contents($filePath), $filename, "application/pdf");
            // }

            $this->transportBuilder->setFrom($from)
                ->addTo($data['toEmail']);

            if ($this->scopeConfig->getValue('email_affiliate/general/cc_email')) {
                $this->transportBuilder->addCc($this->scopeConfig->getValue('email_affiliate/general/cc_email'), '');
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->writeLog("Chottvn\Affiliate\Helper\Data - Error: " . $e->getMessage());
            $this->_logger->info($e->getMessage());
        }
    }

    public function sendUnLockedEmail($data)
    {
        $identity = $this->getSenderIdentity();

        $fromName = $identity['name'];
        $fromEmail = $identity['email'];

        $template = "email_affiliate_locked_unlocked_customer_template_unlocked_customer";

        try {
            // template variables pass here
            $templateVars = [
                'username' => $data['username'],
                'fullName' => $data['fullName'],
                'homeLink' => $data['homeLink']
            ];

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];

            $this->transportBuilder->setTemplateIdentifier($template, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars);

            $this->transportBuilder->setFrom($from)
                ->addTo($data['toEmail']);

            if ($this->scopeConfig->getValue('email_affiliate/general/cc_email')) {
                $this->transportBuilder->addCc($this->scopeConfig->getValue('email_affiliate/general/cc_email'), '');
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->writeLog("Chottvn\Affiliate\Helper\Data - Func sendUnLockedEmail: " . $e->getMessage());
            $this->_logger->info($e->getMessage());
        }
    }

    public function getSenderIdentity()
    {
        return [
            "name" => $this->scopeConfig->getValue('email_affiliate/general/sender_name'),
            "email" => $this->scopeConfig->getValue('email_affiliate/general/sender_email')
        ];
    }

    // public function sendEmail($data)
    // {
    //     $senderIdentityCode = $this->scopeConfig->getValue('email_affiliate/general/identity', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    //     if (empty($senderIdentityCode)) {
    //         $senderIdentityCode = "general";
    //     }
    //     $identity = $this->resolverInterface->resolve($senderIdentityCode);

    //     $fromName = $identity['name'];
    //     $fromEmail = $identity['email'];

    //     $template = "email_affiliate_active_affiliate_template";

    //     if (isset($data['template'])) {
    //         $template = $data['template'];
    //     };

    //     try {
    //         // template variables pass here
    //         $templateVars = [
    //             'affiliateCode' => $data['affiliateCode'],
    //             'username' => $data['username']
    //         ];

    //         if (isset($data['activeLink'])) {
    //             $templateVars['activeLink'] = $data['activeLink'];
    //         }

    //         if (isset($data['password'])) {
    //             $templateVars['password'] = $data['password'];
    //         }

    //         $storeId = $this->storeManager->getStore()->getId();

    //         $from = ['email' => $fromEmail, 'name' => $fromName];
    //         $this->inlineTranslation->suspend();

    //         $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    //         $templateOptions = [
    //             'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
    //             'store' => $storeId
    //         ];
    //         $transport = $this->transportBuilder->setTemplateIdentifier($template, $storeScope)
    //             ->setTemplateOptions($templateOptions)
    //             ->setTemplateVars($templateVars)
    //             ->setFrom($from)
    //             ->addTo($data['toEmail'])
    //             ->getTransport();
    //         $transport->sendMessage();
    //         $this->inlineTranslation->resume();
    //     } catch (\Exception $e) {
    //         $this->writeLog("Chottvn\Affiliate\Helper\Data - Error: " . $e->getMessage());
    //         $this->_logger->info($e->getMessage());
    //     }
    // }

    public function getAffiliateCode($customerId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerObj = $objectManager->create('Magento\Customer\Model\ResourceModel\Customer\Collection');
        $collection = $customerObj->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['eq' => $customerId])
            ->addAttributeToFilter('affiliate_status', "activated")->load();
        $customerModel = $collection->getLastItem();
        $affiliateAccountId = '';
        if ($customerModel) {
            $affiliateAccountId = $customerModel->getData('affiliate_code');
        }
        return $affiliateAccountId;
    }

    public function getGrandTotal($afterMonth, $customerID)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $timefc = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $beforeMonth = date('Y-m-01 00:00:00', strtotime($afterMonth . " - 5 months"));
        $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
        $conn = $connection->getConnection();
        $select = $conn->select()
            ->from(
                array('main_table' => 'sales_order'),
                array(null)
            )
            ->join(
                ['sales_item' => 'sales_order_item'],
                'main_table.entity_id = sales_item.order_id',
                array('SUM((sales_item.qty_ordered - sales_item.qty_refunded)  *  sales_item.base_price) as sumTotal')
            )
            ->where("sales_item.created_at >= '$beforeMonth' AND sales_item.created_at <= '$afterMonth' ")
            ->where('main_table.status IN (?)', ['finished', 'returned_and_finished'])
            ->where('main_table.affiliate_account_id = ?', $customerID);
        // $this->writeLog('#QuerygetGrandTotal'.$select->__toString());
        $data = $conn->fetchRow($select);
        return $data;
    }
    public function getAllItemsOrderSuccess($afterMonth, $customerID)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $timefc = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $beforeMonth = date('Y-m-01 00:00:00', strtotime($afterMonth . " - 5 months"));
        $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
        $conn = $connection->getConnection();
        $select = $conn->select()
            ->from(
                array('main_table' => 'sales_order'),
                array(null)
            )
            ->join(
                ['sales_item' => 'sales_order_item'],
                'main_table.entity_id = sales_item.order_id',
                array('SUM((sales_item.qty_ordered - sales_item.qty_refunded)  *  sales_item.base_price) as sumTotal')
            )
            ->join(
                ['ves_brand' => 'ves_brand'],
                'sales_item.product_brand_id = ves_brand.brand_id',
                array('ves_brand.thumbnail', 'ves_brand.name')
            )
            ->where("sales_item.created_at >= '$beforeMonth' AND sales_item.created_at <= '$afterMonth' ")
            ->where('main_table.status IN (?)', ['finished', 'returned_and_finished'])
            ->where('main_table.affiliate_account_id = ?', $customerID);
        $select->group('ves_brand.thumbnail', 'ves_brand.name');
        $data = $conn->fetchAll($select);
        // $this->writeLog('#getAllItemsOrderSuccess'.$select->__toString());
        return $data;
    }
    public function countOrderSuccess($afterMonth, $customerID)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $timefc = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $beforeMonth = date('Y-m-01 00:00:00', strtotime($afterMonth . " - 5 months"));
        $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
        $conn = $connection->getConnection();
        $select = $conn->select()
            ->from(
                array('main_table' => 'sales_order'),
                array('count(main_table.entity_id) as countOrder', 'main_table.status')
            )
            ->where("main_table.created_at >= '$beforeMonth' AND main_table.created_at <= '$afterMonth' ")
            ->where('main_table.status IN (?)', ['finished', 'returned_and_finished'])
            ->where('main_table.affiliate_account_id = ?', $customerID);
        $data = $conn->fetchRow($select);
        // $this->writeLog('#countOrderSuccess'.$select->__toString());
        return $data;
    }

    public function countTakeInitiativeOrderSuccess($afterMonth, $customerID, $numberphone)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $timefc = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $beforeMonth = date('Y-m-01 00:00:00', strtotime($afterMonth . " - 5 months"));
        $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
        $conn = $connection->getConnection();
        $select = $conn->select()
            ->from(
                array('main_table' => 'sales_order'),
                array('count(main_table.entity_id) as countOrder', 'main_table.status')
            )
            ->where("main_table.created_at >= '$beforeMonth' AND main_table.created_at <= '$afterMonth' ")
            ->where('main_table.status IN (?)', ['finished', 'returned_and_finished'])
            ->where('main_table.chott_customer_phone_number = ?', $numberphone)
            ->where('main_table.affiliate_account_id = ?', $customerID);
        // $this->writeLog('#countTakeInitiativeOrderSuccess'.$select->__toString());
        $data = $conn->fetchRow($select);

        return $data;
    }
    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/affiliate_approve.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        switch ($type) {
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
