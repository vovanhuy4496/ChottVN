<?php

namespace Chottvn\Address\Plugin\Magento\Quote\Model;

class ShippingMethodManagement
{
    /**
     * @var \Chottvn\Address\Helper\Data
     */
    private $helper;

    /**
     * @param \Chottvn\Address\Helper\Data $helper
     */
    public function __construct(
        \Chottvn\Address\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Assign custom address attribute to shipping address
     *
     * @param \Magento\Quote\Model\ShippingMethodManagement $subject
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     */
    public function beforeEstimateByExtendedAddress(
        \Magento\Quote\Model\ShippingMethodManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $address
    ) {       
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get('Magento\Framework\Webapi\Rest\Request');            
        if ($addressParams = $request->getBodyParams()){ 
            if($addressAttrs = $addressParams["address"]){
                if($addressCustomAttrs = $addressAttrs["custom_attributes"]){
                    $logger->info($addressCustomAttrs); 
                    try{
                        foreach ($addressCustomAttrs as $addressCustomAttr) { 
                            $attributeCode = $addressCustomAttr["attribute_code"];
                            $value = $addressCustomAttr["value"];    
                            if ($value != null && $attributeCode) {
                                $set = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $attributeCode)));
                                $address->$set($value);
                            }
                        }
                    }
                    catch(\Exception $e){
                        
                    }                    
                }
            }
        }
        /*$extAttributes = $address->getExtensionAttributes();                
        if (!empty($extAttributes)) {            
            $this->helper->transportFieldsFromExtensionAttributesToObject(
                $extAttributes,
                $address,
                'extra_checkout_shipping_address_fields'
            );
        }*/
    }
}
