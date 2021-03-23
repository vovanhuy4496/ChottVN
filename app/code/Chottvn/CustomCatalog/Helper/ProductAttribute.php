<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\CustomCatalog\Helper;

use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class ProductAttribute extends AbstractHelper
{
  protected $_objectManager;
  protected $_storeManager;

  public function __construct(
    ObjectManagerInterface $objectManager,
    StoreManagerInterface $storeManagerInterface
  ){
    $this->_objectManager = $objectManager;
    $this->_storeManager = $storeManagerInterface;
  }

  public function getHtmlAttributeBySwatchInputType($inputType, $product, $attributeCode, $currentAttribute, $attributeOptions, $is_show_data){
    $helperImage = $this->_objectManager->get('\Magento\Catalog\Helper\Image');
    $priceHelper = $this->_objectManager->create('\Chottvn\PriceDecimal\Helper\Data');

    // get attribute value
    $product_attribute_value = $product->getData($attributeCode);

    // get iamge
    $imageUrl = '';
    if($is_show_data['is_show_image']){
      // image product
      $imageUrl = $helperImage->init($product, 'product_page_image_small')
                           ->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
                           ->resize(200)
                           ->getUrl();
    }

    // return html data
    $html = '';
    foreach ($attributeOptions as $keyOption => $valueOption) {
      $current_selected = '';
      $current_checked = '';
      $outOfStock = '';
      
      // check current simple product page
      if($keyOption == $currentAttribute){ $current_selected = 'selected';$current_checked = 'checked'; }
      // check out of stock
      if($product->getIsSalable() == false){ $outOfStock = 'out-of-stock'; }
      // get price
      $finalPrice = $priceHelper->formatPrice($product->getFinalPrice());

      // print data
      if($product_attribute_value == $keyOption){
        switch ($inputType) {
          case 'dropdown':
            $html_description = '';
            $html_description .= htmlspecialchars('<span class="item-name">'.$valueOption.'</span>');
            if($is_show_data['is_show_price']){
                $html_description .= htmlspecialchars($finalPrice);
              }
            $html .= '<option class="simple-item '.$current_selected.' '.$outOfStock.'" '.$current_selected.' value="'.$product->getProductUrl().'" data-description="'.$html_description.'">'.$valueOption.'</option>';
            break;
          
          case 'text':
            $html .= '<a href="'.$product->getProductUrl().'" title="'.$valueOption.'">';
              $html .= '<div class="simple-item '.$current_selected.' '.$outOfStock.'">';
                  // check image url
                  if($imageUrl){
                    $html .= '<img src="'.$imageUrl.'" alt="'.$product->getName().'" />';
                  }else{
                    $html .= '<span class="name">'.$valueOption.'</span>';
                  }
                  
                  if($is_show_data['is_show_price']){
                    $html .= '<span class="item-price">'.$finalPrice.'</span>';
                  }
              $html .= '</div>';
            $html .= '</a>';
            break;

          case 'visual':
            $html .= '<div class="simple-item '.$current_selected.' '.$outOfStock.'">';
              $html .= '<a href="'.$product->getProductUrl().'" title="'.$valueOption.'">';
                // check image url
                $html .= '<div class="item-img">';
                if($imageUrl){
                  $html .= '<img src="'.$imageUrl.'" alt="'.$product->getName().'" />';
                }else{
                  $html .= '<span class="name">'.$valueOption.'</span>';
                }
                $html .= '</div>';
                
                if($is_show_data['is_show_price']){
                  $html .= '<span class="item-price">'.$finalPrice.'</span>';
                }
              $html .= '</a>';
            $html .= '</div>';
            break;
        }
      }
    }

    return $html;
  }

  public function getHtmlAttributeBySwatchInputTypeReverse($inputType, $simpleProductCollection, $attributeCode, $currentAttribute, $keyOption, $valueOption, $is_show_data){
    $helperImage = $this->_objectManager->get('\Magento\Catalog\Helper\Image');
    $priceHelper = $this->_objectManager->create('\Chottvn\PriceDecimal\Helper\Data');

    // return html data
    $html = '';
    foreach ($simpleProductCollection as $product) {
      $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());

      // get attribute value
      $product_attribute_value = $product->getData($attributeCode);

      // get iamge
      $imageUrl = '';
      if($is_show_data['is_show_image']){
        // image product
        $imageUrl = $helperImage->init($product, 'product_page_image_small')
                             ->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
                             ->resize(200)
                             ->getUrl();
      }

      $current_selected = '';
      $current_checked = '';
      $outOfStock = '';
      
      // check current simple product page
      if($keyOption == $currentAttribute){ $current_selected = 'selected';$current_checked = 'checked'; }
      // check out of stock
      if($product->getIsSalable() == false){ $outOfStock = 'out-of-stock'; }
      // get price
      $finalPrice = $priceHelper->formatPrice($product->getFinalPrice());

      // print data
      if($product_attribute_value == $keyOption){
        switch ($inputType) {
          case 'dropdown':
            $html_description = '';
            $html_description .= htmlspecialchars('<span class="item-name">'.$valueOption.'</span>');
            if($is_show_data['is_show_price']){
                $html_description .= htmlspecialchars($finalPrice);
              }
            $html .= '<option class="simple-item '.$current_selected.' '.$outOfStock.'" '.$current_selected.' value="'.$product->getProductUrl().'" data-description="'.$html_description.'">'.$valueOption.'</option>';
            break;
          
          case 'text':
            $html .= '<a href="'.$product->getProductUrl().'" title="'.$valueOption.'">';
              $html .= '<div class="simple-item '.$current_selected.' '.$outOfStock.'">';
                  // check image url
                  if($imageUrl){
                    $html .= '<img src="'.$imageUrl.'" alt="'.$product->getName().'" />';
                  }else{
                    $html .= '<span class="name">'.$valueOption.'</span>';
                  }
                  
                  if($is_show_data['is_show_price']){
                    $html .= '<span class="item-price">'.$finalPrice.'</span>';
                  }
              $html .= '</div>';
            $html .= '</a>';
            break;

          case 'visual':
            $html .= '<div class="simple-item '.$current_selected.' '.$outOfStock.'">';
              $html .= '<a href="'.$product->getProductUrl().'" title="'.$valueOption.'">';
                // check image url
                $html .= '<div class="item-img">';
                if($imageUrl){
                  $html .= '<img src="'.$imageUrl.'" alt="'.$product->getName().'" />';
                }else{
                  $html .= '<span class="name">'.$valueOption.'</span>';
                }
                $html .= '</div>';
                
                if($is_show_data['is_show_price']){
                  $html .= '<span class="item-price">'.$finalPrice.'</span>';
                }
              $html .= '</a>';
            $html .= '</div>';
            break;
        }
      }
    }

    return $html;
  }
}
