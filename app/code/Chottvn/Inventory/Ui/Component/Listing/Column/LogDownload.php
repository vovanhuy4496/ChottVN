<?php

namespace Chottvn\Inventory\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;


/**
 * Class Thumbnail
 * @package Mageplaza\BannerSlider\Ui\Component\Listing\Column
 */
class LogDownload extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    

    /**
     * Thumbnail constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Image $imageModel
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
       
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {            
            foreach ($dataSource['data']['items'] as & $item) {
                $download = '<p> ' . $item['file_name'] .  '</p><a  target="_blank" href="'
                . $this->urlBuilder->getUrl('chottvn_inventory/log/download', ['log_id' => $item['log_id'] ]) . '">'
                . __('Download')
                . '</a>';

                $item["file_name"] = $download;
            }
        }

        return $dataSource;
    }
}
