<?php

namespace Chottvn\Sales\Observer\QuoteSubmit;

class BeforeSubmitObserver implements \Magento\Framework\Event\ObserverInterface{

	/**
     * Save custom attributes
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
		try {
			$order = $observer->getEvent()->getOrder();
			$quote = $observer->getEvent()->getQuote();

			$order->save();

			$orderId = $order->getId();
			$quoteId = $quote->getId();

			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

			$itemFactory = $objectManager->get('Magento\Sales\Model\Order\ItemFactory');
			$orderItemRepo = $objectManager->get('Magento\Sales\Api\OrderItemRepositoryInterface');
			$quoteFactory = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
			
			$quoteItem = $quoteFactory->create()->addFieldToFilter('quote_id', $quoteId);
			$salesOrderItem = $itemFactory->create()->getCollection()->addFieldToFilter('order_id', $orderId);

			foreach($salesOrderItem as $orders) {
				foreach($quoteItem as $item) {
					if ($item->getId() == $orders->getQuoteItemId()) {
						$itemId = $orders->getItemId();
						$this->writeLog($itemId); // get id from table sales_order_item
						$salesOrder = $orderItemRepo->get($itemId);

						$cartPromoParentId = $item->getData('cart_promo_parent_id') ? $item->getData('cart_promo_parent_id'): '';
					    $cartPromoItemIds = $item->getData('cart_promo_item_ids') ? $item->getData('cart_promo_item_ids'): '';
					    $cartPromoOption = $item->getData('cart_promo_option') ? $item->getData('cart_promo_option'): '';
					    $cartPromoIds = $item->getData('cart_promo_ids') ? $item->getData('cart_promo_ids'): '';
					    $cartPromoQty = $item->getData('cart_promo_qty') ? $item->getData('cart_promo_qty'): '';
					    $cartPromoParentItemId = $item->getData('cart_promo_parent_item_id') ? $item->getData('cart_promo_parent_item_id'): '';

						$salesOrder->setCartPromoParentId($cartPromoParentId);
						$salesOrder->setCartPromoItemIds($cartPromoItemIds);
						$salesOrder->setCartPromoOption($cartPromoOption);
						$salesOrder->setCartPromoIds($cartPromoIds);
						$salesOrder->setCartPromoQty($cartPromoQty);
						$salesOrder->setCartPromoParentItemId($cartPromoParentItemId);
						$salesOrder->save();
						break;
					}
				}
			}
			$order->save();

			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

			$savingsAmount = $quote->getSavingsAmount();
			$originalTotal = $quote->getOriginalTotal();
			$getFlagShipping = $quote->getFlagShipping();
			
			// $this->writeLog($savingsAmount);
			// $this->writeLog($originalTotal);

			$order->setSavingsAmount($savingsAmount);
			$order->setBaseSavingsAmount($savingsAmount);
			$order->setOriginalTotal($originalTotal);
			$order->setBaseOriginalTotal($originalTotal);
			
			$order->setFlagShipping($getFlagShipping);

			$_request = $objectManager->get('Magento\Framework\Webapi\Rest\Request');
			$array_request = $_request->getBodyParams();

			$extension_attributes = $array_request['billingAddress']['extension_attributes'];
			// foreach(get_class_methods($order) as $item){
			// 	$this->writeLog('Order==>'.$item);
			// }

			$isGuest = $order->getCustomerIsGuest();
			$groupId  = $order->getCustomerGroupId();				
			// $session = $objectManager->create('Magento\Customer\Model\Session');
			$collectionQuote = $salesOrderItem
            ->addFieldToFilter('cart_promo_option', ['null' => true])
            ->addFieldToFilter('product_type', ['neq' => 'configurable']);
                                            
            $collectionQuote->getSelect()
                            ->reset(\Zend_Db_Select::COLUMNS)
                            ->columns('SUM(qty_ordered) as sum_qty');
            $sum_qty = 0;
            if (isset($collectionQuote->getData()[0]['sum_qty'])) {
                $sum_qty = (int) $collectionQuote->getData()[0]['sum_qty'];
            }
            if ($sum_qty > 0) {
                $order->setTotalQtyOrdered($sum_qty);
            }   
			/*
			* Set Order Contact: Phone Number, Email
			*/		
			$phoneNumber = "";
			$email = "";
			$nameCustomer = "";
			// $prefixCustomer = "";

