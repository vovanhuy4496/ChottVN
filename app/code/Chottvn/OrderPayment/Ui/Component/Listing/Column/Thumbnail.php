<?php

namespace Chottvn\OrderPayment\Ui\Component\Listing\Column;

use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Chottvn\OrderPayment\Model\BankAccount\DataProvider;

/**
 * Class Thumbnail
 * @package Mageplaza\BannerSlider\Ui\Component\Listing\Column
 */
class Thumbnail extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
     /**
     * @var DataProvider
     */
    public $dataProvider;

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
        DataProvider $dataProvider,
        array $components = [],
        array $data = []
    ) {
       
        $this->urlBuilder = $urlBuilder;
        $this->dataProvider = $dataProvider;
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
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $banner = new DataObject($item);
                if ($item['bank_image']) {
                    $item[$fieldName . '_src'] = $this->dataProvider->getMediaUrl(). $item['bank_image'];
                }

                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'chottvn_orderpayment/bankaccount/edit',
                    ['bankaccount_id' => $banner->getBankaccountId(), 'store' => $this->context->getRequestParam('store')]
                );
            }
        }

        return $dataSource;
    }
}
