<?php 
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Affiliate\Model;

class Log extends \Magento\Framework\Model\AbstractModel{

 	const EVENT_REGISTERED = 'registered';
  const EVENT_PHONE_VERIFIED = 'phone_verified';
  const EVENT_REJECTED = 'rejected';
  const EVENT_PROCESSING = 'processing';
  const EVENT_APPROVED = 'approved';
  const EVENT_ACTIVATED = 'activated';
  const EVENT_MARGIN_LIMIT_CHANGED = 'margin_limit_changed';
  const EVENT_AFFILIATE_LEVEL_CHANGED = 'affiliate_level_changed';
  const EVENT_BANK_ACCOUNT_CHANGED = 'bank_account_changed';
  const EVENT_REREGISTER = "re-register";
  const EVENT_REQUEST_IDENTITY_CARD = 'request_identity_card';
  const EVENT_FREEZED = 'freezed';
  const EVENT_UNFREEZED = 'unfreezed';

	public function _construct(){
		$this->_init("Chottvn\Affiliate\Model\ResourceModel\Log");
	}

	
}	