			if (!empty($extension_attributes)) {
				$affiliateValue = '';
				if (isset($extension_attributes['affiliate_account_code'])) {
					$affiliateValue = mb_strtoupper($extension_attributes['affiliate_account_code'], 'UTF-8');
				}
				$conditionAffiliate = '';
				$conditionAffiliate = $this->getConditionAffiliate($affiliateValue);
				if ($affiliateValue != '') {
					$customerObj = $objectManager->create('Magento\Customer\Model\ResourceModel\Customer\Collection');
					// $collection = $customerObj->addAttributeToSelect('*')->addAttributeToFilter([['attribute' => 'affiliate_code', 'eq' => $affiliateValue], ['attribute' => 'phone_number', 'eq' => $affiliateValue]])->addAttributeToFilter('affiliate_status', "activated")->load();
					$collection = $customerObj->addAttributeToSelect('*')->addAttributeToFilter([['attribute' => 'affiliate_code', 'eq' => $affiliateValue]])->addAttributeToFilter('affiliate_status', "activated")->load();
					// $this->writeLog($collection->getSelect()->__toString());
					$customerModel = $collection->getLastItem();
					// $this->writeLog($customerModel->getData());
					$affiliateAccountId = '';
					$affiliateLevel = '';
					if ($customerModel) {
						$affiliateAccountId = $customerModel->getData('entity_id');
						$affiliateLevel = $customerModel->getData('affiliate_level');
					}
					$order->setAffiliateAccountCode($affiliateValue);
					$order->setAffiliateAccountId($affiliateAccountId);
					$order->setAffiliateLevel($affiliateLevel);
				}
				
				$getFullname = $extension_attributes['firstname_ctt'];
				$getEmail = $extension_attributes['email_ctt'];
				$getPhone = $extension_attributes['telephone_ctt'];
				$fee_shipping_contact = $extension_attributes['fee_shipping_contact'];
				$order->setFeeShippingContact($fee_shipping_contact);
				if ($fee_shipping_contact == '1') {
					$order->setMaxDeliveryDates(null);
				} else {
					$order->setMaxDeliveryDates($quote->getMaxDeliveryDates());
				}

				$billing_address = $order->getBillingAddress();
				$billing_address->setFirstname($getFullname)
							->setTelephone($getPhone)
							->setEmail($getEmail);
				$billing_address->save();
				if (isset($extension_attributes['others_receive_products'])) {
					$getOthersTelephone = $extension_attributes['others_telephone'];
					$getOthersFullname = $extension_attributes['others_fullname'];
					$getOthersEmail = $extension_attributes['others_email'];

					$shipping_address = $order->getShippingAddress();
					$shipping_address->setFirstname($getOthersFullname)
								->setTelephone($getOthersTelephone)
								->setEmail($getOthersEmail);
					$shipping_address->save();
				} else {
					$shipping_address = $order->getShippingAddress();
					$shipping_address->setFirstname($getFullname)
								->setTelephone($getPhone)
								->setEmail($getEmail);
					$shipping_address->save();
				}
			}

			$phoneAddressBilling = $order->getBillingAddress()->getData("telephone");
			$emailAddressBilling = $order->getBillingAddress()->getData("email");
			$nameAddressBilling = $order->getBillingAddress()->getData("firstname");
			// $this->writeLog('Email Bill: '.$emailAddressBilling);
			// $phoneAddressShipping = $order->getShippingAddress()->getData("telephone");
			// $nameAddressShipping = $order->getShippingAddress()->getData("firstname");
			// $emailAddressShipping = $order->getShippingAddress()->getData("email");
			// $prefixAddressShipping = $order->getShippingAddress()->getData("prefix");

			// $this->writeLog('------getShippingAddress------');
			// $this->writeLog($order->getShippingAddress()->getData());
			// $this->writeLog('------getBillingAddress------');
			// $this->writeLog($order->getBillingAddress()->getData());
			// $this->writeLog($extension_attributes);

			// Log
				/*$this->writeLog('Email Ship: '.$emailAddressShipping);   
				$this->writeLog('Phone Ship: '.$phoneAddressShipping); 
				$this->writeLog('Email Bill: '.$emailAddressBilling);  
				$this->writeLog('Phone Bill: '.$phoneAddressBilling);*/
			if($isGuest == 1){ // Guest
				$phoneNumber = $phoneAddressBilling ? $phoneAddressBilling : '';
				$email = $emailAddressBilling ? $emailAddressBilling : '';
				$nameCustomer = $nameAddressBilling ? $nameAddressBilling : '';
				// if($prefixAddressShipping){
				// 	$prefix = $order->setCustomerPrefix($prefixAddressShipping);
				// }
			}else{ // Customer
				/*$phoneCustomer = $session->getCustomer()->getData("telephone"); 
				$emailCustomer = $session->getCustomer()->getData("email"); */
				$customer = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface')
										->getById($order->getCustomerId());    
				
				$phoneCustomer = $customer->getCustomAttribute('phone_number')->getValue(); 
				$emailCustomer = $customer->getEmail();
				$nameCustomer = $customer->getFirstname();
				// Log
					/*$this->writeLog('CusEmail: '.$phoneCustomer);    
					$this->writeLog('CusEmail: '.$emailCustomer); */
				$phoneNumber = $phoneAddressBilling ? $phoneAddressBilling : $phoneCustomer;
				$email = $emailAddressBilling ? $emailAddressBilling : '';
				$nameCustomer = $nameAddressBilling ? $nameAddressBilling : $nameCustomer;
			}
			// Update data to table sale_order
			$order->setCustomerEmail($email);
			$order->setChottCustomerPhoneNumber($phoneNumber);
			$order->setCustomerFirstname($nameCustomer);
			// Log
				/*$this->writeLog('Email: '.$email);    
				$this->writeLog('Phone: '.$phoneNumber); */           
			// Update data
			// if($email != "" && $order->getCustomerEmail() != $email){
			// 	$order->setCustomerEmail($email);
			// }
			// if($phoneNumber != "" && $order->getChottCustomerPhoneNumber() != $phoneNumber){
			// 	$order->setChottCustomerPhoneNumber($phoneNumber);
			// }
			// if($nameCustomer != "" && $order->getCustomerFirstname() != $nameCustomer){
			// 	$order->setCustomerFirstname($nameCustomer);
			// }

