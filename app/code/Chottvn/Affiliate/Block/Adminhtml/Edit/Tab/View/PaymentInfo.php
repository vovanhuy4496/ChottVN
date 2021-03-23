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
use Chottvn\PaymentAccount\Model\CustomerBankAccountFactory;

/**
 * Adminhtml customer view personal information sales block.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentInfo extends \Magento\Backend\Block\Template
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

    /**
     * @var CustomerBankAccountFactory
     */
    public $customBankAccountFactory;

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
        array $data = [],
        CustomerBankAccountFactory $customBankAccountFactory
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
        $this->customBankAccountFactory = $customBankAccountFactory;

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
     * @param 
     *
     * @return Collection
     */
    public function getCustomerBankAccountCollection()
    {
        $collection = $this->customBankAccountFactory
        ->create()
        ->getCollection()
        ->addFieldToFilter('customer_id', $this->getCustomerId());

        return $collection;
    }
}
