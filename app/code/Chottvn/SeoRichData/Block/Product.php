<?php
namespace Chottvn\SeoRichData\Block;

class Product extends \Amasty\SeoRichData\Block\Product
{
	/**
     * @param \Magento\Catalog\Model\Product $product
     * @param $priceCurrency
     * @param $orgName
     * @return array
     */
    protected function generateOffers($product, $priceCurrency, $orgName)
    {
        $price = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $offers = [
            '@type' => 'Offer',
            'priceCurrency' => $priceCurrency,
            'price' => round($price, 2),
            'availability' => $this->getAvailabilityCondition($product),
            'itemCondition' => self::NEW_CONDITION,
            'seller' => [
                '@type' => 'Organization',
                'name' => $orgName
            ],
            'url' => $product->getProductUrl(),
            'priceValidUntil' => date('Y-m-d H:i:s', strtotime("+30 days"))
        ];

        if ($product->getSpecialPrice()
            && $this->dateTime->timestamp() < $this->dateTime->timestamp($product->getSpecialToDate())
        ) {
            $offers['priceValidUntil'] = $this->dateTime->date(\DateTime::ATOM, $product->getSpecialToDate());
        }

        return $offers;
    }

}