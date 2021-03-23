<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\SigninPhoneNumber\Rewrite\Magento\Customer\Controller\Account;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AddressRegistry;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\Phrase;

/**
 * Class EditPost
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPost extends \Magento\Customer\Controller\Account\EditPost
{
    /**
     * Form code for data extractor
     */
    const FORM_DATA_EXTRACTOR_CODE = 'customer_account_edit';

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Customer\Model\EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var Mapper
     */
    private $customerMapper;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var \Magento\Customer\Model\Address
     */
    protected $addressModel;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param Validator $formKeyValidator
     * @param CustomerExtractor $customerExtractor
     * @param Escaper|null $escaper
     * @param AddressRegistry|null $addressRegistry
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        Validator $formKeyValidator,
        \Magento\Customer\Model\Address $addressModel,
        CustomerExtractor $customerExtractor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        ?Escaper $escaper = null,
        AddressRegistry $addressRegistry = null
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerAccountManagement,
            $customerRepository,
            $formKeyValidator,
            $customerExtractor
        );
        $this->addressModel = $addressModel;
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerExtractor = $customerExtractor;
        $this->addressFactory = $addressFactory;
        $this->storeManager = $storeManager;
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
        $this->addressRegistry = $addressRegistry ?: ObjectManager::getInstance()->get(AddressRegistry::class);
    }

    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    private function getAuthentication()
    {

        if (!($this->authentication instanceof AuthenticationInterface)) {
            return ObjectManager::getInstance()->get(
                \Magento\Customer\Model\AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated 100.1.0
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/edit');

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }
    
    
      /**
     * Get PostCode from City Id
     *
     * @param 
     * @return 
     */
    protected function getPostcodeFromAddress($cityid){
        $postCode =  null;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        try {         
            if(!empty($cityid)){                
                $city = $objectManager->get('\Chottvn\Address\Model\ResourceModel\City\CollectionFactory')->create()->addFieldToFilter(
                    'city_id',
                    ['eq' => $cityid]
                );                
                if ($city){
                    $city = $city->getFirstItem();
                    $postCode = $city->getPostcode();
                }
            }            
        }
        catch(\Exception $e){
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }              
        return $postCode;
    }

    /**
     * Account editing action completed successfully event
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customerCandidateDataObject
     * @return void
     */
    private function dispatchSuccessEvent(\Magento\Customer\Api\Data\CustomerInterface $customerCandidateDataObject)
    {
        if (empty($customerCandidateDataObject->getEmail()) ){
            return;
        }
        $params = array();        
        $params['email'] = $customerCandidateDataObject->getEmail();
        $this->_eventManager->dispatch(
            'customer_account_edited',
            //['email' => $customerCandidateDataObject->getEmail()]
            $params
        );        
    }

    /**
     * Get customer data object
     *
     * @param int $customerId
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function getCustomerDataObject($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * Create Data Transfer Object of customer candidate
     *
     * @param \Magento\Framework\App\RequestInterface $inputData
     * @param \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function populateNewCustomerDataObject(
        \Magento\Framework\App\RequestInterface $inputData,
        \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
    ) {
        $attributeValues = $this->getCustomerMapper()->toFlatArray($currentCustomerData);
        $customerDto = $this->customerExtractor->extract(
            self::FORM_DATA_EXTRACTOR_CODE,
            $inputData,
            $attributeValues
        );
        $customerDto->setId($currentCustomerData->getId());
        if (!$customerDto->getAddresses()) {
            $customerDto->setAddresses($currentCustomerData->getAddresses());
        }
        if (!$inputData->getParam('change_email')) {
            $customerDto->setEmail($currentCustomerData->getEmail());
        }

        return $customerDto;
    }
    /**
     * Change customer email or password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());
        if ($validFormKey && $this->getRequest()->isPost()) {
            $currentCustomerDataObject = $this->getCustomerDataObject($this->session->getCustomerId());
            $customerCandidateDataObject = $this->populateNewCustomerDataObject(
                $this->_request,
                $currentCustomerDataObject
            );
            try {
                $gender = $this->getRequest()->getParam('gender') ? $this->getRequest()->getParam('gender'):'';
                if($gender == ''){
                    $response = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => "error",
                        'message' => __("Gender is the required case")
                    ]); 
                    return $response;
                }
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $encryptor = $objectManager->get('Magento\Framework\Encryption\EncryptorInterface');
                $current_password = $this->getRequest()->getParam('current_password');
                $password = $this->getCurrentPasswordHash($this->session->getCustomerId());
                $password = $password['password_hash'];
                $bool = $encryptor->validateHash($current_password, $password);
                $dob = $this->getRequest()->getParam('dob')? $this->getRequest()->getParam('dob'):'';
                if($bool === true){
                    // whether a customer enabled change email option
                    if (!empty($currentCustomerDataObject->getEmail()) ){
                        $this->processChangeEmailRequest($currentCustomerDataObject);
                    }
                    // whether a customer enabled change password option
                    // Magento\Customer\Model\Data\Customer
                    $phoneNumberAttribute = $currentCustomerDataObject->getCustomAttribute('phone_number');
                    $phoneNumber = $phoneNumberAttribute ? $phoneNumberAttribute->getValue() : null ;
                    $isPasswordChanged = $this->changeCustomerPassword($currentCustomerDataObject->getEmail(), $phoneNumber);
                    
                    // Email
                    $email = $this->getRequest()->getParam('email') ? $this->getRequest()->getParam('email'):'';                    
                    $customerCandidateDataObject->setEmail($email);
                    // DOB
                    if($dob){
                        $dob = explode("/", $dob);
                        $dob = intval($dob[2])."-".intval($dob[1])."-".intval($dob[0]);
                        $customerCandidateDataObject->setDob(date('Y-m-d',strtotime($dob)));
                    }else{
                        $customerCandidateDataObject->setDob('');
                    }
                    // Sex
                    $customerCandidateDataObject->setGender($gender);

                    // No need to validate customer address while editing customer profile
                    $this->disableAddressValidation($customerCandidateDataObject);
                    $this->customerRepository->save($customerCandidateDataObject);

                    if (!empty($currentCustomerDataObject->getEmail()) ){
                        $this->getEmailNotification()->credentialsChanged(
                            $customerCandidateDataObject,
                            $currentCustomerDataObject->getEmail(),
                            $isPasswordChanged
                        );
                    }
                  
                    // Address
                    // -- Prepare data form request
                    $countryId = $this->getRequest()->getParam('country_id') ? $this->getRequest()->getParam('country_id'):'VN';
                    $regionId = $this->getRequest()->getParam('region_id')? $this->getRequest()->getParam('region_id'):'';
                    $region = $this->getRequest()->getParam('region')? $this->getRequest()->getParam('region'):'';
                    $cityId = $this->getRequest()->getParam('city_id')? $this->getRequest()->getParam('city_id'):'';
                    $city = $this->getRequest()->getParam('city') ? $this->getRequest()->getParam('city'):'-';
                    $townshipId = $this->getRequest()->getParam('township_id') ? $this->getRequest()->getParam('township_id'):'-';
                    $township = $this->getRequest()->getParam('township') ? $this->getRequest()->getParam('township'):'-';
                    $street = $this->getRequest()->getParam('street')[0] ? $this->getRequest()->getParam('street')[0]:'-';                    
                    $postcode = $this->getPostcodeFromAddress($cityId) ? $this->getPostcodeFromAddress($cityId):'-';
                    // -- Update/Create to Billing Address
                    $billingAddressId = $customerCandidateDataObject->getDefaultBilling();
                    $address = $this->addressModel->load($billingAddressId);
                    $address->setCustomerId($currentCustomerDataObject->getId())
                        ->setFirstname($currentCustomerDataObject->getFirstname())
                        ->setLastname('-')
                        ->setCountryId($countryId)
                        ->setPostcode($postcode)
                        ->setRegion($region)
                        ->setRegionId($regionId)
                        ->setCity($city)
                        ->setCityId($cityId)
                        ->setStreet($street)                                
                        ->setTownshipId($townshipId)
                        ->setTownship($township)
                        ->setIsDefaultBilling('1')
                        ->setIsDefaultShipping(false)
                        ->setSaveInAddressBook('1')
                        ->save();

                    $this->dispatchSuccessEvent($customerCandidateDataObject);
                    $response = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => "ok",
                        'message' => __("Update affiliate account successfull!"),
                        'redirect_url' => $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB).'customer/account'
                    ]);
                }else{
                    $response = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => "error",
                        'message' => __('Invalid password'),
                    ]);
                }
            }catch (\Exception $e) {
                $this->writeLog($e->getMessage());
                $response = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData([
                    'status'  => "error",
                    'message' => $e->getMessage() // __("This is the required case")
                ]);
            }
        }
        return $response;
    }
    private function getCurrentPasswordHash($customerId){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $select = $connection->select()
        ->from(['o' => $resource->getTableName('customer_entity')], 'o.password_hash')
        ->where('o.entity_id = ?', $customerId);
        $row =  $connection->fetchRow($select);
        return $row;
    }
    /**
     * Change customer password
     *
     * @param string $email
     * @return boolean
     * @throws InvalidEmailOrPasswordException|InputException
     */
    protected function changeCustomerPassword($email, $phoneNumber = null)
    {
        $isPasswordChanged = false;
        if ($this->getRequest()->getParam('change_password')) {
            $currPass = $this->getRequest()->getPost('current_password');
            $newPass = $this->getRequest()->getPost('password');
            $confPass = $this->getRequest()->getPost('password_confirmation');
            if ($newPass != $confPass) {
                throw new InputException(__('Password confirmation doesn\'t match entered password.'));
            }

            $isPasswordChanged = $this->customerAccountManagement->changePassword($email, $currPass, $newPass, $phoneNumber);
        }

        return $isPasswordChanged;
    }

    /**
     * Process change email request
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $currentCustomerDataObject
     * @return void
     * @throws InvalidEmailOrPasswordException
     * @throws UserLockedException
     */
    private function processChangeEmailRequest(\Magento\Customer\Api\Data\CustomerInterface $currentCustomerDataObject)
    {
        if ($this->getRequest()->getParam('change_email')) {
            // authenticate user for changing email
            try {
                $this->getAuthentication()->authenticate(
                    $currentCustomerDataObject->getId(),
                    $this->getRequest()->getPost('current_password')
                );
            } catch (InvalidEmailOrPasswordException $e) {
                throw new InvalidEmailOrPasswordException(
                    __("The password doesn't match this account. Verify the password and try again.")
                );
            }
        }
    }

    /**
     * Get Customer Mapper instance
     *
     * @return Mapper
     *
     * @deprecated 100.1.3
     */
    private function getCustomerMapper()
    {
        if ($this->customerMapper === null) {
            $this->customerMapper = ObjectManager::getInstance()->get(\Magento\Customer\Model\Customer\Mapper::class);
        }
        return $this->customerMapper;
    }

    /**
     * Disable Customer Address Validation
     *
     * @param CustomerInterface $customer
     * @throws NoSuchEntityException
     */
    private function disableAddressValidation($customer)
    {
        foreach ($customer->getAddresses() as $address) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
            $addressModel->setShouldIgnoreValidation(true);
        }
    }
     /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/create_edit.log');
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


