<?php

namespace Chottvn\Affiliate\Controller\Register;

use Magento\Framework\Controller\ResultFactory;
use Chottvn\Affiliate\Helper\Log as HelperAffiliateLog;
use Chottvn\Affiliate\Model\Log as AffiliateLog;
use Chottvn\Affiliate\Helper\Data;

class Create extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_request;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Customer\Model\CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    protected $accountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Customer\Api\Data\RegionInterfaceFactory
     */
    protected $regionDataFactory;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Chottvn\SigninPhoneNumber\Model\Handler\Signin
     */
    protected $signInPhoneNubmer;

    /**
     * @var \Magento\Customer\Model\Group
     */
    protected $groupCustomer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PhoneVerificationRepository
     */
    protected $phoneVerificationRepository;

    /**
     * @var Session
     */
    protected $coreSession;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerModel;

    /**
     * @var \Magento\Customer\Model\Address
     */
    protected $addressModel;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @var \Chottvn\SigninPhoneNumber\Model\PhoneVerificationRepository
     */
    protected $phoneVerification;

    /**
     * @var HelperAffiliateLog
     */
    protected $helperAffiliateLog;

    protected $_affiliateHelper;

    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerExtractor $customerExtractor,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Chottvn\SigninPhoneNumber\Model\Handler\Signin $signInPhoneNubmer,
        \Magento\Customer\Model\Group $groupCustomer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Chottvn\SigninPhoneNumber\Api\PhoneVerificationRepositoryInterface $phoneVerificationRepository,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Customer\Model\Address $addressModel,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Chottvn\SigninPhoneNumber\Model\PhoneVerificationRepository $phoneVerification,
        \Magento\Framework\App\Action\Context $context,
        HelperAffiliateLog $helperAffiliateLog,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Data $affiliateHelper
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_request = $request;
        $this->formKeyValidator = $formKeyValidator;
        $this->session = $customerSession;
        $this->customerExtractor = $customerExtractor;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->formFactory = $formFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->signInPhoneNubmer = $signInPhoneNubmer;
        $this->groupCustomer = $groupCustomer;
        $this->storeManager = $storeManager;
        $this->phoneVerificationRepository = $phoneVerificationRepository;
        $this->coreSession = $coreSession;
        $this->customerModel = $customerModel;
        $this->addressModel = $addressModel;
        $this->addressFactory = $addressFactory;
        $this->phoneVerification = $phoneVerification;
        $this->_helperAffiliateLog = $helperAffiliateLog;
        $this->_affiliateHelper = $affiliateHelper;
        $this->scopeConfig = $scopeConfig;

        return parent::__construct($context);
    }

    /**
     * Get PostCode from City Id
     *
     * @param 
     * @return 
     */
    protected function getPostcodeFromAddress($cityid)
    {
        $postCode =  null;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        try {
            if (!empty($cityid)) {
                $city = $objectManager->get('\Chottvn\Address\Model\ResourceModel\City\CollectionFactory')->create()->addFieldToFilter(
                    'city_id',
                    ['eq' => $cityid]
                );
                if ($city) {
                    $city = $city->getFirstItem();
                    $postCode = $city->getPostcode();
                }
            }
        } catch (\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }
        return $postCode;
    }

    /**
     * Add address to customer during create account
     *
     * @return AddressInterface|null
     */
    protected function extractAddress()
    {
        if (!$this->getRequest()->getPost('create_address')) {
            return null;
        }

        $addressForm = $this->formFactory->create('customer_address', 'customer_register_address');
        $allowedAttributes = $addressForm->getAllowedAttributes();

        $addressData = [];

        $regionDataObject = $this->regionDataFactory->create();
        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $value = $this->getRequest()->getParam($attributeCode) ? $this->getRequest()->getParam($attributeCode) : '';

            if ($value === null) {
                continue;
            }
            switch ($attributeCode) {
                case 'firstname':
                    if ($this->session->isLoggedIn()) {
                        $addressData[$attributeCode] = $this->session->getCustomerData()->getFirstname();
                    } else {
                        $addressData[$attributeCode] = $value;
                    }
                    break;

                case 'email':
                    $value = $this->getRequest()->getParam('customer_email');
                    $addressData[$attributeCode] = $value;
                    break;

                case 'lastname':
                    if ($value == '') {
                        $addressData[$attributeCode] = '-';
                    }
                    break;

                case 'telephone':
                    $value = $this->getRequest()->getParam('phone_number');
                    $addressData[$attributeCode] = $value;
                    break;

                case 'region_id':
                    $regionDataObject->setRegionId($value);
                    break;
                case 'region':
                    $regionDataObject->setRegion($value);
                    break;
                case 'city_id':
                case 'township_id':
                    $addressData[$attributeCode] = (int) $value;
                    break;
                case 'street':
                    if ($value[0]) {
                        $addressData[$attributeCode] = $value;
                    } else {
                        $addressData[$attributeCode] = array('N/A');
                    }
                    //$addressData[$attributeCode] = $value ? $value : array('-');
                    break;
                case 'postcode':
                    $postcode = $this->getPostcodeFromAddress($this->getRequest()->getParam('city_id')) ? $this->getPostcodeFromAddress($this->getRequest()->getParam('city_id')) : 'N/A';
                    $addressData[$attributeCode] = $postcode;
                    break;
                default:
                    $addressData[$attributeCode] = $value;
            }
        }
        $addressDataObject = $this->addressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $addressData,
            \Magento\Customer\Api\Data\AddressInterface::class
        );

        $addressDataObject->setRegion($regionDataObject);

        $addressDataObject->setIsDefaultBilling(
            $this->getRequest()->getParam('default_billing', false)
        );
        // $addressDataObject->setIsDefaultBilling(
        //     $this->getRequest()->getParam('default_billing', false)
        // )->setIsDefaultShipping(
        //     $this->getRequest()->getParam('default_shipping', false)
        // );
        // echo '<pre>'; print_r($addressData); echo '</pre>';exit;
        return $addressDataObject;
    }

    public function execute()
    {
        // check form post validate or empty
        if (
            !$this->getRequest()->isPost()
            || !$this->formKeyValidator->validate($this->getRequest())
        ) {
            $response = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData([
                    'status'  => "error",
                    'message' => __('This is the required case'),
                    'result' => $this->_request->getPostValue()
                ]);

            return $response;
        }

        $this->session->regenerateId();
        try {
            // get group_id customer
            $cus_grp_code = 'Affiliate';
            $group_obj = $this->groupCustomer;
            $existing_group = $group_obj->load($cus_grp_code, 'customer_group_code');
            $group_id = $existing_group->getId();

            // check request phone number
            if ($this->session->isLoggedIn()) {
                $phone_number = $this->session->getCustomerData()->getCustomAttribute('phone_number')->getValue();
            } else {
                $phone_number = $this->getRequest()->getParam('phone_number');
            }

            $request_customer = $this->signInPhoneNubmer->getByPhoneNumber($phone_number);
            if ($request_customer) {
                $isPhoneVerified = $this->phoneVerification->isActivated($phone_number);
            } else {
                $isPhoneVerified = false;
            }

            if ($request_customer && $isPhoneVerified == true) {
                // check phone number is ctv ?
                $customer_exist = $this->customerFactory->create()->load($request_customer->getId());

                if ($customer_exist->getAffiliateStatus() && $customer_exist->getAffiliateStatus() != 're-register') {
                    if ($customer_exist->getAffiliateStatus() == 'approved') {
                        // approved
                        $response = $this->resultFactory
                            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                            ->setData([
                                'status'  => "error",
                                'message' => __("Your affiliate account was approved. Please check your email to activate account.")
                            ]);
                    } elseif ($customer_exist->getAffiliateStatus() == 'rejected') {
                        // rejected
                        $response = $this->resultFactory
                            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                            ->setData([
                                'status'  => "error",
                                'message' => __("Your affiliate account was rejected.")
                            ]);
                    } else {
                        // if exist account with affiliate status
                        $response = $this->resultFactory
                            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                            ->setData([
                                'status'  => "error",
                                'message' => __("Your account registered Affiliate Program.")
                            ]);
                    }

                    return $response;
                } elseif ($customer_exist && $this->session->isLoggedIn() == false) {
                    // if exist account && not yet login
                    $response = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData([
                            'status'  => "error",
                            'message' => __("You are a member store. Please login to register affiliate program.")
                        ]);

                    return $response;
                } else {
                    // check email exist
                    $request_email = $this->getRequest()->getParam('customer_email');
                    $existEmailCustomer = $this->customerModel->getCollection()->addAttributeToFilter('customer_email', $request_email);

                    // check email exists in system
                    $current_email = $request_customer->getCustomAttribute('customer_email') ? $request_customer->getCustomAttribute('customer_email')->getValue() : '';
                    if ($request_email != $current_email && count($existEmailCustomer) > 0) {
                        $response = $this->resultFactory
                            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                            ->setData([
                                'status'  => "error",
                                'message' => __("Email was registered.")
                            ]);

                        return $response;
                    } else {
                        // if not exist account
                        $customer_current = $this->customerRepository->getById($customer_exist->getId());

                        // get info address
                        $shippingAddressId = $customer_current->getDefaultShipping();
                        $addressShippingCustomer = $this->addressModel->load($shippingAddressId);

                        // set new email
                        $current_email = $request_customer->getCustomAttribute('customer_email') ? $request_customer->getCustomAttribute('customer_email')->getValue() : '';
                        if ($request_email != $current_email) {
                            //$customer_current->setEmail($request_email);
                            $customer_current->setCustomAttribute('customer_email', $request_email);
                        }
                        // update dob
                        if ($this->getRequest()->getParam('dob') != '') {
                            $dob = explode("/", $this->getRequest()->getParam('dob'));
                            $dob = intval($dob[2]) . "-" . intval($dob[1]) . "-" . intval($dob[0]);
                            $customer_current->setDob(date('Y-m-d', strtotime($dob)));
                        }

                        // update registered account affiliate
                        $customer_current->setCustomAttribute('affiliate_status', 'phone_verified');

                        // update group_id
                        // dont change group id
                        // $customer_current->setGroupId($group_id);

                        // saving data customer
                        $this->customerRepository->save($customer_current);

                        // Phuoc add 2020-08-12 for store customer bank info
                        $this->saveBankCustomer($customer_current);

                        // Phuoc add 2020-09-23 for send email new affiliate
                        if ($this->scopeConfig->getValue('email_affiliate/new_affiliate/enabled') == 1) {
                            $this->_affiliateHelper->sendNewAffiliateEmail();
                        }

                        // Save log
                        $this->_helperAffiliateLog->saveLog(["account_id" => $customer_current->getId(), "event" => AffiliateLog::EVENT_REGISTERED]);
                        $this->_helperAffiliateLog->saveLog(["account_id" => $customer_current->getId(), "event" => AffiliateLog::EVENT_PHONE_VERIFIED]);

                        // get info address
                        $region_id = $this->getRequest()->getParam('region_id');
                        $region = $this->getRequest()->getParam('region');
                        $city_id = $this->getRequest()->getParam('city_id');
                        $city = $this->getRequest()->getParam('city') ? $this->getRequest()->getParam('city') : 'N/A';
                        $township_id = $this->getRequest()->getParam('township_id') ? $this->getRequest()->getParam('township_id') : 'N/A';
                        $township = $this->getRequest()->getParam('township') ? $this->getRequest()->getParam('township') : 'N/A';
                        $street = $this->getRequest()->getParam('street')[0] ? $this->getRequest()->getParam('street')[0] : 'N/A';
                        $country_id = $this->getRequest()->getParam('country_id') ? $this->getRequest()->getParam('country_id') : 'VN';
                        $postcode = $this->getPostcodeFromAddress($city_id) ? $this->getPostcodeFromAddress($city_id) : 'N/A';
                        $phone_number = $this->getRequest()->getParam('phone_number');
                        $customer_email = $this->getRequest()->getParam('customer_email');
                        // update address
                        if (
                            $region_id != $addressShippingCustomer->getData('region_id')
                            || $city_id != $addressShippingCustomer->getData('city_id')
                            || $township_id != $addressShippingCustomer->getData('township_id')
                            || $street != $addressShippingCustomer->getData('street')
                        ) {
                            $address = $this->addressFactory->create();
                            $address->setCustomerId($customer_current->getId())
                                ->setFirstname($customer_current->getFirstname())
                                ->setLastname('-')
                                ->setCompany('')
                                ->setStreet($street)
                                ->setCountryId($country_id)
                                ->setRegion($region)
                                ->setRegionId($region_id)
                                ->setCityId($city_id)
                                ->setCity($city)
                                ->setTownshipId($township_id)
                                ->setTownship($township)
                                ->setPostcode($postcode)
                                ->setTelephone($phone_number)
                                ->setVatId('')
                                ->setEmail($customer_email)
                                ->setIsDefaultBilling('1')
                                ->setIsDefaultShipping(false)
                                ->setSaveInAddressBook('1');
                            $address->save();
                        }

                        $response = $this->resultFactory
                            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                            ->setData([
                                'status'  => "ok",
                                'message' => __("Create affiliate account successfull!"),
                                'redirect_url' => $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) . 'affiliate/register/success'
                            ]);
                    }
                }
            } else {
                // create new account with member login. donot need verify phone
                // create new account, need verify phone
                // request phone,email
                $phone_number = $this->getRequest()->getParam('phone_number');
                $email = $this->getRequest()->getParam('customer_email');
                $existEmailCustomer = $this->customerModel->getCollection()->addAttributeToFilter('customer_email', $email);

                // error if email exist
                if (count($existEmailCustomer) > 0) {
                    $response = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData([
                            'status'  => "error",
                            'message' => __("Email was registered.")
                        ]);

                    return $response;
                }

                // save customer
                $address = $this->extractAddress();
                $addresses = $address === null ? [] : [$address];
                //print_r($addresses);exit;
                $customer = $this->customerExtractor->extract('customer_account_create', $this->_request);
                $customer->setAddresses($addresses);
                $customer->setGroupId($group_id);
                $customer->setLastname('-');

                //$customer->setAffiliateStatus('registered');
                $customer = $this->accountManagement->createAccount($customer);
                //print_r(get_class_methods($customer));exit;

                // Phuoc add 2020-08-12 for store customer bank info
                $this->saveBankCustomer($customer);

                // Phuoc add 2020-09-23 for send email new affiliate
                if ($this->scopeConfig->getValue('email_affiliate/new_affiliate/enabled') == 1) {
                    $this->_affiliateHelper->sendNewAffiliateEmail();
                }

                if (true) {
                    // Send OTP
                    $phoneNumberAttribute = $customer->getCustomAttribute('phone_number');
                    $this->phoneVerificationRepository->sendOTP($customer->getId());
                    $this->coreSession->setVerifyingPhone($phoneNumberAttribute->getValue());

                    // save affiliate code & status
                    $customer_current = $this->customerRepository->getById($customer->getId());

                    // update registered account affiliate
                    $customer_current->setCustomAttribute('affiliate_status', 'registered');
                    $customer_current->setCustomAttribute('customer_email', $email);

                    // update dob
                    if ($this->getRequest()->getParam('dob') != '') {
                        $dob = explode("/", $this->getRequest()->getParam('dob'));
                        $dob = intval($dob[2]) . "-" . intval($dob[1]) . "-" . intval($dob[0]);
                        $customer_current->setDob(date('Y-m-d', strtotime($dob)));
                    }

                    // saving data customer
                    $this->customerRepository->save($customer_current);

                    // Save log
                    $this->_helperAffiliateLog->saveLog(["account_id" => $customer->getId(), "event" => AffiliateLog::EVENT_REGISTERED]);

                    $response = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData([
                            'status'  => "ok",
                            'message' => __("Create affiliate account successfull!"),
                            'redirect_url' => $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) . 'affiliate/verify/code'
                        ]);
                } else {
                    $response = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData([
                            'status'  => "error",
                            'message' => __("We can't save the affiliate customer. Please contact with Admin.")
                        ]);
                }
            }
        } catch (\Exception $e) {
            $this->writeLog($e->getMessage());
            $response = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData([
                    'status'  => "error",
                    'message' => __("Email or Phone number was registered.")
                ]);
        }

        return $response;
    }

    /**
     * Phuoc add 2020-08-12 for store customer bank info
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    protected function saveBankCustomer($customer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerBankAccount = $objectManager->create('Chottvn\PaymentAccount\Model\CustomerBankAccount');
        $customerBankAccount->setData([
            "customer_id" => $customer->getId(),
            "paymentaccount_bank_id" => $this->getRequest()->getParam('bank_id'),
            "account_owner" => "-",
            "account_number" => $this->getRequest()->getParam('bank_number'),
            "order" => 0
        ]);
        $customerBankAccount->save();

        if ($customerBankAccount->getId()) {
            // Save log bank
            $this->_helperAffiliateLog->saveLogWithResource([
                "account_id" => $customer->getId(),
                "resource_type" => 'chottvn_paymentaccount_customerba',
                "resource_id" => $customerBankAccount->getId(),
                "event" => AffiliateLog::EVENT_BANK_ACCOUNT_CHANGED,
                "value" => [
                    "paymentaccount_bank_id" => (int)$customerBankAccount->getData('paymentaccount_bank_id'),
                    "account_owner" => $customerBankAccount->getData('account_owner'),
                    "account_number" => $customerBankAccount->getData('account_number'),
                    "bank_branch" => $customerBankAccount->getData('bank_branch'),
                    "note" => $customerBankAccount->getData('note'),
                    "status" => $customerBankAccount->getData('status')
                ]
            ]);
        }
    }

    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/affiliate_create.log');
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
