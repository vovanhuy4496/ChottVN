<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\CustomCatalog\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool;
use Magento\Catalog\Model\FilterProductCustomAttribute;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

/**
 * Catalog product model
 *
 * @api
 * @method Product setHasError(bool $value)
 * @method null|bool getHasError()
 * @method array getAssociatedProductIds()
 * @method Product setNewVariationsAttributeSetId(int $value)
 * @method int getNewVariationsAttributeSetId()
 * @method int getPriceType()
 * @method string getUrlKey()
 * @method Product setUrlKey(string $urlKey)
 * @method Product setRequestPath(string $requestPath)
 * @method Product setWebsiteIds(array $ids)
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Product extends \Magento\Catalog\Model\Product implements
    IdentityInterface,
    SaleableInterface,
    ProductInterface
{
	public function getNameShort()
    {
        return $this->_getData("product_name_distributor");
    }
    public function getNameLong()
    {
        return $this->getName();
    }
    public function getNameLongHtml()
    {
        $longName =  $this->getNameLong();
        $shortName = $this->getNameShort();
        if (empty($shortName)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($this->getId());
            $shortName = $product->getData('product_name_distributor');
        }
        $shortNameStrong = "<strong>".$shortName."</strong>";
        return str_replace($shortName, $shortNameStrong, $longName);
    }
    public function getDefaultStockCustom()
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
            $stockResolver = $objectManager->get('Magento\InventorySalesApi\Api\StockResolverInterface');
            $productSalableQty = $objectManager->get('Magento\InventorySalesApi\Api\GetProductSalableQtyInterface');

            $websiteCode = $storeManager->getWebsite()->getCode();
            $stock = $stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
            $stockId = $stock->getStockId();
            $productSku = $this->getSku();
            // $this->writeLog('productName: '.$this->getName());

            $defaultStockQty = $productSalableQty->execute($productSku, $stockId);
            // $sumQtyCurrentInQuoteItem = $this->sumQtyCurrentInQuoteItem();

            // return (int)$defaultStockQty - (int)$sumQtyCurrentInQuoteItem;
            // if ((int)$defaultStockQty >= (int)$sumQtyCurrentInQuoteItem) {
            //     return (int)$defaultStockQty - (int)$sumQtyCurrentInQuoteItem;
            // }
            // $this->writeLog('defaultStockQty: '.$defaultStockQty);
            return (int)$defaultStockQty;
        } catch(\Exception $e) {
            $this->writeLog($e);
            return 0;
        }
    }
    public function sumQtyCurrentInQuoteItem()
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $checkoutSession = $objectManager->create('Magento\Checkout\Model\Session');
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $productSku = (string)$this->getSku();
            $quote = $checkoutSession->getQuote();
            $quoteId = $quote->getId();
            if (isset($quoteId)) {
                //Select Data from table
                $sql = 'SELECT sku, SUM(qty) AS qty FROM quote_item WHERE parent_item_id IS NULL AND quote_id = '.$quoteId. ' AND sku = "'.$productSku.'" GROUP BY sku';
                // $this->writeLog($sql);
                $result = $connection->fetchRow($sql);
                if (isset($result['qty'])) {
                    return (int)$result['qty'];
                }
            }
            // $this->writeLog($result);
            return 0;
        } catch(\Exception $e) {
            $this->writeLog($e);
            return 0;
        }
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/DefaultStockCustom.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        switch($type){
        	case "error":
        		$logger->err($info);  
        		break;
        	case "warning":
        		$logger->notice($info);  
        		break;
        	case "info":
        		$logger->info($info);  
        		break;
        	default:
        		$logger->info($info);  
        }
	}
}

?>