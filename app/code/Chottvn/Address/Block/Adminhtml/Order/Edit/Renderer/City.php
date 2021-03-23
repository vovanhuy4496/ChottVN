<?php

namespace Chottvn\Address\Block\Adminhtml\Order\Edit\Renderer;

class City extends \Magento\Backend\Block\AbstractBlock implements
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
        if ($region = $element->getForm()->getElement('region_id')) {
            $regionId = $region->getValue();
        } else {
            return $element->getDefaultHtml();
        }

        $city = $element->getForm()->getElement('city');
        $cityId = $element->getValue();

        $html = '<div class="field field-city-id required admin__field _required" style="display:none">';
        $element->setClass('input-text admin__control-text');
        $element->setRequired(true);
        $html .= $element->getLabelHtml() . '<div class="control admin__field-control">';

        $selectName = $element->getName();
        $selectId = $element->getHtmlId();
        $html .= '<select id="' . $selectId . '" name="' . $selectName . '" class="select required-entry admin__control-select">';
        $html .= '<option value="">' . __('Please select a city.') . '</option>';
        $html .= '</select>';

        $addressType = ($element->getForm()->getData('html_name_prefix') == 'order[shipping_address]') ? 'order-shipping-address' : '';
        $html .= '<script>';
        $html .=    'require(["jquery", "cityUpdater"], function($) {
                        $(document).ready(function () {
                            $("#'.$region->getHtmlId().'").mage("cityUpdater", {
                                cityListContainter: "#'.$element->getForm()->getData('html_id_prefix').'fields .field-city-id",
                                cityInputContainter: "#'.$element->getForm()->getData('html_id_prefix').'fields .field-city",
                                cityListId: "#'.$selectId.'",
                                cityInputId: "#'.$city->getHtmlId().'",
                                cityJson: '.$this->addressHelper->getCityJson().',
                                defaultCity: "'.$cityId.'",
                                addressType: "'.$addressType.'"
                            });
                            $(".field-city").css("display", "none");
                        });
                    });';
        $html .= '</script>';

        $html .= '</div></div>' . "\n";

        return $html;
    }
}
