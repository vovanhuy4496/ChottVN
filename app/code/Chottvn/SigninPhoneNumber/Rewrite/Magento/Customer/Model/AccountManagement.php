<?php

namespace Chottvn\SigninPhoneNumber\Rewrite\Magento\Customer\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\ValidationResultsInterfaceFactory;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Customer\CredentialsValidator;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Customer\Model\Metadata\Validator;
use Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory;
use Magento\Directory\Model\AllowedCountries;
use Magento\Eav\Model\Validator\Attribute\Backend;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\AddressRegistry;

use Chottvn\SigninPhoneNumber\Api\SigninInterface as HandlerSignin;
use Chottvn\SigninPhoneNumber\Model\Config\Source\SigninMode;
use Chottvn\SigninPhoneNumber\Api\PhoneVerificationRepositoryInterface as PhoneVerificationRepository;
use Chottvn\SigninPhoneNumber\Exception\PhoneNotVerifiedException;
use Exception;
use Magento\Framework\UrlFactory;

use function GuzzleHttp\json_decode;

/**
 * Override Magento's default AccountManagement class.
 * @see \Magento\Customer\Model\AccountManagement
 */
class AccountManagement extends \Magento\Customer\Model\AccountManagement
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Customer\Api\Data\ValidationResultsInterfaceFactory
     */
    private $validationResultsDataFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadataService;

    /**
     * @var PsrLogger
     */
    protected $logger;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var ConfigShare
     */
    private $configShare;

    /**
     * @var StringHelper
     */
    protected $stringHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var SaveHandlerInterface
     */
    private $saveHandler;

    /**
     * @var CollectionFactory
     */
    private $visitorCollectionFactory;

    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var CustomerViewHelper
     */
    protected $customerViewHelper;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var CustomerModel
     */
    protected $customerModel;

    /**
     * @var AuthenticationInterface
     */
    protected $authentication;

    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var \Magento\Eav\Model\Validator\Attribute\Backend
     */
    private $eavValidator;

    /**
     * @var CredentialsValidator
     */
    private $credentialsValidator;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * @var GetCustomerByToken
     */
    private $getByToken;

    /**
     * @var SigninInterface
     */
    private $handlerSignin;

    /**
     * @var PhoneVerificationRepository
     */
    protected $phoneVerificationRepository;

    /**
     * @var Session
     */
    protected $coreSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;

    /**
     * @var \Magento\Customer\Model\Group
     */
    protected $groupCustomer;

    /**
     * @param CustomerFactory $customerFactory
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Random $mathRandom
     * @param Validator $validator
     * @param ValidationResultsInterfaceFactory $validationResultsDataFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerMetadataInterface $customerMetadataService
     * @param CustomerRegistry $customerRegistry
     * @param PsrLogger $logger
     * @param Encryptor $encryptor
     * @param ConfigShare $configShare
     * @param StringHelper $stringHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param DataObjectProcessor $dataProcessor
     * @param Registry $registry
     * @param CustomerViewHelper $customerViewHelper
     * @param DateTime $dateTime
     * @param CustomerModel $customerModel
     * @param ObjectFactory $objectFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param HandlerSignin $handlerSignin
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CustomerFactory $customerFactory,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Random $mathRandom,
        Validator $validator,
        ValidationResultsInterfaceFactory $validationResultsDataFactory,
        AddressRepositoryInterface $addressRepository,
        CustomerMetadataInterface $customerMetadataService,
        CustomerRegistry $customerRegistry,
        PsrLogger $logger,
        Encryptor $encryptor,
        ConfigShare $configShare,
        StringHelper $stringHelper,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        DataObjectProcessor $dataProcessor,
        Registry $registry,
        CustomerViewHelper $customerViewHelper,
        DateTime $dateTime,
        CustomerModel $customerModel,
        ObjectFactory $objectFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        HandlerSignin $handlerSignin,
        PhoneVerificationRepository $phoneVerificationRepository,
        UrlFactory $urlFactory,
        \Magento\Customer\Model\Group $groupCustomer,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        parent::__construct(
            $customerFactory,
            $eventManager,
            $storeManager,
            $mathRandom,
            $validator,
            $validationResultsDataFactory,
            $addressRepository,
            $customerMetadataService,
            $customerRegistry,
            $logger,
            $encryptor,
            $configShare,
            $stringHelper,
            $customerRepository,
            $scopeConfig,
            $transportBuilder,
            $dataProcessor,
            $registry,
            $customerViewHelper,
            $dateTime,
            $customerModel,
            $objectFactory,
            $extensibleDataObjectConverter
        );
        /*$this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->eventManager = $eventManager;*/
        $this->handlerSignin = $handlerSignin;

        $this->customerFactory = $customerFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->mathRandom = $mathRandom;
        $this->validator = $validator;
        $this->validationResultsDataFactory = $validationResultsDataFactory;
        $this->addressRepository = $addressRepository;
        $this->customerMetadataService = $customerMetadataService;
        $this->customerRegistry = $customerRegistry;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->configShare = $configShare;
        $this->stringHelper = $stringHelper;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->dataProcessor = $dataProcessor;
        $this->registry = $registry;
        $this->customerViewHelper = $customerViewHelper;
        $this->dateTime = $dateTime;
        $this->customerModel = $customerModel;
        $this->objectFactory = $objectFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->credentialsValidator = $objectManager->get(CredentialsValidator::class);
        $this->dateTimeFactory = $objectManager->get(DateTimeFactory::class);
        //$this->accountConfirmation = $objectManager->get(AccountConfirmation::class);
        $this->sessionManager = $objectManager->get(SessionManagerInterface::class);
        $this->saveHandler = $objectManager->get(SaveHandlerInterface::class);
        $this->visitorCollectionFactory = $objectManager->get(CollectionFactory::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->addressRegistry = $objectManager->get(AddressRegistry::class);
        $this->getByToken = $objectManager->get(GetCustomerByToken::class);
        $this->allowedCountriesReader = $objectManager->get(AllowedCountries::class);
        $this->phoneVerificationRepository = $phoneVerificationRepository;
        $this->coreSession = $coreSession;
        $this->groupCustomer = $groupCustomer;
        $this->urlModel = $urlFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function authenticate($username, $password)
    {
        $urlCreateAccount = $this->urlModel->getUrl('customer/account/create', ['_secure' => true]);
        try {
            $customer = $this->handleSignin($username);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__("Account not found. Please recheck your phone number, your password or <a href='%1'>Create new account</a>.", $urlCreateAccount));
        }

        $customerId = $customer->getId();
        if ($this->getAuthentication()->isLocked($customerId)) {
            throw new UserLockedException(__('The account is locked.'));
        }
        try {
            $this->getAuthentication()->authenticate($customerId, $password);
            // phpcs:disable Magento2.Exceptions.ThrowCatch
        } catch (InvalidEmailOrPasswordException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        if ($customer->getConfirmation() && $this->isConfirmationRequired($customer)) {
            throw new EmailNotConfirmedException(__("This account isn't confirmed. Verify and try again."));
        }

        $this->dispatchEvents($customer, $password);

        return $customer;
    }

    /**
     * Handle login mode.
     *
     * @param string $username Customer email or phone number
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function handleSignin(string $username)
    {
        if (!$this->handlerSignin->isEnabled()) {
            return $this->customerRepository->get($username);
        }

        $this->writeLog('func: handleSignin - verify phone status: ' .$this->isVerifyPhone($username));
        if ($this->isVerifyPhone($username)) {
            $this->writeLog('func: handleSignin - verified');
            switch ($this->handlerSignin->getSigninMode()) {
                case SigninMode::TYPE_PHONE:
                    $this->writeLog('func: handleSignin - type phone');
                    return $this->withPhoneNumber($username);
                case SigninMode::TYPE_BOTH_OR:
                    return $this->withPhoneNumberOrEmail($username);
                default:
                    return $this->customerRepository->get($username);
            }
        }
        $this->writeLog('func: handleSignin - not verified');
    }

    // Huy: call public function handleSignin de get customerRepository
    public function getCustomerRepository(string $username) {
        return $this->handleSignin($username);
    }

    // Huy: create func de check xem Invalid login or password.
    public function getAuthenticationCTT($customerId, $password) {
        $flag = true;
        try {
            $this->getAuthentication()->authenticate($customerId, $password);
        } catch (InvalidEmailOrPasswordException $e) {
            $flag = false;
        }
        return $flag;
    }

    /**
     * Action to login with Phone Number only.
     *
     * @param string $username
     * @return bool
     * @throws PhoneNotVerifiedException
     */
    private function isVerifyPhone(string $username)
    {
        $this->writeLog('func: isVerifyPhone - check verify phone');
        // Not verify then throw exception Phone Not Verified
        if (!$this->phoneVerificationRepository->isActivated($username)) {
            $this->coreSession->setSignInPhone($username);
            $this->writeLog('func: isVerifyPhone - throw exception phone not verified');
            $url = $this->urlModel->getUrl('signinphonenumber/phoneverification/display', ['_secure' => true]);
            $message = __(
                "Phone of this account is not verified. <a href='%1'>Click here</a> to verify your phone number",
                $url
            );
            throw new PhoneNotVerifiedException($message);
        }
        return true;
    }

    /**
     * Action to login with Phone Number only.
     *
     * @param string $username
     * @return CustomerInterface
     * @throws NoSuchEntityException
     */
    private function withPhoneNumber(string $username)
    {
        $customer = $this->handlerSignin->getByPhoneNumber($username);
        if (false == $customer) {
            throw new NoSuchEntityException();
        }
        return $customer;
    }

    /**
     * Action to login with Phone Number or Email.
     *
     * @param string $username
     * @return CustomerInterface
     */
    private function withPhoneNumberOrEmail(string $username)
    {
        $customer = $this->handlerSignin->getByPhoneNumber($username);
        if (false == $customer) {
            return $this->customerRepository->get($username);
        }
        return $customer;
    }

    /**
     * {@inheritdoc}
     */
    private function getAuthentication()
    {
        if (!($this->authentication instanceof AuthenticationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Customer\Model\AuthenticationInterface::class
            );
        }
        return $this->authentication;
    }

    /**
     * @param CustomerInterface $customer
     * @param string $password Customer password.
     * @return AccountManagement
     */
    private function dispatchEvents($customer, $password)
    {
        $customerModel = $this->customerFactory->create()->updateData($customer);
        $this->eventManager->dispatch(
            'customer_customer_authenticated',
            ['model' => $customerModel, 'password' => $password]
        );

        $this->eventManager->dispatch(
            'customer_data_object_login',
            ['customer' => $customer]
        );
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function createAccount(CustomerInterface $customer, $password = null, $redirectUrl = '')
    {
        if ($password !== null) {
            $this->checkPasswordStrength($password);
            $customerEmail = $customer->getEmail();
            if (!empty($customerEmail)) {
                try {
                    $this->credentialsValidator->checkPasswordDifferentFromEmail($customerEmail, $password);
                } catch (InputException $e) {
                    throw new LocalizedException(
                        __("The password can't be the same as the email address. Create a new password and try again.")
                    );
                }
            }
            $hash = $this->createPasswordHash($password);
        } else {
            $hash = null;
        }
        return $this->createAccountWithPasswordHash($customer, $hash, $redirectUrl);
    }


    /**
     * @inheritdoc
     *
     * @throws InputMismatchException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function createAccountWithPasswordHash(CustomerInterface $customer, $hash, $redirectUrl = '')
    {
        // This logic allows an existing customer to be added to a different store.  No new account is created.
        // The plan is to move this logic into a new method called something like 'registerAccountWithStore'
        if ($customer->getId()) {
            if (!empty($customer->getEmail())) {
                $customer = $this->customerRepository->get($customer->getEmail());
            } else {
                $customer = $this->customerRepository->getByPhoneNumber($customer->getPhoneNumber());
            }

            $websiteId = $customer->getWebsiteId();

            if ($this->isCustomerInStore($websiteId, $customer->getStoreId())) {
                throw new InputException(__('This customer already exists in this store.'));
            }
            // Existing password hash will be used from secured customer data registry when saving customer
        }

        // Make sure we have a storeId to associate this customer with.
        if (!$customer->getStoreId()) {
            if ($customer->getWebsiteId()) {
                $storeId = $this->storeManager->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
            } else {
                $this->storeManager->setCurrentStore(null);
                $storeId = $this->storeManager->getStore()->getId();
            }
            $customer->setStoreId($storeId);
        }

        // Associate website_id with customer
        if (!$customer->getWebsiteId()) {
            $websiteId = $this->storeManager->getStore($customer->getStoreId())->getWebsiteId();
            $customer->setWebsiteId($websiteId);
        }

        // Update 'created_in' value with actual store name
        if ($customer->getId() === null) {
            $websiteId = $customer->getWebsiteId();
            if ($websiteId && !$this->isCustomerInStore($websiteId, $customer->getStoreId())) {
                throw new LocalizedException(__('The store view is not in the associated website.'));
            }

            $storeName = $this->storeManager->getStore($customer->getStoreId())->getName();
            $customer->setCreatedIn($storeName);
        }

        $customerAddresses = $customer->getAddresses() ?: [];
        $customer->setAddresses(null);
        try {
            // If customer exists existing hash will be used by Repository
            $customer = $this->customerRepository->save($customer, $hash);
        } catch (AlreadyExistsException $e) {
            throw new InputMismatchException(
                __('A customer with the same email address already exists in an associated website.')
            );
            // phpcs:ignore Magento2.Exceptions.ThrowCatch
        } catch (LocalizedException $e) {
            throw $e;
        }
        try {
            foreach ($customerAddresses as $address) {
                if (!$this->isAddressAllowedForWebsite($address, $customer->getStoreId())) {
                    continue;
                }
                if ($address->getId()) {
                    $newAddress = clone $address;
                    $newAddress->setId(null);
                    $newAddress->setCustomerId($customer->getId());
                    $this->addressRepository->save($newAddress);
                } else {
                    $address->setCustomerId($customer->getId());
                    $this->addressRepository->save($address);
                }
            }
            $this->customerRegistry->remove($customer->getId());
            // phpcs:ignore Magento2.Exceptions.ThrowCatch
        } catch (InputException $e) {
            $this->customerRepository->delete($customer);
            throw $e;
        }
        $customer = $this->customerRepository->getById($customer->getId());
        $newLinkToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($customer, $newLinkToken);
        $this->updateCustomerLevel($customer);

        // get group_id customer
        $cus_grp_code = 'Affiliate';
        $group_obj = $this->groupCustomer;
        $existing_group = $group_obj->load($cus_grp_code, 'customer_group_code');
        $group_affiliate_id = $existing_group->getId();

        if ($customer->getGroupId() != $group_affiliate_id){
            $this->sendEmailConfirmation($customer, $redirectUrl);
        }
        return $customer;
    }

    /**
     * Set Customer Level by 
     *
     * @param CustomerInterface $customer
     * @return void
     */
    protected function updateCustomerLevel(CustomerInterface $customer){   
        try{                       
            if($phoneNumberAttr = $customer->getCustomAttribute('phone_number')){
                $chottCustomerPhoneNumber = $phoneNumberAttr->getValue();
                    //$chottCustomerPhoneNumber = $customer->getPhoneNumber();        

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

                $cttSaleOrderPlugin = $objectManager->create('Chottvn\CustomerMembership\Plugin\Magento\Sales\Model\ResourceModel\Order');
                    //$cttSaleOrderPlugin->updateCustomerMembership($customer, $chottCustomerPhoneNumber);      
                $levelCode = $cttSaleOrderPlugin->getCustomerLevelByPhoneNumber($chottCustomerPhoneNumber);                   
                if (empty($levelCode) ){
                    $levelCode = "member";                
                }
                $customer->setCustomAttribute('customer_level',$levelCode);
                $this->customerRepository->save($customer);    
            }
            
        }catch(\Exception $e){
            $this->writeLog($e);
            throw $e;
        }
            
    }

    /**
     * Send either confirmation or welcome email after an account creation
     *
     * @param CustomerInterface $customer
     * @param string $redirectUrl
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function sendEmailConfirmation(CustomerInterface $customer, $redirectUrl)
    {
        try {
            $hash = $this->customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash();
            $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED;
            if ($this->isConfirmationRequired($customer) && $hash != '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_CONFIRMATION;
            } elseif ($hash == '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD;
            }
            $this->getEmailNotification()->newAccount($customer, $templateType, $redirectUrl, $customer->getStoreId());
            $customer->setConfirmation(null);
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        } catch (\UnexpectedValueException $e) {
            $this->logger->error($e);
        }
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
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated 100.1.0
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Destroy all active customer sessions by customer id (current session will not be destroyed).
     *
     * Customer sessions which should be deleted are collecting from the "customer_visitor" table considering
     * configured session lifetime.
     *
     * @param string|int $customerId
     * @return void
     */
    private function destroyCustomerSessions($customerId)
    {
        $sessionLifetime = $this->scopeConfig->getValue(
            \Magento\Framework\Session\Config::XML_PATH_COOKIE_LIFETIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $dateTime = $this->dateTimeFactory->create();
        $activeSessionsTime = $dateTime->setTimestamp($dateTime->getTimestamp() - $sessionLifetime)
            ->format(DateTime::DATETIME_PHP_FORMAT);
        /** @var \Magento\Customer\Model\ResourceModel\Visitor\Collection $visitorCollection */
        $visitorCollection = $this->visitorCollectionFactory->create();
        $visitorCollection->addFieldToFilter('customer_id', $customerId);
        $visitorCollection->addFieldToFilter('last_visit_at', ['from' => $activeSessionsTime]);
        $visitorCollection->addFieldToFilter('session_id', ['neq' => $this->sessionManager->getSessionId()]);
        /** @var \Magento\Customer\Model\Visitor $visitor */
        foreach ($visitorCollection->getItems() as $visitor) {
            $sessionId = $visitor->getSessionId();
            $this->saveHandler->destroy($sessionId);
        }
    }

    /**
     * Set ignore_validation_flag for reset password flow to skip unnecessary address and customer validation
     *
     * @param Customer $customer
     * @return void
     */
    private function setIgnoreValidationFlag($customer)
    {
        $customer->setData('ignore_validation_flag', true);
    }

    /**
     * Check is address allowed for store
     *
     * @param AddressInterface $address
     * @param int|null $storeId
     * @return bool
     */
    private function isAddressAllowedForWebsite(AddressInterface $address, $storeId): bool
    {
        $allowedCountries = $this->allowedCountriesReader->getAllowedCountries(ScopeInterface::SCOPE_STORE, $storeId);

        return in_array($address->getCountryId(), $allowedCountries);
    }


    /**
     * @inheritdoc
     *
     * @throws InvalidEmailOrPasswordException
     */
    public function changePassword($email, $currentPassword, $newPassword, $phoneNumber = null)
    {
        try {
            //$customer = $this->customerRepository->get($email);
            $customer = $this->customerRepository->get($email);
            if (!$customer) {
                $customer = $this->customerRepository->getByPhoneNumber($phoneNumber);
            }
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        return $this->changePasswordForCustomer($customer, $currentPassword, $newPassword);
    }

    /**
     * Change customer password
     *
     * @param CustomerInterface $customer
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     * @throws InputException
     * @throws InputMismatchException
     * @throws InvalidEmailOrPasswordException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws UserLockedException
     */
    private function changePasswordForCustomer($customer, $currentPassword, $newPassword)
    {
        try {
            $this->getAuthentication()->authenticate($customer->getId(), $currentPassword);
            // phpcs:ignore Magento2.Exceptions.ThrowCatch
        } catch (InvalidEmailOrPasswordException $e) {
            throw new InvalidEmailOrPasswordException(
                __("The password doesn't match this account. Verify the password and try again.")
            );
        }
        $customerEmail = $customer->getEmail();
        $this->credentialsValidator->checkPasswordDifferentFromEmail($customerEmail, $newPassword);
        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerSecure->setRpToken(null);
        $customerSecure->setRpTokenCreatedAt(null);
        $this->checkPasswordStrength($newPassword);
        $customerSecure->setPasswordHash($this->createPasswordHash($newPassword));
        $this->destroyCustomerSessions($customer->getId());
        $this->disableAddressValidation($customer);
        $this->customerRepository->save($customer);

        return true;
    }


    /**
     * Reset Password By OTP
     */
    public function resetPasswordByOTP($phoneNumber, $otpCode, $newPassword)
    {
        $this->writeLog("func: resetPasswordByOTP - phone number: ".$phoneNumber);
        $customer = $this->customerRepository->getByPhoneNumber($phoneNumber);
        $this->writeLog("func: resetPasswordByOTP - customer id: ".$customer->getId());
        if(!$customer){
            throw new NoSuchEntityException();
        }

        // No need to validate customer and customer address while saving customer reset password token
        $this->disableAddressValidation($customer);
        $this->setIgnoreValidationFlag($customer);

        $this->writeLog("func: resetPasswordByOTP - Validate OTP");
        //Validate Token and new password strength
        $this->phoneVerificationRepository->validateOtp($customer->getId(), $phoneNumber, $otpCode);

        $this->writeLog("func: resetPasswordByOTP - New Password");
        $this->checkPasswordStrength($newPassword);
        $this->writeLog("func: resetPasswordByOTP - Checked Strength");
        //Update secure data
        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerSecure->setRpToken(null);
        $customerSecure->setRpTokenCreatedAt(null);
        $customerSecure->setPasswordHash($this->createPasswordHash($newPassword));
        $this->writeLog("func: resetPasswordByOTP - Update secure data");
        $this->destroyCustomerSessions($customer->getId());
        $this->writeLog("func: resetPasswordByOTP - destroy customer sessions");
        if ($this->sessionManager->isSessionExists()) {
            //delete old session and move data to the new session
            //use this instead of $this->sessionManager->regenerateId because last one doesn't delete old session
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            session_regenerate_id(true);
        }
        $this->writeLog("func: resetPasswordByOTP - prepare save customer");
        $this->customerRepository->save($customer);
        $this->writeLog("func: resetPasswordByOTP - saved customer");

        return true;
    }

    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/account_management.log');
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
