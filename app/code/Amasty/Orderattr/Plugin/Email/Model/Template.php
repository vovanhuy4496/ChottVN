<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Plugin\Email\Model;

class Template
{
    const IS_SALES_EMAIL_VARIABLE = '{{var order.increment_id}}';

    /**
     * @var \Amasty\Orderattr\Model\ResourceModel\Attribute\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(\Amasty\Orderattr\Model\ResourceModel\Attribute\CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Plugin for adding attributes to marketing order emails to Insert Variable.
     *
     * @param \Magento\Framework\Mail\TemplateInterface|\Magento\Email\Model\Template $subject
     * @param array $result
     *
     * @return array $result
     */
    public function afterGetVariablesOptionArray($subject, $result)
    {
        if (!empty($result['value']) && $this->isSalesEmail($result)) {
            /** @var \Amasty\Orderattr\Model\ResourceModel\Attribute\Collection $attributeCollection */
            $attributeCollection = $this->collectionFactory->create();
            $attributeCollection->addFieldToSelect('attribute_code');
            $attributeCollection->addFieldToSelect('frontend_label');
            foreach ($attributeCollection->getData() as $attribute) {
                $result['value'][] = [
                    'label' => 'Amasty Order Attribute: ' . $attribute['frontend_label'],
                    'value' => '{{var order.getData(\'' . $attribute['attribute_code'] . '\')}}'
                ];
            }
        }

        return $result;
    }

    /**
     * @param array $result
     *
     * @return bool
     */
    private function isSalesEmail($result)
    {
        foreach ($result['value'] as $variable) {
            if ($variable['value'] === self::IS_SALES_EMAIL_VARIABLE) {

                return true;
            }
        }

        return false;
    }
}
