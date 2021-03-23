<?php

namespace Chottvn\CustomerMembership\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Asset\Repository;
 
class Data extends AbstractHelper
{
 	protected $assetRepo;
 
	public function __construct(
	        Context $context,
	 		CustomerRepositoryInterface $customerRepository,
	 		Repository $assetRepo
	    ){
	 	$this->customerRepository = $customerRepository;
	 	$this->assetRepo = $assetRepo;
	    parent::__construct($context);
	}
	 
	public function getAttributeValue($_customer, $attributeCode){
		if (! is_object($_customer)){
			try{
				$customer = $this->customerRepository->getById($_customer);
			} catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
				$customer = null;
			}			
		}else{
			$customer = $_customer;
		}		
	 	$attribute  = $customer ? $customer->getCustomAttribute($attributeCode) : null;
	 	return $attribute ? $attribute->getValue() : "";
	}

	public function getCustomerLevel($_customer){
		$level = $this->getAttributeValue($_customer, "customer_level");
		return $level ? $level : "member";
	}
	public function getCustomerLevelName($_customer){
		$levelName = "level_" . $this->getCustomerLevel($_customer);
		return  __($levelName);
	}
	public function getCustomerLevelCssClass($_customer){
		$levelClass = "level-" . $this->getCustomerLevel($_customer);
		return $levelClass;
	}
	public function getCustomerLevelImageUrl($_customer){
		$levelImage = "level_" . $this->getCustomerLevel($_customer).".png";
		return $this->assetRepo->getUrl('Chottvn_CustomerMembership::images/'.$levelImage); 
	}
	
}


?>