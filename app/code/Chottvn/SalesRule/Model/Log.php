<?php 
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\SalesRule\Model;

class Log extends \Magento\Framework\Model\AbstractModel{

	public function _construct(){
		$this->_init("Chottvn\SalesRule\Model\ResourceModel\Log");
	}

	
}	
