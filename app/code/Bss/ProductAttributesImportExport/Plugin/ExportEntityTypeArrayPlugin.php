<?php
namespace Bss\ProductAttributesImportExport\Plugin;

class ExportEntityTypeArrayPlugin extends \Magento\ImportExport\Model\Source\Export\Entity
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * ExportEntityTypeArrayPlugin constructor.
     * @param \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->request = $request;
        $this->moduleManager = $moduleManager;
        parent::__construct($exportConfig);
    }

    /**
     * Around to option array
     *
     * @param \Magento\ImportExport\Model\Source\Export\Entity $subject
     * @param \Closure $proceed
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundToOptionArray($subject, \Closure $proceed)
    {
        if ($this->moduleManager->isEnabled("Bss_ImportExportCore")) {
            return $proceed();
        }
        $bssOptions = [];
        $bssOptions[] = ['label' => __('-- Please Select --'), 'value' => ''];
        $options = [];
        $options[] = ['label' => __('-- Please Select --'), 'value' => ''];
        foreach ($this->_exportConfig->getEntities() as $entityName => $entityConfig) {
            if (strpos($entityName, 'bss')!==false) {
                $bssOptions[] = ['label' => __($entityConfig['label']), 'value' => $entityName];
            } else {
                $options[] = ['label' => __($entityConfig['label']), 'value' => $entityName];
            }
        }

        if ($this->request->getFullActionName() == "bssimportexport_export_index") {
            return $bssOptions;
        }
        return $options;
    }
}
