<?php

namespace Chottvn\Address\Block\Adminhtml\Township\Edit\Tab;

class Labels extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    /**
     * @var \Chottvn\Address\Model\TownshipFactory
     */
    private $townshipFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Chottvn\Address\Model\TownshipFactory $townshipFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Chottvn\Address\Model\TownshipFactory $townshipFactory,
        array $data = []
    ) {
        $this->townshipFactory = $townshipFactory;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_nameInLayout = 'store_view_labels';
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('Labels');
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Labels');
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $currentTownship = $this->_coreRegistry->registry('address_township');

        if (!$currentTownship) {
            $id = $this->getRequest()->getParam('township_id');
            $currentTownship = $this->townshipFactory->create();
            $currentTownship->load($id);
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $labels = $currentTownship->getStoreLabels();

        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset = $this->_createStoresLabelFieldset($form, $labels);
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Create store specific fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @param array $labels
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     */
    protected function _createStoresLabelFieldset($form, $labels)
    {
        $fieldset = $form->addFieldset(
            'store_labels_fieldset',
            ['legend' => __('Store View Specific Labels'), 'class' => 'store-scope']
        );
        $renderer = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset::class
        );
        $fieldset->setRenderer($renderer);

        $locale = [];
        foreach ($this->_storeManager->getWebsites() as $website) {
            $fieldset->addField(
                "w_{$website->getId()}_label",
                'note',
                ['label' => $website->getName(), 'fieldset_html_class' => 'website']
            );
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (empty($stores)) {
                    continue;
                }
                $fieldset->addField(
                    "sg_{$group->getId()}_label",
                    'note',
                    ['label' => $group->getName(), 'fieldset_html_class' => 'store-group']
                );
                foreach ($stores as $store) {
                    $localeCode = $this->_scopeConfig->getValue(
                        \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $store->getCode()
                    );
                    $disabled = false;
                    if (!in_array($localeCode, $locale)) {
                        $locale[] = $localeCode;
                    } else {
                        $disabled = true;
                    }
                    $fieldset->addField(
                        "s_{$store->getId()}",
                        'text',
                        [
                            'name' => ($disabled == false) ? 'store_labels[' . $localeCode . ']' : '',
                            'title' => $store->getName(),
                            'label' => $store->getName(),
                            'required' => false,
                            'value' => isset($labels[$localeCode]) ? $labels[$localeCode] : '',
                            'fieldset_html_class' => 'store',
                            'data-form-part' => 'township_form',
                            'disabled' => $disabled
                        ]
                    );
                }
            }
        }
        return $fieldset;
    }
}
