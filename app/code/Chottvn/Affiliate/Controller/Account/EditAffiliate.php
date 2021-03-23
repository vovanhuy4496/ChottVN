<?php
namespace Chottvn\Affiliate\Controller\Account;

use Magento\Framework\Controller\ResultFactory;
use Chottvn\Affiliate\Helper\Log as HelperAffiliateLog;
use Chottvn\Affiliate\Model\Log as AffiliateLog;

class EditAffiliate extends \Magento\Framework\App\Action\Action
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
        HelperAffiliateLog $helperAffiliateLog
	){
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

		return parent::__construct($context);
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

	public function execute()
	{
        try {
            $post = $this->getRequest()->getPostValue();
            $change_password =  $this->getRequest()->getParam('change_password')? $this->getRequest()->getParam('change_password'):'';
            $bank_number = $this->getRequest()->getParam('bank_number') ? $this->getRequest()->getParam('bank_number'):'';
            $password_input = $this->getRequest()->getParam('password')? $this->getRequest()->getParam('password'):'';
            $password_confirmation = $this->getRequest()->getParam('password_confirmation')? $this->getRequest()->getParam('password_confirmation'):'';
            $bank_id = $this->getRequest()->getParam('bank_id') ? $this->getRequest()->getParam('bank_id'):'';
            $email = $this->getRequest()->getParam('email') ? $this->getRequest()->getParam('email'):'';
            $dob = $this->getRequest()->getParam('dob')? $this->getRequest()->getParam('dob'):'';
			$customerId = $this->session->getCustomer()->getId();
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $encryptor = $objectManager->get('Magento\Framework\Encryption\EncryptorInterface');
            $current_password = $this->getRequest()->getParam('current_password');
            $password = $this->getCurrentPasswordHash($customerId);
            $password = $password['password_hash'];
            $bool = $encryptor->validateHash($current_password, $password);
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
			if($customerId){
                if($bool === true){
                    // if not exist account
                    $customer_current = $this->customerRepository->getById($customerId);
                    // update email
                    $customer_email = $customer_current->setCustomAttribute('customer_email', $email);
                    // update dob
                    if($dob){
                        $dob = explode("/", $dob);
                        $dob = intval($dob[2])."-".intval($dob[1])."-".intval($dob[0]);
                        $customer_current->setDob(date('Y-m-d',strtotime($dob)));
                    }else{
                        $customer_current->setDob('');
                    }
                    // update sex
                    $customer_current->setGender($gender);
                    // save model 
                    $this->customerRepository->save($customer_current);
                    // $this->writeLog($customer_current->getCustomAttribute('customer_email')->getValue());
                    // update bank
                    $this->updateBankCustomer($customerId, $bank_id,$bank_number);
                    //update password
                    if($change_password == 1){
                        if($password_input ==  $password_confirmation){
                            $this->customerRepository->save($customer_current, $encryptor->getHash($password_input, true));
                        }else{
                            $response = $this->resultFactory
                            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                            ->setData([
                                'status'  => "error",
                                'message' => __('Password and confirmation password do not match')
                            ]);
                        }
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
                    $billingAddressId = $customer_current->getDefaultBilling();
                    $address = $this->addressModel->load($billingAddressId);
                    $address->setCustomerId($customer_current->getId())
                        ->setFirstname($customer_current->getFirstname())
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
                    $this->coreSession->start();
                    $this->coreSession->setMessage('successfully');
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
                    return $response;
                }
			}else{
				$response = $this->resultFactory
				->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
				->setData([
					'status'  => "error",
					'message' => __('This is the required case'),
				]);
				return $response;
			}
        } catch (\Exception $e) {
			$this->writeLog($e->getMessage());
            $response = $this->resultFactory
		    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
		    ->setData([
		        'status'  => "error",
		        'message' => $e->getMessage()
		    ]);
        }

		return $response;
    }
    private function saveLog($model)
    {
        // Save log bank
        $this->_helperAffiliateLog->saveLogWithResource([
            "account_id" => $model['customer_id'],
            "resource_type" => 'chottvn_paymentaccount_customerba',
            "resource_id" => $model['customerba_id'],
            "event" => AffiliateLog::EVENT_BANK_ACCOUNT_CHANGED,
            "value" => [
                "paymentaccount_bank_id" => (int)$model['paymentaccount_bank_id'],
                "account_owner" => $model['account_owner'],
                "account_number" => $model['account_number'],
                "bank_branch" => $model['bank_branch'],
                "note" => $model['note'],
                "status" => $model['status']
            ]
        ]);
    }
    /**
     * @param $info
     * @param $type  
     * @return 
     */
    protected function updateBankCustomer($customerId,$paymentaccountBankId,$accountNumber) {
        try {       
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $connection->beginTransaction();
            $tableName = $resource->getTableName('chottvn_paymentaccount_customerba');
            $data = [
                "paymentaccount_bank_id" => $paymentaccountBankId,
                "account_number" => $accountNumber

            ];
            $where = ['customer_id = ?' => $customerId];
            $updatedRows= $connection->update($tableName, $data, $where);
            $connection->commit();
            // get customerba_id 
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $select = $connection->select()
            ->from(['o' => $resource->getTableName('chottvn_paymentaccount_customerba')])
            ->where('o.customer_id = ?', $customerId)
            ->where('o.account_number = ?', $accountNumber);
            $row =  $connection->fetchRow($select);
            $this->saveLog($row);
            return true;
        }
        catch(\Exception $e){
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }     
        return false;         
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