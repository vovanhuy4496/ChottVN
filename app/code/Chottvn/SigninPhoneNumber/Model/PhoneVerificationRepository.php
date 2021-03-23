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

namespace Chottvn\SigninPhoneNumber\Model;

use Chottvn\SigninPhoneNumber\Api\PhoneVerificationRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Exception\ValidatorException;
use Chottvn\Sms\Api\SendOtpRepositoryInterface as SendOtpRepository;

class PhoneVerificationRepository implements PhoneVerificationRepositoryInterface
{
    /**
     * @var PhoneVerificationRepository
     */
	protected $_sendOtpRepository;
	
	protected $_phoneVerificationFactory;

	protected $_resource;

	protected $_customerRepository;

	protected $_scopeConfig;

	protected $_encryptor;

	protected $_smsConfigs;

	protected $_resourceConnection;

	protected $_connection;
	public function __construct(
		\Magento\Framework\App\ResourceConnection $resourceConnection,
		\Chottvn\SigninPhoneNumber\Model\PhoneVerificationFactory $phoneVerificationFactory,
		\Chottvn\SigninPhoneNumber\Model\ResourceModel\PhoneVerification $resource,
		ScopeConfigInterface $scopeConfig,
		CustomerRepositoryInterface $customerRepository,
        SendOtpRepository $sendOtpRepository,
		\Magento\Framework\Encryption\EncryptorInterface $encryptor
	) {
		$this->_resourceConnection = $resourceConnection;
		$this->_encryptor = $encryptor;
		$this->_scopeConfig = $scopeConfig;
		$this->_phoneVerificationFactory = $phoneVerificationFactory;
		$this->_resource = $resource;
		$this->_customerRepository = $customerRepository;
		$this->_sendOtpRepository = $sendOtpRepository;
		$this->_smsConfigs = $this->loadConfigs();
	}

	protected function loadConfigs()
	{
		return [
			"provider" => $this->_scopeConfig->getValue('chottvn_sms/sms_brandname/provider'),
			"secret_key" => $this->_encryptor->decrypt($this->_scopeConfig->getValue('chottvn_sms/sms_brandname/secret_key')),
			"api_key" => $this->_encryptor->decrypt($this->_scopeConfig->getValue('chottvn_sms/sms_brandname/api_key')),
			"token" => $this->_encryptor->decrypt($this->_scopeConfig->getValue('chottvn_sms/sms_brandname/token')),
			"brandname" => $this->_scopeConfig->getValue('chottvn_sms/sms_brandname/brandname'),
			"message_template" => $this->_scopeConfig->getValue('chottvn_sms/sms_brandname/message_template'),
			"effective_time" => $this->_scopeConfig->getValue('chottvn_sms/sms_brandname/effective_time'),
			"max_request_limit" => $this->_scopeConfig->getValue('chottvn_sms/sms_brandname/max_request_limit')
		];
	}

	protected function getCustomerPhone($customerId)
	{
		$currentCustomerDataObject = $this->_customerRepository->getById($customerId);
		return $currentCustomerDataObject->getCustomAttribute('phone_number');
	}

