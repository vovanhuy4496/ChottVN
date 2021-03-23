<?php

namespace Chottvn\Address\Block\Adminhtml\Order\Edit\Renderer;

class Township extends \Magento\Backend\Block\AbstractBlock implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var \Chottvn\Address\Helper\Data
     */
    protected $addressHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Chottvn\Address\Helper\Data $addressHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Chottvn\Address\Helper\Data $addressHelper,
        array $data = []
    ) {
        $this->addressHelper = $addressHelper;
        parent::__construct($context, $data);
    }

    /**
     * Output the region element and javasctipt that makes it dependent from country element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($city = $element->getForm()->getElement('city_id')) {
            $cityId = $city->getValue();
        } else {
            return $element->getDefaultHtml();
        }

        $township = $element->getForm()->getElement('township');
        $townshipId = $element->getValue();

        $html = '<div class="field field-township-id admin__field" style="display:block">';
        $element->setClass('input-text admin__control-text');
        $html .= $element->getLabelHtml() . '<div class="control admin__field-control">';

        $selectName = $element->getName();
        $selectId = $element->getHtmlId();
        $html .= '<select id="' . $selectId . '" name="' . $selectName . '" class="select admin__control-select">';
        $html .= '<option value="">' . __('Please select a township.') . '</option>';
        $html .= '</select>';

        $addressType = ($element->getForm()->getData('html_name_prefix') == 'order[shipping_address]') ? 'order-shipping-address' : '';
        $html .= '<script>';
        $html .=    'require(["jquery", "townshipUpdater"], function($) {
                        $(document).ready(function () {
                            $("#'.$city->getHtmlId().'").mage("townshipUpdater", {
                                townshipListContainter: "#'.$element->getForm()->getData('html_id_prefix').'fields .field-township-id",
                                townshipInputContainter: "#'.$element->getForm()->getData('html_id_prefix').'fields .field-township",
                                townshipListId: "#'.$selectId.'",
                                townshipInputId: "#'.$township->getHtmlId().'",
                                townshipJson: '.$this->addressHelper->getTownshipJson().',
                                defaultTownship: "'.$townshipId.'",
                                addressType: "'.$addressType.'"
                            });
                            $(".field-township").css("display", "none");
                        });
                    });';
        $html .= '</script>';

        $html .= '</div></div>' . "\n";

        return $html;
    }
}
