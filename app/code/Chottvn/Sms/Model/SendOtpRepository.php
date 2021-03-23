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

namespace Chottvn\Sms\Model;

use Chottvn\Sms\Api\SendOtpRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Chottvn\Sms\Model\Config\Source\SMSBrandname;
use Chottvn\Sms\Model\Config\Source\SMSType;

class SendOtpRepository implements SendOtpRepositoryInterface
{

	protected $_sendOtpFactory;

	protected $_resource;

	protected $_customerRepository;

	protected $_scopeConfig;

	protected $_encryptor;

	protected $_smsConfigs;

	public function __construct(
		\Chottvn\Sms\Model\SendOtpFactory $sendOtpFactory,
		\Chottvn\Sms\Model\ResourceModel\SendOtp $resource,
		ScopeConfigInterface $scopeConfig,
		CustomerRepositoryInterface $customerRepository,
		\Magento\Framework\Encryption\EncryptorInterface $encryptor
	) {
		$this->_encryptor = $encryptor;
		$this->_scopeConfig = $scopeConfig;
		$this->_sendOtpFactory = $sendOtpFactory;
		$this->_resource = $resource;
		$this->_customerRepository = $customerRepository;
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
			"max_request_limit" => $this->_scopeConfig->getValue('chottvn_sms/sms_brandname/max_request_limit'),
			"voucher_template" => $this->_scopeConfig->getValue('chottvn_promo_configuration/voucher/cttpromo_message_template')
		];
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function sendVoucher($data) {
		$data = json_decode($data);

		$return = [
			"status" => false,
			"message" => __('Send voucher code to phone number %1 failed', $data->phone),
			"voucher" => $data->voucher,
			"phone" => $data->phone,
			"rule_end_date" => $data->rule_end_date
		];

		$result = $this->callAPISendSms(SMSType::TYPE_SMS_VOUCHER, $data->phone, $data->voucher, $data->rule_end_date);

		if ($result['status']) {
			$return["status"] = true;
			$return["message"] = __('Sent voucher code to phone number %1', $data->phone);
		}

		$this->saveSMSLog(json_encode([
			"phone_number" => $data->phone,
			"type" => SMSType::TYPE_SMS_VOUCHER,
			"status" => $result["status"],
			"provider" => $this->_smsConfigs['provider']
		]));
		
		$return['result'] = json_encode($result);

		return json_encode($return);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function index() {
		
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function saveSMSLog($data) {
		$this->writeLog('func:saveSMSLog');
		$this->writeLog('func:saveSMSLog - params: '.$data);

		$data = json_decode($data);
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$itemSendOtp = $objectManager->create('Chottvn\Sms\Model\SendOtp');
		$itemSendOtp->setData([
			"phone_number" => $data->phone_number,
			"type" => $data->type,
			"status" => $data->status,
			"send_at" => date("Y-m-d H:i:s"),
			"provider" => $data->provider,
			"params" => ""
		]);

		$this->writeLog('func:saveSMSLog - entity: '.json_encode($itemSendOtp));
		$itemSendOtp->save();
	}

	/**
	 * {@inheritdoc}
	 */
	public function callAPISendSms($type = SMSType::TYPE_SMS_VOUCHER, $phone, $code, $rule_end_date)
	{
		if($type == SMSType::TYPE_SMS_VOUCHER) {
			$template = $this->_smsConfigs['voucher_template'];
			if ($this->_smsConfigs['provider'] == SMSBrandname::PROVIDER_VMG) {
				return $this->callAPISendSmsVMG($phone, $code, $rule_end_date, $template);
			} else {
				return [
					"status" => "false",
					"message" => "Must send SMS with provider VMG"
				];
			}
		}else{
			return [
				"status" => "false",
				"message" => "Must send SMS with type VOUCHER"
			];
		}
	}

	protected function callAPISendSmsVMG($phone, $code, $rule_end_date, $template)
	{
		$url = "https://api.brandsms.vn/api/SMSBrandname/SendSMS";

		$message = str_replace('$VOUCHER$', $code, $template);
		$message = str_replace('$RULEDATE$', $rule_end_date, $message);

		$ch = curl_init($url);
		# Setup request to send json via POST.
		$payload = json_encode(array(
			"to" => $phone,
			"from" => $this->_smsConfigs['brandname'],
			"type" => 1,
			"message" => $message,
			"scheduled" => ""
		));

		$header = array(
			"Content-Type:application/json",
			"token:" . $this->_smsConfigs['token']
		);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		# Return response instead of printing.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		# Send request.
		$result = curl_exec($ch);
		curl_close($ch);

		$obj = json_decode($result, true);

		$return = [
			"status" => false,
			"api_result" => $obj
		];

		if (isset($obj['errorCode']) && $obj['errorCode'] == "000") {
			$return["status"] = true;
		}

		$this->writeLog('func:callAPISendSMSVMG - $return: '.json_encode($return));

		return $return;
	}

	/**
	 * {@inheritdoc}
	 */
	public function callAPISendOTP($itemPhoneVerification)
	{
		if ($this->_smsConfigs['provider'] == SMSBrandname::PROVIDER_ESMS) {
			return $this->callAPISendOTPeSMS($itemPhoneVerification);
		} else {
			return $this->callAPISendOTPVMG($itemPhoneVerification);
		}
	}

	protected function callAPISendOTPVMG($itemPhoneVerification)
	{
		$url = "https://api.brandsms.vn/api/SMSBrandname/SendSMS";

		$ch = curl_init($url);
		# Setup request to send json via POST.
		$payload = json_encode(array(
			"to" => $itemPhoneVerification->getData('phone_number'),
			"from" => $this->_smsConfigs['brandname'],
			"type" => 1,
			"message" => str_replace('$AUTHCODE$', $itemPhoneVerification->getData('auth_code'), $this->_smsConfigs['message_template']),
			"scheduled" => ""
		));

		$header = array(
			"Content-Type:application/json",
			"token:" . $this->_smsConfigs['token']
		);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		# Return response instead of printing.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		# Send request.
		$result = curl_exec($ch);
		curl_close($ch);

		$obj = json_decode($result, true);

		$return = [
			"status" => false,
			"api_result" => $obj
		];

		if (isset($obj['errorCode']) && $obj['errorCode'] == "000") {
			$return["status"] = true;
		}

		$this->writeLog('func:callAPISendOTPVMG - $return: '.json_encode($return));

		return $return;
	}

	protected function callAPISendOTPeSMS($itemPhoneVerification)
	{
		$url = "http://rest.esms.vn/MainService.svc/json/SendMultipleMessage_V4_post_json/";

		$ch = curl_init($url);
		# Setup request to send json via POST.
		$payload = json_encode(array(
			"ApiKey" => $this->_smsConfigs['api_key'],
			"Content" => "Baotrixemay da nhan duoc so tien thanh toan {P2," . $itemPhoneVerification->getData('auth_code') . "} VND luc {P2,20} cho don hang {P1,20}. Cam on quy khach!",
			"Phone" => $itemPhoneVerification->getData('phone_number'),
			"SecretKey" => $this->_smsConfigs['secret_key'],
			"Brandname" => $this->_smsConfigs['brandname'],
			"SmsType" => 2
		));

		$header = array(
			"Content-Type:application/json"
		);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		# Return response instead of printing.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		# Send request.
		$result = curl_exec($ch);
		curl_close($ch);

		$obj = json_decode($result, true);

		$return = [
			"status" => false,
			"api_result" => $obj
		];

		if (isset($obj['CodeResult']) && $obj['CodeResult'] == 100) {
			$return["status"] = true;
		}

		return $return;
	}

	/**
	 * @param $info
	 * @param $type  [error, warning, info]
	 * @return 
	 */
	private function writeLog($info, $type = "info")
	{
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sms_otp_log.log');
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
