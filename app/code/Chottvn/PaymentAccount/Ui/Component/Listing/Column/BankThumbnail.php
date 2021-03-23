<?php

namespace Chottvn\PaymentAccount\Ui\Component\Listing\Column;

use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Chottvn\PaymentAccount\Model\Config\Source\BankImage as Image;

/**
 * Class Thumbnail
 * @package Mageplaza\BannerSlider\Ui\Component\Listing\Column
 */
class BankThumbnail extends Column
{
    /**
     * @var Image
     */
    protected $imageModel;

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
        Image $imageModel,
        array $components = [],
        array $data = []
    ) {
       
        $this->urlBuilder = $urlBuilder;
        $this->imageModel = $imageModel;
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
            $path = $this->imageModel->getBaseUrl();
            foreach ($dataSource['data']['items'] as & $item) {
                $bank = new DataObject($item);
                if ($item['image']) {
                    $item[$fieldName . '_src'] = $path . $item['image'];
                }

                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                   'chottvn_paymentaccount/bank/edit',
                    ['bank_id' => $bank->getBankId(), 'store' => $this->context->getRequestParam('store')]
                );
            }
        }

        return $dataSource;
    }
}
