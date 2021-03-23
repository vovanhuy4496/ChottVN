<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\Affiliate\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\Customer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Chottvn\SigninPhoneNumber\Api\PhoneVerificationRepositoryInterface as PhoneVerificationRepository;

/**
 * Adminhtml customer view personal information sales block.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PersonalInfo extends \Magento\Backend\Block\Template
{
    /**
     * Interval in minutes that shows how long customer will be marked 'Online'
     * since his last activity. Used only if it's impossible to get such setting
     * from configuration.
     */
    const DEFAULT_ONLINE_MINUTES_INTERVAL = 15;

    /**
     * Customer
     *
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $customer;

    /**
     * Customer log
     *
     * @var \Magento\Customer\Model\Log
     */
    protected $customerLog;

    /**
     * Customer logger
     *
     * @var \Magento\Customer\Model\Logger
     */
    protected $customerLogger;

    /**
     * Customer registry
     *
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * Account management
     *
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * Customer group repository
     *
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * Customer data factory
     *
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * Address helper
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $addressHelper;

    /**
     * Date time
     *
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Address mapper
     *
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * Data object helper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

	protected $_resourceConnection;

    /**
     * @var PhoneVerificationRepository
     */
    protected $phoneVerificationRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    protected $aclRetriever;
    protected $authSession;
    

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Registry $registry
     * @param Mapper $addressMapper
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Model\Logger $customerLogger
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        AccountManagementInterface $accountManagement,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Registry $registry,
        Mapper $addressMapper,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Logger $customerLogger,
		\Magento\Framework\App\ResourceConnection $resourceConnection,
        PhoneVerificationRepository $phoneVerificationRepository,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever,
        \Magento\Backend\Model\Auth\Session $authSession, 
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->accountManagement = $accountManagement;
        $this->groupRepository = $groupRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->addressHelper = $addressHelper;
        $this->dateTime = $dateTime;
        $this->addressMapper = $addressMapper;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerLogger = $customerLogger;
		$this->_resourceConnection = $resourceConnection;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->phoneVerificationRepository = $phoneVerificationRepository;
        $this->customerRepository = $customerRepository;
        $this->aclRetriever = $aclRetriever;
        $this->authSession = $authSession;

        parent::__construct($context, $data);
    }

    /**
     * Set customer registry
     *
     * @param \Magento\Framework\Registry $customerRegistry
     * @return void
     * @deprecated 100.1.0
     */
    public function setCustomerRegistry(\Magento\Customer\Model\CustomerRegistry $customerRegistry)
    {
        $this->customerRegistry = $customerRegistry;
    }

    /**
     * Get customer registry
     *
     * @return \Magento\Customer\Model\CustomerRegistry
     * @deprecated 100.1.0
     */
    public function getCustomerRegistry()
    {

        if (!($this->customerRegistry instanceof \Magento\Customer\Model\CustomerRegistry)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Customer\Model\CustomerRegistry::class
            );
        } else {
            return $this->customerRegistry;
        }
    }

    /**
     * Retrieve customer object
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        if (!$this->customer) {
            $this->customer = $this->customerDataFactory->create();
            $data = $this->_backendSession->getCustomerData();
            $this->dataObjectHelper->populateWithArray(
                $this->customer,
                $data['account'],
                \Magento\Customer\Api\Data\CustomerInterface::class
            );
        }
        return $this->customer;
    }

    /**
     * Retrieve customer id
     *
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Retrieves customer log model
     *
     * @return \Magento\Customer\Model\Log
     */
    protected function getCustomerLog()
    {
        if (!$this->customerLog) {
            $this->customerLog = $this->customerLogger->get(
                $this->getCustomer()->getId()
            );
        }

        return $this->customerLog;
    }

    /**
     * Returns customer's created date in the assigned store
     *
     * @return string
     */
    public function getStoreCreateDate()
    {
        $createdAt = $this->getCustomer()->getCreatedAt();
        try {
            return $this->formatDate(
                $createdAt,
                \IntlDateFormatter::MEDIUM,
                true,
                $this->getStoreCreateDateTimezone()
            );
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            return '';
        }
    }

    /**
     * Retrieve store default timezone from configuration
     *
     * @return string
     */
    public function getStoreCreateDateTimezone()
    {
        return $this->_scopeConfig->getValue(
            $this->_localeDate->getDefaultTimezonePath(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getCustomer()->getStoreId()
        );
    }

    /**
     * Get customer creation date
     *
     * @return string
     */
    public function getCreateDate()
    {
        return $this->formatDate(
            $this->getCustomer()->getCreatedAt(),
            \IntlDateFormatter::MEDIUM,
            true
        );
    }

    /**
     * Check if account is confirmed
     *
     * @return \Magento\Framework\Phrase
     */
    public function getIsConfirmedStatus()
    {
        $id = $this->getCustomerId();
        switch ($this->accountManagement->getConfirmationStatus($id)) {
            case AccountManagementInterface::ACCOUNT_CONFIRMED:
                return __('Confirmed');
            case AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED:
                return __('Confirmation Required');
            case AccountManagementInterface::ACCOUNT_CONFIRMATION_NOT_REQUIRED:
                return __('Confirmation Not Required');
        }
        return __('Indeterminate');
    }

    /**
     * Retrieve store
     *
     * @return null|string
     */
    public function getCreatedInStore()
    {
        return $this->_storeManager->getStore(
            $this->getCustomer()->getStoreId()
        )->getName();
    }

    /**
     * Retrieve billing address html
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getBillingAddressHtml()
    {
        try {
            $address = $this->accountManagement->getDefaultBillingAddress($this->getCustomer()->getId());
        } catch (NoSuchEntityException $e) {
            return __('The customer does not have default billing address.');
        }

        if ($address === null) {
            return __('The customer does not have default billing address.');
        }

        return $this->addressHelper->getFormatTypeRenderer(
            'html'
        )->renderArray(
            $this->addressMapper->toFlatArray($address)
        );
    }

    /**
     * Retrieve group name
     *
     * @return string|null
     */
    public function getGroupName()
    {
        $customer = $this->getCustomer();
        if ($groupId = $customer->getId() ? $customer->getGroupId() : null) {
            if ($group = $this->getGroup($groupId)) {
                return $group->getCode();
            }
        }

        return null;
    }

    /**
     * Retrieve customer group by id
     *
     * @param int $groupId
     * @return \Magento\Customer\Api\Data\GroupInterface|null
     */
    private function getGroup($groupId)
    {
        try {
            $group = $this->groupRepository->getById($groupId);
        } catch (NoSuchEntityException $e) {
            $group = null;
        }
        return $group;
    }

    /**
     * Returns timezone of the store to which customer assigned.
     *
     * @return string
     */
    public function getStoreLastLoginDateTimezone()
    {
        return $this->_scopeConfig->getValue(
            $this->_localeDate->getDefaultTimezonePath(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getCustomer()->getStoreId()
        );
    }

    /**
     * Get customer's current status.
     *
     * Customer considered 'Offline' in the next cases:
     *
     * - customer has never been logged in;
     * - customer clicked 'Log Out' link\button;
     * - predefined interval has passed since customer's last activity.
     *
     * In all other cases customer considered 'Online'.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getCurrentStatus()
    {
        $lastLoginTime = $this->getCustomerLog()->getLastLoginAt();

        // Customer has never been logged in.
        if (!$lastLoginTime) {
            return __('Offline');
        }

        $lastLogoutTime = $this->getCustomerLog()->getLastLogoutAt();

        // Customer clicked 'Log Out' link\button.
        if ($lastLogoutTime && strtotime($lastLogoutTime) > strtotime($lastLoginTime)) {
            return __('Offline');
        }

        // Predefined interval has passed since customer's last activity.
        $interval = $this->getOnlineMinutesInterval();
        $currentTimestamp = (new \DateTime())->getTimestamp();
        $lastVisitTime = $this->getCustomerLog()->getLastVisitAt();

        if ($lastVisitTime && $currentTimestamp - strtotime($lastVisitTime) > $interval * 60) {
            return __('Offline');
        }

        return __('Online');
    }

    /**
     * Get customer last login date.
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getLastLoginDate()
    {
        $date = $this->getCustomerLog()->getLastLoginAt();

        if ($date) {
            return $this->formatDate($date, \IntlDateFormatter::MEDIUM, true);
        }

        return __('Never');
    }

    /**
     * Returns customer last login date in store's timezone.
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getStoreLastLoginDate()
    {
        $date = strtotime($this->getCustomerLog()->getLastLoginAt());

        if ($date) {
            $date = $this->_localeDate->scopeDate($this->getCustomer()->getStoreId(), $date, true);
            return $this->formatDate($date, \IntlDateFormatter::MEDIUM, true);
        }

        return __('Never');
    }

    /**
     * Returns interval that shows how long customer will be considered 'Online'.
     *
     * @return int Interval in minutes
     */
    protected function getOnlineMinutesInterval()
    {
        $configValue = $this->_scopeConfig->getValue(
            'customer/online_customers/online_minutes_interval',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return (int)$configValue > 0 ? (int)$configValue : self::DEFAULT_ONLINE_MINUTES_INTERVAL;
    }

    /**
     * Get customer account lock status
     *
     * @return \Magento\Framework\Phrase
     */
    public function getAccountLock()
    {
        $customerModel = $this->getCustomerRegistry()->retrieve($this->getCustomerId());
        $customerStatus = __('Unlocked');
        if ($customerModel->isCustomerLocked()) {
            $customerStatus = __('Locked');
        }
        return $customerStatus;
    }

    /**
     * Retrieve Affiliate Code
     *
     * @return string|null
     */
    public function getAffiliateCode()
    {
        $customer = $this->customerRepository->getById($this->getCustomerId());
        if($affiliateCode = $customer->getCustomAttribute('affiliate_code')){
            return $affiliateCode->getValue();
        }

        return null;
    }

    /**
     * Retrieve Affiliate Status
     *
     * @return string|null
     */
    public function getAffiliateStatus()
    {
        $customer = $this->customerRepository->getById($this->getCustomerId());
        if($affiliateCode = $customer->getCustomAttribute('affiliate_status')){
            return $affiliateCode->getValue();
        }

        return null;
    }

    public function getUrlApproveAffiliate() 
    {
        return $this->urlBuilder->getUrl('*/*/approve', ['id' => $this->getCustomerId()]);
    }

    public function getUrlVerifyAffiliate() 
    {
        return $this->urlBuilder->getUrl('*/*/verify', ['id' => $this->getCustomerId()]);
    }

    public function getUrlRejectAffiliate() 
    {
        return $this->urlBuilder->getUrl('*/*/reject', ['id' => $this->getCustomerId()]);
    }

    public function getUrlReRegisterAffiliate() 
    {
        return $this->urlBuilder->getUrl('*/*/reregister', ['id' => $this->getCustomerId()]);
    }

    public function getUrlActiveAffiliate() 
    {
        return $this->urlBuilder->getUrl('*/*/active', ['id' => $this->getCustomerId()]);
    }

    public function getUrlRequestIdentityCard() 
    {
        return $this->urlBuilder->getUrl('*/*/requestidentitycard', ['id' => $this->getCustomerId()]);
    }

    public function getUrlFreezedAffiliate() 
    {
        return $this->urlBuilder->getUrl('*/*/freezed', ['id' => $this->getCustomerId()]);
    }

    public function getUrlUnfreezedAffiliate() 
    {
        return $this->urlBuilder->getUrl('*/*/unfreezed', ['id' => $this->getCustomerId()]);
    }

    public function checkPhoneVerified() 
    {
        $customer = $this->customerRepository->getById($this->getCustomerId());
        if($phoneNumberAttr = $customer->getCustomAttribute('phone_number')){
            if($this->phoneVerificationRepository->isActivated($phoneNumberAttr->getValue())){
                return true;
            }
        }
        
        return false;
    }

    public function validateUniquePhonenumber()
    {
        $customer = $this->customerRepository->getById($this->getCustomerId());
        // If have activated phone number then return phone already existed
            $activatedCustomerId = $this->phoneVerificationRepository->getValidatedCustomerId($customer->getCustomAttribute('phone_number')->getValue());
        if(!empty($activatedCustomerId)){
            if($activatedCustomerId != $this->getCustomerId()){
                return false;
            }
        }
        return true;
    }
    
    public function getIsAllowed() { // like a string, e.g. "Magento_Backend::cache" for Cache Management
        $role = $this->authSession->getUser()->getRole();
        $resources = $this->aclRetriever->getAllowedResourcesByRole($role->getId());
        return in_array("Magento_Backend::all", $resources);
    }

    /**
     * Retrieve Status
     *
     * @return string|null
     */
    public function getStatus()
    {
        $customerModel = $this->getCustomerRegistry()->retrieve($this->getCustomerId());

        return $customerModel->getData('is_disabled');
    }

    public function getUrlLock() 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $context = $objectManager->get('\Magento\Backend\Block\Template\Context');
        $urlBuilder = $context->getUrlBuilder();

        return $urlBuilder->getUrl('customer/*/lock', ['id' => $this->getCustomerId()]);
    }

    public function getUrlUnLock() 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $context = $objectManager->get('\Magento\Backend\Block\Template\Context');
        $urlBuilder = $context->getUrlBuilder();

        return $urlBuilder->getUrl('customer/*/unlock', ['id' => $this->getCustomerId()]);
    }

    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Affiliate_PersonalInfo.log');
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
