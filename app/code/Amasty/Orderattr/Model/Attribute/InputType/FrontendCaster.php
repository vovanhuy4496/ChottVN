<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Model\Attribute\InputType;

use Amasty\Orderattr\Model\Config\Source\DateFormat;
use Amasty\Orderattr\Model\ConfigProvider;
use Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\RelationDetails\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\Orderattr\Model\Value\LastCheckoutValue;

class FrontendCaster
{
    /**
     * EAV attribute properties to fetch from meta storage
     * @var array
     */
    protected $metaPropertiesMap = [
        'dataType' => 'getFrontendInput',
        'visible' => 'getIsVisibleOnFront',
        'required' => 'getIsRequired',
        'notice' => 'getNote',
        'default' => 'getDefaultOrLastValue',
    ];

    /**
     * @var \Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\RelationDetails\CollectionFactory
     */
    private $relationCollectionFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int
     */
    private $sortOrder = 0;

    /**
     * @var LastCheckoutValue
     */
    private $lastCheckoutValue;

    public function __construct(
        CollectionFactory $relationCollectionFactory,
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider,
        LastCheckoutValue $lastCheckoutValue
    ) {
        $this->relationCollectionFactory = $relationCollectionFactory;
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->lastCheckoutValue = $lastCheckoutValue;
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeInterface              $attribute
     * @param \Amasty\Orderattr\Model\Attribute\InputType\InputType $inputType
     * @param string                                                $providerName
     * @param string                                                $dataScope
     *
     * @return array|bool
     */
    public function cast($attribute, $inputType, $providerName, $dataScope)
    {
        $element = [
            'formElement' => $inputType->getFrontendInputType(),
            'label' => __($attribute->getStoreLabel())
        ];

        foreach ($this->metaPropertiesMap as $metaName => $methodName) {
            $value = $attribute->$methodName();
            $element[$metaName] = $value;
        }

        $element['sortOrder'] = $this->sortOrder++;

        if (!$element['visible']) {
            return false;
        }

        if ($inputType->getSourceModel()) {
            $this->setOptions($element, $attribute, $inputType);
        }

        $element['frontend_class'] = $attribute->getFrontend()->getClass();

        if (!empty($element['frontend_class'])) {
            foreach (explode(' ', $element['frontend_class']) as $key) {
                $element['validation'][$key] = true;
            }
        }

        $validateRules = $attribute->getValidateRules();
        if (!empty($validateRules['min_text_length'])) {
            $element['validation']['min_text_length'] = $validateRules['min_text_length'];
        }
        if (!empty($validateRules['max_text_length'])) {
            $element['validation']['max_text_length'] = $validateRules['max_text_length'];
        }

        $element['shipping_methods'] = $attribute->getShippingMethods();

        $tooltips = $attribute->getStoreTooltips();
        if (!empty($tooltips[$this->storeManager->getStore()->getId()])) {
            $element['config']['tooltip']['description'] = $tooltips[$this->storeManager->getStore()->getId()];
        }

        $this->setElementRelations($element, $attribute, $inputType);

        if ($attribute->isSaveToFutureCheckout()
            && (($value = $this->lastCheckoutValue->retrieve($attribute)) !== null)
            && $value !== false
            && $value !== ""
        ) {
            $element['value'] = $value;
        } elseif ($element['default'] !== null) {
            $element['value'] = $element['default'];
        }
        unset($element['default']);

        $this->setSpecificAttributeOptions($element, $attribute, $inputType);

        $element = array_merge_recursive(
            $element,
            [
                'component' => $inputType->getFrontendUiComponent(),
                'config' => [
                    'customScope' => $dataScope,
                    'template' => 'ui/form/field',
                    'elementTmpl' => !empty($inputType->getFrontendTmpl())
                        ? $inputType->getFrontendTmpl()
                        : 'ui/form/element/' . $element['formElement']
                ],
                'dataScope' => $dataScope . '.' . $attribute->getAttributeCode(),
                'provider' => $providerName
            ]
        );

        return $element;
    }

    /**
     * @param array                                                 &$element
     * @param \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface $attribute
     * @param \Amasty\Orderattr\Model\Attribute\InputType\InputType &$inputType
     *
     * @return void
     */
    protected function setOptions(&$element, $attribute, &$inputType)
    {
        $allOptions = $attribute->getSource()->getAllOptions(false);

        if ($inputType->isDisplayEmptyOption()) {
            array_unshift($allOptions, ['label' => ' ', 'value' => '']);
        }
        $element['options'] = $allOptions;
    }

    /**
     * @param array                                                 &$element
     * @param \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface $attribute
     * @param \Amasty\Orderattr\Model\Attribute\InputType\InputType &$inputType
     *
     * @return void
     */
    protected function setSpecificAttributeOptions(&$element, $attribute, &$inputType)
    {
        switch ($inputType->getFrontendInputType()) {
            case 'multiselect':
                $element['size'] = (int)$attribute->getMultiselectSize();
                break;
            case 'datetime':
                $validationRules = $attribute->getValidationRules();
                $format = DateFormat::$formats[$this->configProvider->getDateFormat()]['format'];

                if (!isset($element['additionalClasses'])) {
                    $element['additionalClasses'] = '';
                }
                $element['additionalClasses'] .= ' date';
                $element['dataType'] = $element['formElement'] = 'date';
                $element['options'] = [
                    'dateFormat' => $this->configProvider->getDateFormatJs(),
                    'showsTime'  => true,
                    'timeFormat' =>  $this->configProvider->getTimeFormatJs(),
                    'showOn' => 'both'
                ];

                if (!empty($element['value'])) {
                    $element['value'] = date(
                        $format . ' ' . $this->configProvider->getTimeFormat(),
                        strtotime($element['value'])
                    );
                }

                if (!empty($validationRules['date_range_min'])) {
                    $element['options']['minDate'] = date($format, $validationRules['date_range_min']);
                }

                if (!empty($validationRules['date_range_max'])) {
                    $element['options']['maxDate'] = date($format, $validationRules['date_range_max']);
                }
                break;
            case 'date':
                $validationRules = $attribute->getValidationRules();
                $format = DateFormat::$formats[$this->configProvider->getDateFormat()]['format'];
                if (!isset($element['additionalClasses'])) {
                    $element['additionalClasses'] = '';
                }
                $element['additionalClasses'] .= ' date';
                $element['options'] = [
                    'dateFormat' => $this->configProvider->getDateFormatJs(),
                    'showOn' => 'both'
                ];

                $element['inputDateFormat'] = $this->configProvider->getDateFormatJs();

                if (!empty($validationRules['date_range_min'])) {
                    $element['options']['minDate'] = date($format, $validationRules['date_range_min']);
                }

                if (!empty($validationRules['date_range_max'])) {
                    $element['options']['maxDate'] = date($format, $validationRules['date_range_max']);
                }

                if (!empty($element['value'])) {
                    $element['value'] = date($format, strtotime($element['value']));
                }
                break;
        }
    }

    /**
     * @param array                                                 &$element
     * @param \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface $attribute
     * @param \Amasty\Orderattr\Model\Attribute\InputType\InputType &$inputType
     *
     * @return void
     */
    protected function setElementRelations(&$element, $attribute, &$inputType)
    {
        if ($inputType->isManageOptions()) {
            $element['relations'] = $this->relationCollectionFactory->create()
                ->getAttributeRelations($attribute->getAttributeId());
        }
    }
}
