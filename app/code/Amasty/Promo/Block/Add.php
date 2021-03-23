<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */

namespace Amasty\Promo\Block;

class Add extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\Promo\Helper\Data
     */
    private $promoHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    private $urlHelper;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Promo\Helper\Data $promoHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Amasty\Promo\Model\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->promoHelper = $promoHelper;
        $this->urlHelper = $urlHelper;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function hasItems()
    {
        return (bool)$this->promoHelper->getNewItems();
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->config->getAddMessage();
    }

    /**
     * @return bool
     */
    public function isOpenAutomatically()
    {
        return $this->config->isAutoOpenPopup() && $this->hasItems();
    }

    /**
     * @return string
     */
    public function getCurrentBase64Url()
    {
        return $this->urlHelper->getCurrentBase64Url();
    }

    /**
     * @return null
     */
    public function getAvailableProductQty()
    {
        return $this->promoHelper->getAllowedProductQty();
    }

    /**
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('amasty_promo/cart/add');
    }

    /**
     * @return mixed
     */
    public function getSelectionMethod()
    {
        return $this->config->getSelectionMethod();
    }

    /**
     * @return mixed
     */
    public function getGiftsCounter()
    {
        return $this->config->getGiftsCounter();
    }
}