	/**
	 * {@inheritdoc}
	 */
	public function sendOTP($customerId)
	{
		$return = [
			"status" => false,
			"message" => ""
		];

		$phoneNumberAttribute = $this->getCustomerPhone($customerId);

		if (!$phoneNumberAttribute) {
			$return["message"] = __('User with id: %1 do not have phone number.', $customerId);
			return $return;
		}

		// Find record customer_id, phone_number from table
		$phoneVerification = $this->_phoneVerificationFactory->create();
		$collection = $phoneVerification->getCollection()
			->addFieldToFilter('customer_id', $customerId)
			->addFieldToFilter('phone_number', $phoneNumberAttribute->getValue())
			->setOrder('customer_id', 'ASC');
		$itemPhoneVerification = $collection->getLastItem();

		if (!$itemPhoneVerification->getId()) {
			// The item not exists, create new item
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$itemPhoneVerification = $objectManager->create('Chottvn\SigninPhoneNumber\Model\PhoneVerification');
			$itemPhoneVerification->setData([
				"requested_times" => 0,
				"customer_id" => $customerId,
				"phone_number" => $phoneNumberAttribute->getValue(),
				"auth_code" => 999999,
				"verify_status" => "0",
				"code_status" => "0"
			]);
		}

		// Check is phone number authenticate ? If true then return message phone authenticated
		if ($itemPhoneVerification->getData("verify_status") == 1) {
			$return["message"] = __("Phone authenticated.");
			return json_encode($return);
		}

		// Check last request send OTP is current date ? True then check requested_times > max_request_limit
		if (date("Y-m-d", strtotime($itemPhoneVerification->getData('created_at'))) >= date("Y-m-d")) {
			if ($itemPhoneVerification->getData('requested_times') >= $this->_smsConfigs['max_request_limit']) {
				$return["message"] = __('Request send OTP reach out of max request limit per day');
				return json_encode($return);
			}
		} else {
			// If last request sent OTP is days before then set requested_times to 0
			$itemPhoneVerification->setData('requested_times', 0);
		}

		// Call API send sms
		// Generate random OTP code and update requested_times
		$itemPhoneVerification->setData('auth_code', mt_rand(100000, 999999));
		$itemPhoneVerification->setData('code_status', "0");
		$itemPhoneVerification->setData('requested_times', $itemPhoneVerification->getData('requested_times') + 1);
		$itemPhoneVerification->setData('created_at', date("Y-m-d H:i:s"));
		$itemPhoneVerification->save();

		$resultSendSMS = $this->_sendOtpRepository->callAPISendOTP($itemPhoneVerification);
		$return["resultSendSMS"] = $resultSendSMS;

		// Prepare data to save log sms
		$smsLogData = [
			"phone_number" => $phoneNumberAttribute->getValue(),
			"type" => PhoneVerification::TYPE_SMS_VERIFY_PHONE,
			"provider" => $this->_smsConfigs['provider']
		];

		if ($resultSendSMS['status']) {
			$return["status"] = true;
			$return["message"] = __('Please enter the verification code we just sent to your phone number %1', $phoneNumberAttribute->getValue());
			$smsLogData['status'] = true;
		} else {
			$return["message"] = __('Have error when call API to send SMS');
			$smsLogData['status'] = false;
		}

		// Save log sms
		$this->_sendOtpRepository->saveSMSLog(json_encode($smsLogData));

		return json_encode($return);
	}

	/**
	 * {@inheritdoc}
	 */
	public function verifyPhone($data)
	{
		$data = json_decode($data);
		$return = [
			"status" => false,
			"message" => ""
		];

		$phoneNumberAttribute = $this->getCustomerPhone($data->customerId);

		$this->writeLog('func:verifyPhone - Prepare to verify phone number ' . $phoneNumberAttribute->getValue() . ' for customer id: ' . $data->customerId);

		// Find record customer_id, phone_number from table
		$phoneVerification = $this->_phoneVerificationFactory->create();
		$collection = $phoneVerification->getCollection()
			->addFieldToFilter('customer_id', $data->customerId)
			->addFieldToFilter('phone_number', $phoneNumberAttribute->getValue())
			->setOrder('customer_id', 'ASC');
		$itemPhoneVerification = $collection->getLastItem();

		if (!$itemPhoneVerification->getId()) {
			$return["message"] = __('Phone Verification tracking not found for customer with id: %1', $data->customerId);
			$this->writeLog('func:verifyPhone - Phone Verification tracking not found for customer with id: ' . $data->customerId);
			return json_encode($return);
		}

		// Validate expired time
		$expiredTime = date("Y-m-d H:i:s", strtotime($itemPhoneVerification->getData('created_at')) + $this->_smsConfigs['effective_time']);

		if ($expiredTime < date("Y-m-d H:i:s")) {
			$return["message"] = __('This OTP Code is expired');
			$this->writeLog('func:verifyPhone - This OTP Code is expired');
			return json_encode($return);
		}

		// Validate auth_code is invalid
		if ($itemPhoneVerification->getData('auth_code') != $data->authCode) {
			$return["message"] = __('This OTP Code is invalid');
			$this->writeLog('func:verifyPhone - This OTP Code is invalid');
			return json_encode($return);
		}

		$itemPhoneVerification->setData('verify_status', 1);
		$itemPhoneVerification->setData('code_status', 1);
		$itemPhoneVerification->save();

		$return['status'] = true;
		$return['message'] = __("Phone number verify successfull.");
		$this->writeLog('func:verifyPhone - Phone number verify successfull.');
		return json_encode($return);
	}

