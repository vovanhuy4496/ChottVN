<?php

namespace Chottvn\Address\Block\Adminhtml\City\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 */
class DeleteButton implements ButtonProviderInterface
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $cityEntity = $this->registry->registry('address_city');
        if ($cityEntity) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'id' => 'collections-edit-delete-button',
                'data_attribute' => [
                    'url' => $this->getDeleteUrl()
                ],
                'on_click' => 'deleteConfirm(\'' . __(
                    "Are you sure you want to do this?"
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
            return $data;
        }
        return [];
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        $cityEntity = $this->registry->registry('address_city');
        return $this->urlBuilder->getUrl('*/*/delete', ['city_id' => $cityEntity->getId()]);
    }
}
