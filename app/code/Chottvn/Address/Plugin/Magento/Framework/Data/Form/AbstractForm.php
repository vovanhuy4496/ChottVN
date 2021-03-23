<?php

namespace Chottvn\Address\Plugin\Magento\Framework\Data\Form;

class AbstractForm
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->layout = $layout;
    }

    /**
     *
     * @param \Magento\Framework\Data\Form\AbstractForm $subject
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $result
     * @param  string $elementId
     * @param  string $type
     * @param  array  $config
     * @param  bool|string|null  $after
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function afterAddField(
        \Magento\Framework\Data\Form\AbstractForm $subject,
        $result,
        $elementId,
        $type,
        $config,
        $after = false
    ) {
        if ($elementId == 'city_id') {
            $result->setRenderer($this->layout->createBlock(\Chottvn\Address\Block\Adminhtml\Order\Edit\Renderer\City::class));
        } elseif ($elementId == 'township_id') {
            $result->setRenderer($this->layout->createBlock(\Chottvn\Address\Block\Adminhtml\Order\Edit\Renderer\Township::class));
        }
        return $result;
    }
}
