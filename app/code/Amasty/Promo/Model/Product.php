<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Model;

class Product
{
    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    private $state;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    public function __construct(
        \Magento\CatalogInventory\Api\StockStateInterface $state,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockRegistryProvider
    ) {
        $this->state = $state;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistryProvider = $stockRegistryProvider;
    }

    /**
     * @param string $sku
     *
     * @return bool|float|int
     */
    public function getProductQty($sku)
    {
        $qty = 0;

        try {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $product = $this->productRepository->get($sku, false, $this->storeManager->getStore()->getId());
            if ($product->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
                || $product->getTypeId() === \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
            ) {
                return false;
            }

            if (!$this->isManageStock((int)$product->getId())) {
                return false;
            }

            $qty = $this->state->getStockQty(
                $product->getId(),
                $this->storeManager->getWebsite()->getId()
            );
            $stockItem =
                $this->stockRegistryProvider->getStockItem($product->getId(), $this->storeManager->getWebsite()->getId());

            if ($stockItem->getBackorders()) {
                $qty = $stockItem->getMaxSaleQty();
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->logger->critical($e->getTraceAsString());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e->getTraceAsString());
        }

        return $qty;
    }

    /**
     * @param int $productId
     *
     * @return bool
     */
    private function isManageStock($productId)
    {
        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        /** @var \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem */
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);

        return $stockItem->getManageStock();
    }
}
