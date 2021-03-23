<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductAttributesImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductAttributesImportExport\Block\Adminhtml\Export\Filter;

use Magento\ImportExport\Model\Export as ExportModel;

/**
 * Class Form
 *
 * @package Bss\ProductAttributesImportExport\Block\Adminhtml\Export\Filter
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Catalog\Model\Product\AttributeSet\Options
     */
    protected $attributeSetArr;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSetArr
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSetArr,
        array $data = []
    ) {
        $this->attributeSetArr = $attributeSetArr;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'export_filter_form',
                    'action' => $this->getUrl('*/*/export'),
                    'method' => 'post',
                ],
            ]
        );

        $fieldset = $form->addFieldset('bss_filter_fieldset', ['legend' => __('Entity Attributes')]);
        $fieldset->addField(
            'select_attribute_set',
            'select',
            [
                'name' => ExportModel::FILTER_ELEMENT_GROUP .'[attribute_set]',
                'title' => __('Attribute Set'),
                'label' => __('Attribute Set'),
                'required' => false,
                'value' => 'all',
                'values' => $this->getAttributeSetOptions()
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return array
     */
    public function getAttributeSetOptions()
    {
        $options = [];
        $options[] = ["value" => "all", "label" => __("All")];
        $attributeSets = $this->attributeSetArr->toOptionArray();
        foreach ($attributeSets as $attrSet) {
            $options[] = [
                "value" => $attrSet['value'],
                "label" => $attrSet['label']
            ];
        }
        $options[] = ["value" => "no-attribute-set", "label" => __('No Attribute Set')];
        return $options;
    }
}
