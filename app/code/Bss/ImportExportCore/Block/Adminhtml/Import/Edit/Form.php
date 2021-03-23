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
 * @package    Bss_ImportExportCore
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ImportExportCore\Block\Adminhtml\Import\Edit;

/**
 * Import edit form block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\ImportExport\Block\Adminhtml\Import\Edit\Form
{
    /**
     * Add fieldsets
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = $this->getForm();
        $elements = $form->getElements();
        foreach ($elements as $fieldset) {
            if ($fieldset->getId() == 'base_fieldset') {
                $fields = $fieldset->getElements();
                $versionHtml = '<div style="display:none;" id="bss-version"><span>'
                    . __("Version") .
                    '</span>: <span id="bss-version-number">*</span></div>';
                foreach ($fields as $field) {
                    if ($field->getName() == "entity") {
                        $field->setData("note", $versionHtml);
                    }
                }
            }
        }
        $uniqueBehaviors = $this->_importModel->getUniqueEntityBehaviors();
        foreach ($uniqueBehaviors as $behaviorCode => $behaviorClass) {
            $behaviorObject = $this->_behaviorFactory->create($behaviorClass);
            if (method_exists($behaviorObject, 'getEnableBehaviorFields')) {
                $neededFields = $behaviorObject->getEnableBehaviorFields();
                foreach ($elements as $fieldset) {
                    if ($fieldset->getId() == $behaviorCode . '_fieldset') {
                        $fields = $fieldset->getElements();
                        foreach ($fields as $field) {
                            if (isset($neededFields[$field->getName()])) {
                                $fieldConfig = $neededFields[$field->getName()];
                                if (count($fieldConfig) > 0) {
                                    foreach ($fieldConfig as $key => $value) {
                                        $field->setData($key, $value);
                                    }
                                }
                                unset($neededFields[$field->getName()]);
                            } else {
                                $fieldset->removeField($field->getId());
                            }
                        }

                        if (count($neededFields) > 0) {
                            foreach ($neededFields as $name => $newField) {
                                if ($name) {
                                    $field = $fieldset->addField(
                                        $behaviorCode . "_" .$name,
                                        isset($newField['type']) ? $newField['type'] : "text",
                                        [
                                            "name" => $name
                                        ]
                                    );

                                    foreach ($newField as $key => $value) {
                                        $field->setData($key, $value);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }
}
