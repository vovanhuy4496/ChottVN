<?php

namespace Chottvn\Address\Plugin\Magento\Ui\Component\Form;

class AttributeMapper
{
    /**
     * @var \Magento\Framework\App\Request
     */
    private $requestInterface;

    /**
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->requestInterface = $requestInterface;
    }

    /**
     * Hide township from checkout process
     *
     * @param \Magento\Ui\Component\Form\AttributeMapper $subject
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @return array
     */
    public function afterMap(
        \Magento\Ui\Component\Form\AttributeMapper $subject,
        $result,
        $attribute
    ) {
        if ($this->requestInterface->getFullActionName() == 'checkout_index_index' &&
            in_array($attribute->getAttributeCode(), ['township_id'])
        ) {
            unset($result['options']);
        }
        return $result;
    }
}
