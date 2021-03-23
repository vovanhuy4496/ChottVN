<?php
namespace Bss\ProductAttributesImportExport\Model\Source\Import\Behavior;

use Magento\ImportExport\Model\Import;

class ProductAttribute extends \Magento\ImportExport\Model\Source\Import\Behavior\Basic
{
    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return 'bss_product_attribute';
    }

    /**
     * @return array
     */
    public function getEnableBehaviorFields()
    {
        $fields = [
            "behavior" => [],
            Import::FIELD_NAME_VALIDATION_STRATEGY => [],
            Import::FIELD_NAME_ALLOWED_ERROR_COUNT => [],
            Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR => [
                "value" => "|"
            ]
        ];

        $fields['_import_option_value_separator'] = [
            "name" => "_import_option_value_separator",
            "value" => ":",
            "label" => __("Option Value Separator"),
            "title" => __("Option Value Separator"),
            "type" => "text",
            "class" => ""
        ];

        $fields['_import_store_view_separator'] = [
            "name" => "_import_store_view_separator",
            "value" => ";",
            "label" => __("Store View Separator"),
            "title" => __("Store View Separator"),
            "type" => "text",
            "class" => ""
        ];

        return $fields;
    }
}
