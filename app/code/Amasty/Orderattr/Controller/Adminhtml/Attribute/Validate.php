<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Controller\Adminhtml\Attribute;

use Amasty\Orderattr\Model\ResourceModel\Entity\Entity;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Validate extends \Amasty\Orderattr\Controller\Adminhtml\Attribute
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    public function __construct(
        Context $context,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        parent::__construct($context);
        $this->eavConfig = $eavConfig;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\DataObject $response */
        $response = $this->dataObjectFactory->create();
        $response->setError(false);

        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $attributeId = $this->getRequest()->getParam('attribute_id');
        $attribute = $this->eavConfig->getAttribute(Entity::ENTITY_TYPE_CODE, $attributeCode);

        if ($attribute->getId() && !$attributeId) {
            if (strlen($this->getRequest()->getParam('attribute_code'))) {
                $response->setMessage(
                    __('An attribute with this code already exists.')
                );
            } else {
                $response->setMessage(
                    __('An attribute with the same code (%1) already exists.', $attributeCode)
                );
            }
            $response->setError(true);
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setJsonData($response->toJson());
    }
}
