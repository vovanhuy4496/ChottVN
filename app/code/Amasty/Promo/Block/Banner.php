<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Block;

use Amasty\BannersLite\Model\ConfigProvider as BannerConfigProvider;
use Amasty\BannersLite\Model\ProductBannerProvider;
use Amasty\Promo\Model\Config;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\View\Element\Template;
use Amasty\Promo\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 *
 * @method string getPosition()
 */
class Banner extends Template
{
    /**
     * @var \Magento\SalesRule\Model\Rule[]
     */
    private static $validRules = [];

    /**
     * @var Config
     */
    private $config;

    /**
     * @var BannerConfigProvider
     */
    private $bannerConfig;

    /**
     * @var RuleCollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ProductBannerProvider
     */
    private $bannerProvider;

    /**
     * @var Image
     */
    private $image;

    public function __construct(
        Template\Context $context,
        Config $config,
        BannerConfigProvider $bannerConfig,
        RuleCollectionFactory $ruleCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductBannerProvider\Proxy $bannerProvider,
        Image $image,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->config = $config;
        $this->bannerConfig = $bannerConfig;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->bannerProvider = $bannerProvider;
        $this->image = $image;
    }

    /**
     * @return \Magento\SalesRule\Model\Rule[]
     */
    public function getValidRules()
    {
        if (empty(self::$validRules)) {
            $validRulesIds = $this->bannerProvider->getValidRulesIds($this->getProductId());

            if ($this->bannerConfig->isOneBannerEnabled() && $validRulesIds) {
                $validRulesIds = array_slice($validRulesIds, 0, 1);
            }

            self::$validRules = $this->ruleCollectionFactory->create()
                ->addFieldToFilter(\Amasty\Promo\Api\Data\GiftRuleInterface::SALESRULE_ID, ['in' => $validRulesIds])
                ->getItems();
        }

        return self::$validRules;
    }

    /**
     * @param \Amasty\Promo\Model\Rule $rule
     *
     * @return bool
     */
    public function isShowGiftImages(\Amasty\Promo\Model\Rule $rule)
    {
        return boolval($rule->getData($this->getPosition() . '_banner_show_gift_images'));
    }

    /**
     * @param \Amasty\Promo\Model\Rule $rule
     *
     * @return $this|array
     */
    public function getProducts(\Amasty\Promo\Model\Rule $rule)
    {
        $products = [];

        if ($promoSku = $rule->getSku()) {
            $products = $this->productCollectionFactory->create()
                ->addFieldToFilter('sku', ['in' => explode(",", $promoSku)])
                ->addUrlRewrite()
                ->addAttributeToSelect(
                    [
                        'name',
                        'thumbnail',
                        $this->getAttributeHeader(),
                        $this->getAttributeDescription()
                    ]
                );
        }

        return $products;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        if (!$this->hasData('product_id')) {
            if (!empty($this->_request->getParam('product_id'))) {
                $this->setData('product_id', $this->_request->getParam('product_id'));
            } else {
                $this->setData('product_id', $this->_request->getParam('id'));
            }
        }

        return $this->_getData('product_id');
    }

    /**
     * @return string
     */
    public function getAttributeHeader()
    {
        return $this->config->getAttrForHeader();
    }

    /**
     * @return string
     */
    public function getAttributeDescription()
    {
        return $this->config->getAttrForDescription();
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        return $this->config->getImageWidth();
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->config->getImageHeight();
    }

    /**
     * @return Image
     */
    public function getImageHelper()
    {
        return $this->image;
    }
}