			// $this->writeLog(get_class_methods($order->getShippingAddress()));
			// $this->writeLog(get_class_methods($order->getBillingAddress()));
			// $this->writeLog($order->getShippingAddress()->getData());
			// $this->writeLog($order->getBillingAddress()->getData());
			// $this->writeLog($order->getShippingAddress()->getCustomAttribute());
			// $this->writeLog($order->getShippingAddress()->getCustomAttributes());
			/*
			* Option: NguoiKhacNhanHang
			*/
			if (empty($_REQUEST) && $isGuest == 0) { // co login
				$nameCustomer = $customer->getFirstname();

				// update township & email
				$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');

				$customer_address_id = $order->getShippingAddress()->getData('customer_address_id');
				if (isset($customer_address_id)) {
					$township = $order->getShippingAddress()->getData('township');
					$township_id = $order->getShippingAddress()->getData('township_id');
					$city_id = $order->getShippingAddress()->getData('city_id');
		
					$connection = $resource->getConnection();
					$connection->beginTransaction();
					$tableName = $resource->getTableName('customer_address_entity');

					$data = [
						"township" => $township,
						"township_id" => $township_id,
						"city_id" => $city_id,
						"email" => $email
					];
					$where = ['entity_id = ?' => $customer_address_id];
					$updatedRows = $connection->update($tableName, $data, $where);

					$connection->commit();
				}
			}
			if ($isGuest == 0) { // Customer
				$addressFactory = $objectManager->get('Magento\Customer\Model\AddressFactory');
				// set data billing address for default address
				$billingAddressId = $customer->getDefaultBilling();
				if (isset($billingAddressId)) {
					$billingAddress = $addressFactory->create()->load($billingAddressId);

					if (isset($billingAddress)) {
						$billingAddress->setData('firstname', $nameCustomer)
							->setData('telephone', $phoneNumber)
							// ->setData('prefix', $prefixCustomer)
							->setIsDefaultBilling(true)
							->setIsDefaultShipping(true);
						$billingAddress->save();
					}
				}
			}
		} catch(\Exception $e) {
			$this->writeLog("Exception:");
            $this->writeLog($e);
		}
		
	}
	
	/**
     * Get Condition Affiliate
     *
     * @param $affiliateValue
     * @return $conditionAffiliate
     */
    private function getConditionAffiliate($affiliateValue) {
        try {
            $conditionAffiliate = '';
            if ($affiliateValue != '') {
                $lengtAffiliate = strlen($affiliateValue);
                if ($lengtAffiliate <= 6) {
                    // mã cộng tác viên
                    if ($lengtAffiliate <= 3) {
                        if (is_numeric($affiliateValue)) {
                            $number = str_pad($affiliateValue, 3, '0', STR_PAD_LEFT);
                            $conditionAffiliate = 'CTV' . $number;
                        }
                    } else {
                        $firstString = substr($affiliateValue, 0, 3);
                        if ($firstString == "CTV" || $firstString == "cTV" || $firstString == "CtV" || $firstString == "CTv" || $firstString == "ctV" || $firstString == "Ctv" || $firstString == "cTv" || $firstString == "ctv") {
                            $secondString = substr($affiliateValue, 3, $lengtAffiliate);
                            $lengtsecondString = strlen($secondString);
                            if ($lengtsecondString <= 3) {
                                if (is_numeric($secondString)) {
                                    $number = str_pad($secondString, 3, '0', STR_PAD_LEFT);
                                    $conditionAffiliate = 'CTV' . $number;
                                }
                            }
                        }
                    }
                } else {
                    // số điện thoại
                    $number = str_replace(array('-', '.', ' '), '', $affiliateValue);
                    $lengtnumber = strlen($number);
                    if (preg_match('/((09|03|07|08|05)+([0-9]{8})\b)/', $number) && $lengtnumber > 9 && $lengtnumber <= 10) {
                        $conditionAffiliate = $number;
                    }
                }
            }
            // $this->writeLog('$conditionAffiliate 123: '.$conditionAffiliate);
            if ($conditionAffiliate == "") {
                $conditionAffiliate = $affiliateValue;
            }
            // $this->writeLog('$conditionAffiliate 456: '.$conditionAffiliate);
            return $conditionAffiliate;
        }
        catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }
        return $conditionAffiliate;
    }
	
    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info"){
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/before_submit_order.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);        	                 
        switch($type){
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