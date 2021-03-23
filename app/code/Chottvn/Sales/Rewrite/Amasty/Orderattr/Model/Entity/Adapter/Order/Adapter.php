<?php

namespace  Chottvn\Sales\Rewrite\Amasty\Orderattr\Model\Entity\Adapter\Order;
class Adapter extends \Amasty\Orderattr\Model\Entity\Adapter\Order\Adapter
{
    /**
     * @var \Magento\Sales\Api\Data\OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * @var \Amasty\Orderattr\Model\Entity\EntityResolver
     */
    private $entityResolver;

    /**
     * @var \Amasty\Orderattr\Model\Entity\Handler\Save
     */
    private $saveHandler;

    /**
     * @var \Amasty\Orderattr\Model\Value\Metadata\FormFactory
     */
    private $metadataFormFactory;

    public function __construct(
        \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory,
        \Amasty\Orderattr\Model\Entity\EntityResolver $entityResolver,
        \Amasty\Orderattr\Model\Entity\Handler\Save $saveHandler,
        \Amasty\Orderattr\Model\Value\Metadata\FormFactory $metadataFormFactory
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->entityResolver = $entityResolver;
        $this->saveHandler = $saveHandler;
        $this->metadataFormFactory = $metadataFormFactory;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param bool                                   $force
     */
    public function addExtensionAttributesToOrder(\Magento\Sales\Api\Data\OrderInterface $order, $force = false)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if (empty($extensionAttributes)) {
            $extensionAttributes = $this->orderExtensionFactory->create();
            $order->setExtensionAttributes($extensionAttributes);
        }
        if (!$force && !empty($extensionAttributes->getAmastyOrderAttributes())) {
            return;
        }

        $entity = $this->entityResolver->getEntityByOrder($order);
        $customAttributes = $entity->getCustomAttributes();

        if (!empty($customAttributes)) {
            $extensionAttributes->setAmastyOrderAttributes($customAttributes);
        }
        $order->setExtensionAttributes($extensionAttributes);
        $this->setOrderData($order, $entity, $extensionAttributes->getAmastyOrderAttributes());
    }
    /**
     * Custom save attributes
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    public function saveOrderValues(\Magento\Sales\Api\Data\OrderInterface $order)
    {   
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getAmastyOrderAttributes()) {
            $entity = $this->entityResolver->getEntityByOrder($order);
            $attributes = $extensionAttributes->getAmastyOrderAttributes();
            $entityType = $entity->getParentEntityType();
            $parentId = $entity->getParentId();
            $entityId = $entity->getEntityId();
            $entity->unsetData();
            $entity->setParentEntityType($entityType);
            $entity->setParentId($parentId);
            $entity->setEntityId($entityId);
            $entity->setCustomAttributes($attributes);
            $this->setOrderData($order, $entity, $attributes);
            //query
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            
            $select = $connection->select()
            ->from($resource->getTableName('eav_attribute'))
            ->where('attribute_code = ?', 'vat_invoice_required');
            $row = $connection->fetchRow($select);
            
            // check condition
            if($entity->getData('vat_invoice_required') == $row['default_value']){
                $entity->setData('vat_company','');
                $entity->setData('vat_address','');
                $entity->setData('vat_number','');
                $entity->setData('vat_contact_name','');
                $entity->setData('vat_contact_phone_number','');
                $entity->setData('vat_contact_email','');
                $entity->setData('vat_contact_prefix','');
            }
            $this->saveHandler->execute($entity);
        }
    }
    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Amasty\Orderattr\Model\Entity\EntityData $entity
     * @param \Magento\Framework\Api\AttributeValue[] $attributes
     */
    private function setOrderData(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Amasty\Orderattr\Model\Entity\EntityData $entity,
        $attributes
    ) {
        if (!is_array($attributes)) {
            return;
        }
        $form = $this->createEntityForm($entity);
        $data = $form->outputData();

        foreach ($attributes as $orderAttribute) {
            $attributeCode = $orderAttribute->getAttributeCode();
            if (!empty($data[$attributeCode])) {
                $order->setData($attributeCode, $data[$attributeCode]);
            }
        }
    }

    /**
     * Return Form instance
     *
     * @param \Amasty\Orderattr\Model\Entity\EntityData $entity
     *
     * @return \Amasty\Orderattr\Model\Value\Metadata\Form
     */
    protected function createEntityForm($entity)
    {
        /** @var \Amasty\Orderattr\Model\Value\Metadata\Form $formProcessor */
        $formProcessor = $this->metadataFormFactory->create();
        $formProcessor->setFormCode('all_attributes')
            ->setEntity($entity)
            ->setInvisibleIgnored(false);

        return $formProcessor;
    }
     /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info"){
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sales_order_1.log');
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
?>