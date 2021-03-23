<?php
namespace Chottvn\Frontend\Block;

class FacebookPixel extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry = null;

    protected $request;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $sessionCheckout;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Checkout\Model\Session $sessionCheckout,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->request = $request;
        $this->coreSession = $coreSession;
        $this->sessionCheckout = $sessionCheckout;
        parent::__construct($context, $data);
    }

    /**
     * Function get data script for facebook pixel
     */
    public function getFacebookPixelScript(){
        // Facebook
        $is_show_viewcontent = $this->getData('is_show_viewcontent') != NULL ? $this->getData('is_show_viewcontent'):'1';
        $is_show_addtocart = $this->getData('is_show_addtocart') != NULL ? $this->getData('is_show_addtocart'):'1';
        $is_show_purchase = $this->getData('is_show_purchase') != NULL ? $this->getData('is_show_purchase'):'1';
        $is_show_initiatecheckout = $this->getData('is_show_initiatecheckout') != NULL ? $this->getData('is_show_initiatecheckout'):'1';

        // Google
        $is_show_gg_viewcontent = $this->getData('is_show_gg_viewcontent') != NULL ? $this->getData('is_show_gg_viewcontent'):'1';
        $is_show_gg_addtocart = $this->getData('is_show_gg_addtocart') != NULL ? $this->getData('is_show_gg_addtocart'):'1';
        $is_show_gg_purchase = $this->getData('is_show_gg_purchase') != NULL ? $this->getData('is_show_gg_purchase'):'1';
        $is_show_gg_initiatecheckout = $this->getData('is_show_gg_initiatecheckout') != NULL ? $this->getData('is_show_gg_initiatecheckout'):'1';

        // Google Remarketing
        $is_show_gg_remarketing_viewcontent = $this->getData('is_show_gg_remarketing_viewcontent') != NULL ? $this->getData('is_show_gg_remarketing_viewcontent'):'1';
        $is_show_gg_remarketing_addtocart = $this->getData('is_show_gg_remarketing_addtocart') != NULL ? $this->getData('is_show_gg_remarketing_addtocart'):'1';
        $is_show_gg_remarketing_purchase = $this->getData('is_show_gg_remarketing_purchase') != NULL ? $this->getData('is_show_gg_remarketing_purchase'):'1';
        $is_show_gg_remarketing_initiatecheckout = $this->getData('is_show_gg_remarketing_initiatecheckout') != NULL ? $this->getData('is_show_gg_remarketing_initiatecheckout'):'1';

        $data = '';
        $currentPage = implode('-', $this->getCurrentRoutePage());

        switch ($currentPage) {
            case 'catalog-product-view-catalog':
                # page product detail
                $data = $is_show_viewcontent ? $this->getViewContentFPSscript():'';
                $data .= $is_show_gg_remarketing_viewcontent ? $this->getViewContentRemarketingGGscript():'';
                $data .= $is_show_gg_viewcontent ? $this->getViewContentGGscript():'';
                break;
            
            case 'checkout-index-index-checkout':
                # page checkout page
                $data = $is_show_initiatecheckout ? $this->getInitiateCheckoutFPSscript():'';
                $data .= $is_show_gg_initiatecheckout ? $this->getInitiateCheckoutGGscript():'';
                break;

            case 'checkout-cart-index-checkout':
                # page cart
                $data = $is_show_addtocart ? $this->getAddToCartFPSscript():'';
                $data .= $is_show_gg_addtocart ? $this->getAddToCartGGscript():'';
                break;

            case 'checkout-onepage-success-checkout':
                # page product detail
                $data = $is_show_purchase ? $this->getPurchaseFPSscript():'';
                $data .= $is_show_gg_remarketing_purchase ? $this->getPurchaseRemarketingGGscript():'';
                $data .= $is_show_gg_purchase ? $this->getPurchaseGGscript():'';
                break;
        }


        return $data;
    }

    /**
     * Function get data standard script events - ViewContent
     * - Khi xem 1 sản phẩm SKU-1 thì thông tin lưu trữ
        fbq('track', 'ViewContent', {
          content_ids: ['SKU-1'],
          content_type: 'product'
        })

        - Khi xem tiếp sản phẩm SKU-2, SKU-3 thì thông tin lưu trữ
        fbq('track', 'ViewContent', {
          content_ids: ['SKU-1','SKU-2','SKU-3'],
          content_type: 'product'
        })
     * Update mới 2020-10-05 #1190
      - ViewContent 1 sản phẩm
        fbq('track', 'ViewContent', {
          content_ids: ['SKU-1'],
          content_type: 'product',
          value: 3600000,
          currency: 'VND',
          content_category: 'máy xịt rửa'
        })
      - AddToCart Khi click button thêm sp vào giỏ hàng
        fbq('track', 'AddToCart', {
          content_ids: ['SKU-1'],
          content_type: 'product',
          value: 3600000,
          currency: 'VND',
          content_category: 'máy xịt rửa'
        })
     */
    public function getViewContentFPSscript(){
        $data = '';
        $array_product = array();
        $send_to_gtag_id = $this->getData('send_to_gtag_id') != NULL ? $this->getData('send_to_gtag_id'):'AW-XXXXXXXX';

        // get current product detail
        if($this->registry->registry('current_product')){
            $product = $this->registry->registry('current_product');
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            // core session
            // $currentViewingProductDetail = !empty($this->coreSession->getViewingProductDetail()) ? $this->coreSession->getViewingProductDetail() : array();
            // if(!in_array($product->getSku(), $currentViewingProductDetail)){
            //     array_push($currentViewingProductDetail, $product->getSku());
            // }
            // save to session
            // $this->coreSession->setViewingProductDetail($currentViewingProductDetail);

            // window.addEventListener("load", function() {
            //   alert('Page is loaded');
            // });
            // add script product
            // $data = "<script>window.addEventListener('load', function() {fbq('track', 'ViewContent', { content_ids: ".json_encode($currentViewingProductDetail).", content_type: 'product' })});</script>";
            $cat_name = $this->getLastCateNameByListCates($product->getCategoryIds());
            
            $array_product[] = $product->getSku();
            $data = "<script>
                      window.addEventListener('load', function() {
                        fbq('track', 'ViewContent', {
                          content_ids: ".json_encode($array_product).",
                          content_type: 'product',
                          value: ".(int)$product->getFinalPrice().",
                          currency: 'VND',
                          content_category: '".$cat_name."'
                        })
                      });
                    </script>";

            // add script check click button addtocart
            $data .= '<script type="text/javascript">
                      require(["jquery", "jquery/ui"], function($){
                        $("body").on("click","#product-addtocart-button", function(event){
                          fbq("track", "AddToCart", {
                            content_ids: '.json_encode($array_product).',
                            content_type: "product",
                            value: '.(int)$product->getFinalPrice().',
                            currency: "VND",
                            content_category: "'.$cat_name.'"
                          });
                          dataLayer.push({
                            "event": "addToCart",
                            "ecommerce": {
                              "currencyCode": "VND",
                              "add": {
                              "actionField": {"step": 2},
                              "products": [{
                                "name": "'.addslashes($product->getNameShort()).'",
                                "id": "'.$product->getSku().'",
                                "price": "'.(int)$product->getFinalPrice().'",
                                "category": "'.$cat_name.'",
                                "quantity": 1
                                }]
                              }
                            }
                          });
                          gtag("event", "add_to_cart", {
                            "send_to": "'.$send_to_gtag_id.'",
                            "value": "'.(int)$product->getFinalPrice().'",
                            "items": [{
                              "id": "'.$product->getSku().'",
                              "google_business_vertical": "retail"}]
                          });
                        })
                      });
                      </script>';
            // dataLayer.push({
            //               "event": "add_to_cart",
            //               "value": "'.(int)$product->getFinalPrice().'",
            //               "items": [{
            //                 "id": "'.$product->getSku().'",
            //                 "google_business_vertical": "retail"}]
            //               });
            // $data .= "<script type='text/javascript'>
            //             require(['jquery'], function($){
            //               $('#product-addtocart-button').click(function() {
            //                 fbq('track', 'AddToCart', {
            //                   content_ids: ".json_encode($array_product).",
            //                   content_type: 'product',
            //                   value: ".(int)$product->getFinalPrice().",
            //                   currency: 'VND',
            //                   content_category: '".$cat_name."'
            //                 });

            //                 dataLayer.push({
            //                   'event': 'addToCart',
            //                   'ecommerce': {
            //                     'currencyCode': 'VND',
            //                     'add': {
            //                       'actionField': {'step': 2},
            //                       'products': [{
            //                         'name': '".$product->getNameShort()."',
            //                         'id': '".$product->getSku()."',
            //                         'price': '".(int)$product->getFinalPrice()."',
            //                         'category': '".$cat_name."',
            //                         'quantity': 1
            //                       }]
            //                     }
            //                   }
            //                 });
            //               });
            //             });
            //         </script>";
        }
        
        return $data;
    }

    public function getViewContentGGscript(){
      $data = '';
      $array_product = array();

      // get current product detail
      if($this->registry->registry('current_product')){
          $product = $this->registry->registry('current_product');
          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

          $cat_name = $this->getLastCateNameByListCates($product->getCategoryIds());

          // $array_product[] = $product->getSku();
          $data = "<script>
                    dataLayer.push({
                      'ecommerce': {
                        'currencyCode': 'VND',
                        'detail': {
                          'actionField': {'step': 1},
                          'products': [{
                            'name': '".$product->getNameShort()."',
                            'id': '".$product->getSku()."',
                            'price': '".(int)$product->getFinalPrice()."',
                            'category': '".$cat_name."'
                           }]
                         }
                       }
                    });
                  </script>";

          // add script check click button addtocart
          // $data .= "<script type='text/javascript'>
          //             require(['jquery'], function($){
          //               $( '#product-addtocart-button' ).on( 'click', {
          //                 dataLayer.push({
          //                   'event': 'addToCart',
          //                   'ecommerce': {
          //                     'currencyCode': 'VND',
          //                     'add': {
          //                       'actionField': {'step': 2},
          //                       'products': [{
          //                         'name': '".$product->getNameShort()."',
          //                         'id': '".$product->getSku()."',
          //                         'price': '".(int)$product->getFinalPrice()."',
          //                         'category': '".$cat_name."',
          //                         'quantity': 1
          //                       }]
          //                     }
          //                   }
          //                 });
          //               });
          //             });
          //           </script>";
      }
      
      return $data;
    }

    public function getViewContentRemarketingGGscript(){
      $data = '';
      $array_product = array();
      $send_to_gtag_id = $this->getData('send_to_gtag_id') != NULL ? $this->getData('send_to_gtag_id'):'AW-XXXXXXXX';

      // get current product detail
      if($this->registry->registry('current_product')){
          $product = $this->registry->registry('current_product');
          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

          $cat_name = $this->getLastCateNameByListCates($product->getCategoryIds());

          // $array_product[] = $product->getSku();
          // $data = "<script>
          //         dataLayer.push({
          //           'event': 'view_item',
          //           'value': '".(int)$product->getFinalPrice()."',
          //           'items': [{
          //               'id': '".$product->getSku()."',
          //               'google_business_vertical': 'retail'
          //             }]
          //           });
          //         </script>";
          $data = "<script>
                  gtag('event', 'view_item', {
                    'send_to': '".$send_to_gtag_id."',
                    'value': '".(int)$product->getFinalPrice()."',
                    'items': [{
                        'id': '".$product->getSku()."',
                        'google_business_vertical': 'retail'
                      }]
                    });
                  </script>";
      }

      return $data;
    }

    /**
     * Function get data script events
      - InitiateCheckout
        fbq('track', 'InitiateCheckout', {
          contents: [
            { id: 'SKU-1', quantity: 2 },
            { id: 'SKU-2', quantity: 1 },
          ],
         num_items: 3,
         content_type: 'product',
         value: 3600000,
         currency: 'VND'
        })
     */
    public function getInitiateCheckoutFPSscript(){
        $data = '';
        if($this->sessionCheckout->getQuote()->getAllVisibleItems()){
            $cartItems = $this->sessionCheckout->getQuote()->getAllVisibleItems();
            $contents = array();
            
            // get contents events
            $total_qty_sum = 0;
            foreach ($cartItems as $item) {
                $product_obj = (object) [
                                'id' => $item->getSku(),
                                'quantity' => $item->getQty(),
                                ];
                $contents[] = $product_obj;

                // count total sum
                $total_qty_sum += (int)$item->getQty();
            }

            // check not empty
            if($contents){
                $data = "<script>
                      window.addEventListener('load', function() {
                        fbq('track', 'InitiateCheckout', {
                          contents: ".json_encode($contents,JSON_UNESCAPED_UNICODE).",
                          num_items: ".(int)$total_qty_sum.",
                          content_type: 'product',
                          value: ".(int)$this->sessionCheckout->getQuote()->getGrandTotal().",
                          currency: 'VND'
                        })
                      });
                    </script>";
            }

        }
        return $data;
    }

    public function getInitiateCheckoutGGscript(){
      $data = '';
      if($this->sessionCheckout->getQuote()->getAllVisibleItems()){
          $cartItems = $this->sessionCheckout->getQuote()->getAllVisibleItems();
          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $contents = array();
          
          // get contents events
          $total_qty_sum = 0;
          foreach ($cartItems as $item) {
            $product = $objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface')->get($item->getSku());

            $cat_name = $this->getLastCateNameByListCates($product->getCategoryIds());

            $product_obj = (object) [
                            'name' => $product->getNameShort(),
                            'id' => $product->getSku(),
                            'price' => (int)$product->getFinalPrice(),
                            'category' => $cat_name,
                            'quantity' => $item->getQty()
                            ];
            $contents[] = $product_obj;

            // count total sum
            $total_qty_sum += (int)$item->getQty();
          }

          // check not empty
          if($contents){
              $data = "<script>
                        dataLayer.push({
                          'event': 'checkout',
                          'ecommerce': {
                            'currencyCode': 'VND',
                            'checkout': {
                              'actionField': {'step': 3},
                              'products': ".json_encode($contents,JSON_UNESCAPED_UNICODE)."
                            }
                          }
                        });
                      </script>";
          }

      }
      return $data;
    }

    /**
     * Function get data standard script events - AddToCart
     * - Khi click button thêm sp vào thêm vào giỏ hàng
        fbq('track', 'AddToCart', {
          content_ids: ['SKU-1'],
          content_type: 'product'
        })

        - Khi vào Giỏ hàng hoặc checkout
        fbq('track', 'AddToCart', {
          contents: [
            { id: 'SKU-1', quantity: 2 },
            { id: 'SKU-2', quantity: 1 },
          ],
          content_type: 'product',
          value: 3600000,
          currency: 'VND',
        })
     * Update mới 2020-10-05 #1190
      - AddToCart Khi click button thêm sp vào giỏ hàng
        fbq('track', 'AddToCart', {
          content_ids: ['SKU-1'],
          content_type: 'product',
          value: 3600000,
          currency: 'VND',
          content_category: 'máy xịt rửa'
        })
      - InitiateCheckout
        fbq('track', 'InitiateCheckout', {
          contents: [
            { id: 'SKU-1', quantity: 2 },
            { id: 'SKU-2', quantity: 1 },
          ],
         num_items: 3,
         content_type: 'product',
         value: 3600000,
         currency: "VND"          
        })
     */
    public function getAddToCartFPSscript(){
        $data = '';
        if($this->sessionCheckout->getQuote()->getAllVisibleItems()){
            $cartItems = $this->sessionCheckout->getQuote()->getAllVisibleItems();
            $contents = array();
            
            // get contents events
            foreach ($cartItems as $item) {
                $product_obj = (object) [
                                'id' => $item->getSku(),
                                'quantity' => $item->getQty(),
                                ];
                $contents[] = $product_obj;
            }

            // check not empty
            if($contents){
                $data = "<script>
                      window.addEventListener('load', function() {
                        fbq('track', 'AddToCart', {
                        contents: ".json_encode($contents,JSON_UNESCAPED_UNICODE).",
                        content_type: 'product',
                        value: ".(int)$this->sessionCheckout->getQuote()->getGrandTotal().",
                        currency: 'VND',
                        })
                      });
                    </script>";
            }

        }
        return $data;
    }

    public function getAddToCartGGscript(){
      $data = '';
      if($this->sessionCheckout->getQuote()->getAllVisibleItems()){
          $cartItems = $this->sessionCheckout->getQuote()->getAllVisibleItems();
          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $contents = array();
          
          // get contents events
          foreach ($cartItems as $item) {
            $product = $objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface')->get($item->getSku());

            $cat_name = $this->getLastCateNameByListCates($product->getCategoryIds());

            $product_obj = (object) [
                            'name' => $product->getNameShort(),
                            'id' => $product->getSku(),
                            'price' => (int)$product->getFinalPrice(),
                            'category' => $cat_name,
                            'quantity' => $item->getQty()
                            ];
            $contents[] = $product_obj;
          }

          // check not empty
          if($contents){
              $data = "<script>
                        dataLayer.push({
                          'event': 'addToCart',
                          'ecommerce': {
                            'currencyCode': 'VND',
                            'add': {
                              'actionField': {'step': 2},
                              'products': ".json_encode($contents,JSON_UNESCAPED_UNICODE)."
                            }
                          }
                        });
                      </script>";
          }

      }
      return $data;
    }

    /**
     * Function get data standard script events - Purchase
     * Khi mua hàng thành công, checkout success
        fbq('track', 'Purchase', {
          contents: [
            { id: 'SKU-1', quantity: 2 },
            { id: 'SKU-2', quantity: 1 },
          ],
          content_type: 'product',
          value: 3600000,
          currency: 'VND',
        })
     */
    public function getPurchaseFPSscript(){
        $data = '';
        if($this->sessionCheckout->getLastOrderId()){
            $lastorderId = $this->sessionCheckout->getLastOrderId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            // get info
            $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($lastorderId);
            $contents = array();

            // get order items
            $orderItems = $order->getAllItems();
            foreach ($orderItems as $item) {
                $product_obj = (object) [
                                'id' => $item->getSku(),
                                'quantity' => (int)$item->getQtyOrdered(),
                                ];
                $contents[] = $product_obj;
            }

            $total = $order->getGrandTotal();

            // check not empty
            if($contents){
                $data = "<script>
                      window.addEventListener('load', function() {
                        fbq('track', 'Purchase', {
                        contents: ".json_encode($contents,JSON_UNESCAPED_UNICODE).",
                        content_type: 'product',
                        value: ".(int)$total.",
                        currency: 'VND',
                        })
                      });
                    </script>";
            }
        }
        return $data;
    }

    public function getPurchaseGGscript(){
        $data = '';
        if($this->sessionCheckout->getLastOrderId()){
            $lastorderId = $this->sessionCheckout->getLastOrderId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            // get info
            $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($lastorderId);
            $contents = array();

            // get order items
            $orderItems = $order->getAllItems();
            foreach ($orderItems as $item) {
              $product = $objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface')->get($item->getSku());

              $cat_name = $this->getLastCateNameByListCates($product->getCategoryIds());

              $product_obj = (object) [
                              'name' => $product->getNameShort(),
                              'id' => $product->getSku(),
                              'price' => (int)$product->getFinalPrice(),
                              'category' => $cat_name,
                              'quantity' => (int)$item->getQtyOrdered()
                              ];
              $contents[] = $product_obj;
            }

            $total = $order->getGrandTotal();

            // check not empty
            if($contents){
                $data = "<script>
                          dataLayer.push({
                            'ecommerce': {
                              'currencyCode': 'VND',
                              'purchase': {
                                'actionField': {
                                  'step': 4,
                                  'id': '".(int)$order->getIncrementId()."',
                                  'affiliation': 'chotructuyen.co',
                                  'revenue': '".(int)$total."'
                                },
                                'products': ".json_encode($contents,JSON_UNESCAPED_UNICODE)."
                              }
                            }
                          });
                        </script>";
            }
        }
        return $data;
    }

    public function getPurchaseRemarketingGGscript(){
        $data = '';
        $send_to_gtag_id = $this->getData('send_to_gtag_id') != NULL ? $this->getData('send_to_gtag_id'):'AW-XXXXXXXX';

        if($this->sessionCheckout->getLastOrderId()){
            $lastorderId = $this->sessionCheckout->getLastOrderId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            // get info
            $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($lastorderId);
            $contents = array();

            // get order items
            $orderItems = $order->getAllItems();
            foreach ($orderItems as $item) {
              $product = $objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface')->get($item->getSku());

              $cat_name = $this->getLastCateNameByListCates($product->getCategoryIds());

              $product_obj = (object) [
                              'id' => $product->getSku(),
                              'google_business_vertical' => 'retail'
                              ];
              $contents[] = $product_obj;
            }

            $total = $order->getGrandTotal();

            // check not empty
            if($contents){
                // $data = "<script>
                //           dataLayer = [];
                //           dataLayer.push({
                //             'event': 'purchase',
                //             'value': '".(int)$total."',
                //             'transactionId': '".(int)$order->getIncrementId()."',
                //             'items': ".json_encode($contents,JSON_UNESCAPED_UNICODE)."
                //             });
                //           </script>";
                $data = "<script>
                          gtag('event', 'purchase', {
                            'send_to': '".$send_to_gtag_id."',
                            'value': '".(int)$total."',
                            'transactionId': '".(int)$order->getIncrementId()."',
                            'items': ".json_encode($contents,JSON_UNESCAPED_UNICODE)."
                            });
                          </script>";
                          
            }
        }
        return $data;
    }

    /**
     * Get last category name by list cate ids in product object
     */
    public function getLastCateNameByListCates($cateids){
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $cat_name = 'chotructuyen.co';
      if($cateids){
        $categories = $cateids;
        $last_category_id = $categories[count($categories)-1];
        $cat = $objectManager->create('Magento\Catalog\Model\Category')->load($last_category_id);
        $cat_name = $cat->getName();
      }

      return $cat_name;
    }

    /**
     * Product detail (Module: catalog, Controller: product, Action: view, Route: catalog)
     * Checkout Page (Module: checkout, Controller: index, Action: index, Route: checkout)
     * Cart (Module: checkout, Controller: cart, Action: index, Route: checkout)
     * Checkout Success Page (Module: checkout, Controller: onepage, Action: success, Route: checkout)
     */
    public function getCurrentRoutePage(){
        $result = array();
        $moduleName = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        $action     = $this->request->getActionName();
        $route      = $this->request->getRouteName();

        $result['module'] = $moduleName;
        $result['controller'] = $controller;
        $result['action'] = $action;
        $result['route'] = $route;

        return $result;
    }
}