	/**
	 * {@inheritdoc}
	 */
	public function verifyPhoneByNumber($data)
	{
		$data = json_decode($data);
		$return = [
			"status" => false,
			"message" => ""
		];

		$this->writeLog('func:verifyPhoneByNumber - Prepare to verify phone number ' . $data->phoneNumber);

		// Find record customer_id, phone_number from table
		$phoneVerification = $this->_phoneVerificationFactory->create();
		$collection = $phoneVerification->getCollection()
			->addFieldToFilter('phone_number', $data->phoneNumber)
			->setOrder('customer_id', 'ASC');
		$itemPhoneVerification = $collection->getLastItem();

		if (!$itemPhoneVerification->getId()) {
			$return["message"] = __('Phone Verification tracking not found for customer with phone number: %1', $data->phoneNumber);
			$this->writeLog('func:verifyPhoneByNumber - Phone Verification tracking not found for customer with phone number: ' . $data->phoneNumber);
			return json_encode($return);
		}

		// Validate expired time
		$expiredTime = date("Y-m-d H:i:s", strtotime($itemPhoneVerification->getData('created_at')) + $this->_smsConfigs['effective_time']);

		if ($expiredTime < date("Y-m-d H:i:s")) {
			$return["message"] = __('This OTP Code is expired');
			$this->writeLog('func:verifyPhoneByNumber - This OTP Code is expired');
			return json_encode($return);
		}

		// Validate auth_code is invalid
		if ($itemPhoneVerification->getData('auth_code') != $data->authCode) {
			$return["message"] = __('This OTP Code is invalid');
			$this->writeLog('func:verifyPhoneByNumber - This OTP Code is invalid');
			return json_encode($return);
		}

		$itemPhoneVerification->setData('verify_status', 1);
		$itemPhoneVerification->setData('code_status', 1);
		$itemPhoneVerification->save();

		$return['status'] = true;
		$return['message'] = __("Phone number verify successfull.");
		$this->writeLog('func:verifyPhoneByNumber - Phone number verify successfull.');
		return json_encode($return);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isActivated($phoneNumber)
	{
		// Find record filter by verify_status, phone_number from table
		$phoneVerification = $this->_phoneVerificationFactory->create();
		$collection = $phoneVerification->getCollection()
			->addFieldToFilter('phone_number', $phoneNumber)
			->setOrder('customer_id', 'ASC');
		$itemPhoneVerification = $collection->getLastItem();

		if (!$itemPhoneVerification->getId()) {
			// No record found, must check have customer register with this phone number.
			$this->_connection = $this->_resourceConnection->getConnection();
			$query = "SELECT * FROM `customer_grid_flat` WHERE phone_number = '" . $phoneNumber . "'";

			$queryCollection = $this->_connection->fetchAll($query);

			if (count($queryCollection) < 1) { // No record in table customer_grid_flat -> not registered
				$this->writeLog('func:isActivated - No record in table customer_grid_flat -> not registered');
				throw new NoSuchEntityException();
			}

			// Have more than 1 record -> registered but not activated, then return false
			$this->writeLog('func:isActivated - registered but not activated');
			return false;
		}

		// Have record in table phone verification -> check verified true then return true (activated)
		if ($itemPhoneVerification->getData('verify_status') == PhoneVerification::VERIFY_STATUS_ACTIVE) {
			$this->writeLog('func:isActivated - activated');
			return true;
		}

		$this->writeLog('func:isActivated - Not activated');
		// Not activated -> return false
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function sendOTPPhone($phoneNumber)
	{
		$this->writeLog('func:sendOTPPhone - Start to send OTP for phone number: ' . $phoneNumber);
		$return = [
			"status" => false,
			"message" => ""
		];

		// Find record customer_id, phone_number from table
		$phoneVerification = $this->_phoneVerificationFactory->create();
		$collection = $phoneVerification->getCollection()
			->addFieldToFilter('phone_number', $phoneNumber)
			->setOrder('customer_id', 'ASC');
		$itemPhoneVerification = $collection->getLastItem();

		if (!$itemPhoneVerification->getId()) {
			// No record found, must check have customer register with this phone number.
			$this->_connection = $this->_resourceConnection->getConnection();
			$query = "SELECT * FROM `customer_grid_flat` WHERE phone_number = '" . $phoneNumber . "'";

			$queryCollection = $this->_connection->fetchAll($query);
			if (count($queryCollection) < 1) {
				$return["message"] = __("There are no customer account registered by phone number %1", $phoneNumber);
				return json_encode($return);
			}

			$customerId = $queryCollection[count($queryCollection) - 1]['entity_id'];

			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$itemPhoneVerification = $objectManager->create('Chottvn\SigninPhoneNumber\Model\PhoneVerification');
			$itemPhoneVerification->setData([
				"requested_times" => 0,
				"customer_id" => $customerId,
				"phone_number" => $phoneNumber,
				"auth_code" => 999999,
				"verify_status" => "0",
				"code_status" => "0"
			]);
		}

		// Check is phone number authenticate ? If true then return message phone authenticated
		if ($itemPhoneVerification->getData("verify_status") == 1) {
			$return["message"] = __("Phone authenticated.");
			return json_encode($return);
		}

		// Check last request send OTP is current date ? True then check requested_times > max_request_limit
		if (date("Y-m-d", strtotime($itemPhoneVerification->getData('created_at'))) >= date("Y-m-d")) {
			if ($itemPhoneVerification->getData('requested_times') >= $this->_smsConfigs['max_request_limit']) {
				$return["message"] = __('Request send OTP reach out of max request limit per day');
				return json_encode($return);
			}
		} else {
			// If last request sent OTP is days before then set requested_times to 0
			$itemPhoneVerification->setData('requested_times', 0);
		}

		// Call API send sms
		// Generate random OTP code and update requested_times
		$itemPhoneVerification->setData('auth_code', mt_rand(100000, 999999));
		$itemPhoneVerification->setData('code_status', "0");
		$itemPhoneVerification->setData('requested_times', $itemPhoneVerification->getData('requested_times') + 1);
		$itemPhoneVerification->setData('created_at', date("Y-m-d H:i:s"));
		$itemPhoneVerification->save();

		$resultSendSMS = $this->_sendOtpRepository->callAPISendOTP($itemPhoneVerification);
		$return["resultSendSMS"] = $resultSendSMS;

		// Prepare data to save log sms
		$smsLogData = [
			"phone_number" => $phoneNumber,
			"type" => PhoneVerification::TYPE_SMS_VERIFY_PHONE,
			"provider" => $this->_smsConfigs['provider']
		];

		if ($resultSendSMS['status']) {
			$return["status"] = true;
			$return["message"] = __('Please enter the verification code we just sent to your phone number %1', $phoneNumber);
			$smsLogData['status'] = true;
		} else {
			$return["message"] = __('Have error when call API to send SMS');
			$smsLogData['status'] = false;
		}

		// Save log sms
		$this->_sendOtpRepository->saveSMSLog(json_encode($smsLogData));

		return json_encode($return);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTimeToResendOTP($phoneNumber)
	{
		// Find record customer_id, phone_number from table
		$phoneVerification = $this->_phoneVerificationFactory->create();
		$collection = $phoneVerification->getCollection()
			->addFieldToFilter('code_status', 0)
			->addFieldToFilter('phone_number', $phoneNumber)
			->setOrder('customer_id', 'ASC');
		$itemPhoneVerification = $collection->getLastItem();

		if (!$itemPhoneVerification->getId()) {
			return 0;
		}

		$x = strtotime($itemPhoneVerification->getData('created_at')) + $this->_smsConfigs['effective_time'];
		$y = strtotime(date("Y-m-d H:i:s"));

		$time = $x - $y;

		return ($time < 0) ? 0 : $time;
	}

	/**
	 * {@inheritdoc}
	 */
	public function sendForgotPWOtp($phoneNumber)
	{
		$this->writeLog('func:sendForgotPWOtp - Start to send OTP for forgot pw by phone number: ' . $phoneNumber);
		$return = [
			"status" => false,
			"message" => "",
			"error_code" => "ERROR"
		];

		// Find record customer_id, phone_number from table
		$phoneVerification = $this->_phoneVerificationFactory->create();
		$collection = $phoneVerification->getCollection()
			->addFieldToFilter('phone_number', $phoneNumber)
			->setOrder('customer_id', 'ASC');
		$itemPhoneVerification = $collection->getLastItem();

		if (!$itemPhoneVerification->getId()) {
			// No record found, must check have customer register with this phone number.
			$this->_connection = $this->_resourceConnection->getConnection();
			$query = "SELECT * FROM `customer_grid_flat` WHERE phone_number = '" . $phoneNumber . "'";

			$queryCollection = $this->_connection->fetchAll($query);

			if (count($queryCollection) < 1) { // No record in table customer_grid_flat -> not registered
				$return["message"] = __("This phone number %1 was not registered in our system. Please check your phone number", $phoneNumber);
				$return['error_code'] = "NOT_REGISTERED";
				$this->writeLog('func:sendForgotPWOtp - not register');
				return json_encode($return);
			}

			// Have more than 1 record -> registered but not activated, then return false
			$this->writeLog('func:sendForgotPWOtp - registered but not activated');
			$return["message"] = __("This phone number %1 was not verified. Please verify your phone number", $phoneNumber);
			$return['error_code'] = "NOT_VERIFIED";
			return json_encode($return);
		}

		// Have record in table phone verification -> check verified false then return false (inactivated)
		if ($itemPhoneVerification->getData('verify_status') != PhoneVerification::VERIFY_STATUS_ACTIVE) {
			$return["message"] = __("This phone number %1 was not verified. Please verify your phone number", $phoneNumber);
			$return['error_code'] = "NOT_VERIFIED";
			$this->writeLog('func:sendForgotPWOtp - registered but not activated');
			return json_encode($return);
		}

		// Check last request send OTP is current date ? True then check requested_times > max_request_limit
		if (date("Y-m-d", strtotime($itemPhoneVerification->getData('created_at'))) >= date("Y-m-d")) {
			if ($itemPhoneVerification->getData('requested_times') >= $this->_smsConfigs['max_request_limit']) {
				$return["message"] = __('Request send OTP reach out of max request limit per day');
				return json_encode($return);
			}
		} else {
			// If last request sent OTP is days before then set requested_times to 0
			$itemPhoneVerification->setData('requested_times', 0);
		}

		// Call API send sms
		// Generate random OTP code and update requested_times
		$itemPhoneVerification->setData('auth_code', mt_rand(100000, 999999));
		$itemPhoneVerification->setData('code_status', "0");
		$itemPhoneVerification->setData('requested_times', $itemPhoneVerification->getData('requested_times') + 1);
		$itemPhoneVerification->setData('created_at', date("Y-m-d H:i:s"));
		$itemPhoneVerification->save();

		$resultSendSMS = $this->_sendOtpRepository->callAPISendOTP($itemPhoneVerification);
		$return["resultSendSMS"] = $resultSendSMS;

		$this->writeLog('func:sendForgotPWOtp - Result send OTP SMS: ' . json_encode($resultSendSMS));

		// Prepare data to save log sms
		$smsLogData = [
			"phone_number" => $phoneNumber,
			"type" => PhoneVerification::TYPE_SMS_FORGOT_PASSWORD,
			"provider" => $this->_smsConfigs['provider']
		];

		if ($resultSendSMS['status']) {
			$return["status"] = true;
			$return["message"] = __('Please enter the verification code we just sent to your phone number %1', $phoneNumber);
			$smsLogData['status'] = true;
		} else {
			$return["message"] = __('Have error when call API to send SMS');
			$smsLogData['status'] = false;
		}

		// Save log sms
		$this->_sendOtpRepository->saveSMSLog(json_encode($smsLogData));

		return json_encode($return);
	}

	public function validateOtp($customerId, $phoneNumber, $otpCode)
	{
		$this->writeLog("func:validateOtp - validate OTP");
		$phoneVerification = $this->_phoneVerificationFactory->create();
		$collection = $phoneVerification->getCollection()
			->addFieldToFilter('customer_id', $customerId)
			->addFieldToFilter('phone_number', $phoneNumber)
			->addFieldToFilter('verify_status', PhoneVerification::VERIFY_STATUS_ACTIVE)
			->setOrder('customer_id', 'ASC');
		$itemPhoneVerification = $collection->getLastItem();

		if (!$itemPhoneVerification->getId()) {
			throw new NoSuchEntityException();
		}

		$expiredTime = date("Y-m-d H:i:s", strtotime($itemPhoneVerification->getData('created_at')) + $this->_smsConfigs['effective_time']);

		if ($expiredTime < date("Y-m-d H:i:s")) {
			throw new SessionException(__("This OTP Code is expired"));
		}

		if ($itemPhoneVerification->getData('auth_code') != $otpCode) {
			throw new ValidatorException(__("This OTP Code is not valid"));
		}

		$itemPhoneVerification->setData('code_status', "1");
		$itemPhoneVerification->save();

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValidatedCustomerId($phoneNumber)
	{
		// Find record filter by verify_status, phone_number from table
		$phoneVerification = $this->_phoneVerificationFactory->create();
		$collection = $phoneVerification->getCollection()
			->addFieldToFilter('phone_number', $phoneNumber)
			->addFieldToFilter('verify_status', PhoneVerification::VERIFY_STATUS_ACTIVE)
			->setOrder('customer_id', 'ASC');
		$itemPhoneVerification = $collection->getLastItem();

		if (!$itemPhoneVerification->getId()) {
			return false;
		}

		return $itemPhoneVerification->getData("customer_id");
	}

	/**
	 * @param $info
	 * @param $type  [error, warning, info]
	 * @return 
	 */
	private function writeLog($info, $type = "info")
	{
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/phone_verification_repo.log');
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
