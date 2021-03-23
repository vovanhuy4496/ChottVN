<?php
namespace Chottvn\ConfigurableProduct\Plugin;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as TypeConfigurable;
use Magento\Framework\Json\Decoder;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\View\LayoutFactory;

class MoreInformationConfigProduct
{
    /**
     * @var Decoder
     */
    private $jsonDecoder;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockState;

    /**
     * @var Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;


    public function __construct(
        Decoder $jsonDecoder,
        EncoderInterface $jsonEncoder,
        UrlInterface $urlBuilder,
        RequestInterface $request,
        StockStateInterface $stockState,
        LayoutFactory $layoutFactory
    ) {
        $this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->stockState = $stockState;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Get Allowed Products
     *
     * @return \Magento\Catalog\Model\Product[]
     */
    public function aroundGetAllowProducts(
        TypeConfigurable $subject,
        $result
    ) {
        if (!$subject->hasAllowProducts()) {
            $products = [];
            // $skipSaleableCheck = $this->catalogProduct->getSkipSaleableCheck();
            $skipSaleableCheck = true;
            $allProducts = $subject->getProduct()->getTypeInstance()->getUsedProducts($subject->getProduct(), null);
            foreach ($allProducts as $product) {
                if ($product->isSaleable() || $skipSaleableCheck) {
                    $products[] = $product;
                }
            }
            $subject->setAllowProducts($products);
        }

        $result = $subject->getData('allow_products');
        return $result;
    }

    /**
     * addingMoreInformationConfigProduct
     *
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     *
     * @return array
     */
    public function afterGetJsonConfig(
        TypeConfigurable $subject,
        $result
    ) {
        $result = $this->jsonDecoder->decode($result);

        // config product id
        $configProductId = $subject->getProduct()->getId();

        // initialize object
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        // get list simple products
        $parentProduct = $objectManager->create('Magento\Catalog\Model\Product')->load($configProductId);
        $simpleProducts = $parentProduct->getTypeInstance()->getChildrenIds($configProductId);
        // data
        $descriptions = array();
        foreach ($simpleProducts[0] as $product) {
            // get product data
            $productData = $objectManager->create('Magento\Catalog\Model\Product')->load($product);
            
            if($productData){
                // short description
                $descriptions[$product]['shortDescriptionHtml'] = $this->getShortDescriptionHtml($productData->getData('short_description'));
                // rules html
                $descriptions[$product]['rulesHtml'] = $this->getRulesHtml($productData);
                // default stock
                $defaultStockQty = $productData->getDefaultStockCustom() - $productData->sumQtyCurrentInQuoteItem();
                $currentDefaultStockQty = $productData->getDefaultStockCustom();
                $descriptions[$product]['defaultStock'] = $defaultStockQty;
                $descriptions[$product]['currentDefaultStockQty'] = $currentDefaultStockQty;
                // $descriptions[$product]['defaultStock'] = $this->getStockQty($productData);
                // model product
                $html_product_model = '';
                if($productData->getModel()){
                    $html_product_model .= '<div class="product-sku"><span class="product-sku-label">'.__('Model: ').' </span><span class="product-sku-name">'.$productData->getModel().'</span></div>';
                }
                $descriptions[$product]['modelProduct'] = $html_product_model;
                $descriptions[$product]['contactFormHtml'] = $this->getContactFormHtml($productData);
                $descriptions[$product]['getIsInStock'] = $productData->getIsInStock();
                $descriptions[$product]['getPrice'] = $productData->getPrice();
                $descriptions[$product]['isSaleable'] = $productData->isSaleable();
            }else{
                // short description
                $descriptions[$product]['shortDescriptionHtml'] = '';
                // rules html
                $descriptions[$product]['rulesHtml'] = '';
                // default stock
                $descriptions[$product]['defaultStock'] = '';
                // selected option
                $descriptions[$product]['selectedOptions'] = '';
                // model product
                $descriptions[$product]['modelProduct'] = '';
                // contact form html
                $descriptions[$product]['contactFormHtml'] = '';
                $descriptions[$product]['getIsInStock'] = '';
                $descriptions[$product]['getPrice'] = '';
            }
        }

        // input to json
        $result['descriptions'] = $descriptions;

        // selected option
        if($parentProduct){
            $result['selectedOptions'] = $this->getSelectedOptions($parentProduct);
            // selected option
            $result['requestParams'] = $this->request->getParams();
        }
        
        return $this->jsonEncoder->encode($result);
    }

    // get contact form data when price = 0 or out-of-stock
    public function getContactFormHtml($product){
        $html = '';

        if (!$product->isSaleable()){
            // out of stock
            $html .= $this->layoutFactory->create()
                        ->createBlock('Chottvn\StockAlert\Block\Stockalert')
                        ->setTemplate('Chottvn_StockAlert::stockalert.phtml')
                        ->setData([
                            'title' => __('Out of stock'),
                            'messager_success' => __('We will contact you when in stock'),
                            'title_fieldset' => __('Please leave your information, we will contact you when goods are available'),
                            'flag' => 'contactwhenstock'
                        ])
                        ->toHtml();
        }else{
            if($product->getPrice() == 0){
                // contact priceqoute
                $html .= $this->layoutFactory->create()
                            ->createBlock('Chottvn\StockAlert\Block\Stockalert')
                            ->setTemplate('Chottvn_StockAlert::stockalert.phtml')
                            ->setData([
                                'title' => __('Form Contact Price'),
                                'messager_success' =>  __('We will contact you for a quote soon'),
                                'title_fieldset' => __('Please leave your information, we will contact you for a quote'),
                                'flag' => 'contactquotes'
                            ])
                            ->toHtml();
            }
        }

        return $html;
    }

    // print data html for short description
    public function getShortDescriptionHtml($data){
        $html = '';

        // print short description
        $html .= '<div class="product attribute overview"><div class="value" style="max-height: none;"><p><strong class="strong-short-description">'.__('Salient Features').'</strong></p>';
            $html .= $data;
        $html .= '</div></div>';
        $html .= "<script type='text/javascript'>
                    require([ 'jquery', 'jquery/ui'], function($) {
                      $(document).ready(function(){
                        $('.product.attribute.overview .value').readmore({
                          speed: 75,
                          collapsedHeight: 132,
                          moreLink: '<div class=\"moreless\"><a href=\"#\">".__('Show more sort desctiption')." <i class=\"fa fa-caret-down\" aria-hidden=\"true\"></i></a></div>',
                          lessLink: '<div class=\"moreless\"><a href=\"#\">".__('Show less sort desctiption')." <i class=\"fa fa-caret-up\" aria-hidden=\"true\"></i></a></div>',
                          afterToggle: function(trigger, element, expanded) {
                            if(! expanded) { // The Close link was clicked
                              $('html, body').animate( { scrollTop: element.offset().top - 191 }, {duration: 100 } );
                            }
                          }
                        });
                      });
                    });
                    </script>";

        return $html;
    }
    // print data html for rules
    public function getRulesHtml($product){
        // initialize object
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $salesRuleChottvn = $objectManager->create('Chottvn\SalesRule\Model\SalesRuleRepository');
        $cartRules = array();
        $html = '';

        // get block rules
        $blockRules = $objectManager->create('Chottvn\Frontend\Block\Rules');

        // get catalog rules
        $catalogRulesNames = $blockRules->getCatalogRuleByProduct($product);
        // get cart rules
       
        $rule_type = $blockRules->getRuleType() ? explode(',', $blockRules->getRuleType()):array();
        // $simpleAction = ["ampromo_items"];
        $without_rule_type = $blockRules->getWithoutRuleType() ? explode(',', $blockRules->getWithoutRuleType()):array();
        // $cartRulesNames = $blockRules->getCartRuleByProduct($product,$rule_type,$without_rule_type,$simpleAction);
        // put data into array
        if(!empty($catalogRulesNames)){
            foreach ($catalogRulesNames as $catalogRulesName) {
                $cartRules[] = $catalogRulesName;
            }
        }
        // if(!empty($cartRulesNames)){
        //     foreach ($cartRulesNames as $cartRule) {
        //         $cartRules[] = $cartRule;
        //     }
        // }
        // huan:: gift
        $ruleAmpromoItems = $blockRules->getCartRuleIdsConditionSimpleActionByProduct($product);

        $productRepository = $objectManager->get('\Magento\Catalog\Model\Product');
        $helperData = $objectManager->get('Chottvn\PriceQuote\Helper\Data'); 
        // print rules html
        $arrayQty = array();
        $arrayIdRules = array();
        $countRules = 0;
        if (!empty($ruleAmpromoItems) || !empty($cartRules)) {
            $html .= '<div class="product-detail-rules">';
                $html .= '<div class="rules-title"><i class="fas fa-gift"></i>'.__("Hot promotions").'</div>';
                $html .= '<ul class="rules-list">';
                    // catalog
                    if (!empty($cartRules)) {
                        foreach ($cartRules as $rule) {
                            $html .= '<li><i class="fas fa-gift"></i><span>'.$rule.'</span></li>';
                        }
                    }
                    // product item
                    if (!empty($ruleAmpromoItems)) {
                        foreach($ruleAmpromoItems as $key => $value){
                            $simple_action = $value['simple_action'][0];
                            switch ($simple_action) {
                                case 'ampromo_items':
                                    if($value['type'][0] == 0 ){
                                        if(count($value['sku']) <= 1 ){
                                            $productData = $productRepository->loadByAttribute('sku',$value['sku'][0]);
                                            if (!empty($productData)){
                                                $productId = $productData->getId();
                                                $nameproduct = $productData->getName();
                                                $visibility = $helperData->hasProductUrl($productRepository->load($productId));
                                                $urlProduct = $productData->getProductUrl();
                                                $html .= '<li> <i class="fas fa-gift"></i><span>'.$value['name_rule'][0].'</span>';
                                                $html .= '<ul class="rules-skus-done"><li>
                                                    <i class="fas fa-check-circle"></i><span class="span-product">'.$nameproduct.'</span>';
                                                if($visibility){
                                                    $html .= '<a class="see-more-rules" target="_blank" href="'.$urlProduct.'" title="'.$nameproduct.'"><span class="span-load-more">'.__("Load more details").' &raquo;</span></a>';
                                                }
                                                $html .= '';
                                                $html .= '</li></ul></li>';
                                            }
                                        }else{
                                            $html .= '<li><i class="fas fa-gift"></i><span>'.$value['name_rule'][0].'</span>';
            
                                            $html .='<ul class="rules-skus-done">';
                                                foreach($value['sku'] as $item){
                                                    $productData = $productRepository->loadByAttribute('sku',$item);
                                                    if (!empty($productData)){
                                                        $productId = $productData->getId();
                                                        $nameproduct = $productData->getName();
                                                        $visibility = $helperData->hasProductUrl($productRepository->load($productId));
                                                        $urlProduct = $productData->getProductUrl();
                                                        $html .= '<li><i class="fas fa-check-circle"></i><span class="span-product">'.$nameproduct.'</span>';
                                                        if($visibility){
                                                            $html .= '<a class="see-more-rules" target="_blank" href="'.$urlProduct.'"title="<?= $nameproduct ?>"><span class="span-load-more">'.__('Load more details').' &raquo;</span></a>';
                                                        }
                                                    }
                                                }
                                            $html .='</ul></li>';
                                        }
                                    }else{
                                        if(count($value['sku']) <= 1 ){
                                            $productData = $productRepository->loadByAttribute('sku',$value['sku'][0]);
                                            if (!empty($productData)){
                                                $productId = $productData->getId();
                                                $nameproduct = $productData->getName();
                                                $visibility = $helperData->hasProductUrl($productRepository->load($productId));
                                                $urlProduct = $productData->getProductUrl();
                                                $html .= '<li> <i class="fas fa-gift"></i><span class="span-product">'.__("Gift: ").$nameproduct.'</span>';
                                                if($visibility){
                                                    $html .= '<a class="see-more-rules" target="_blank" href="'.$urlProduct.'" title="'.$nameproduct.'"><span class="span-load-more">'.__("Load more details").' &raquo;</span></a></li>';
                                                }
                                            }
                                        }else{
                                            $html .= '<li>';
                                            $i = 0;
                                            $html .= '<i class="fas fa-gift"></i><span>'.$value['name_rule'][0].'</span> <ul class="rules-skus-done-'. $key.' ul-padding-left">';
                                                array_push($arrayQty, $value['qty'][0]);
                                                array_push($arrayIdRules, $key);
                                                $checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session'); 
                                                $quote = $checkoutSession->getQuote();
                                                $quoteFactory = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
                                                $checked = '';
                                                $dataOptions = array();
                                                if($quote){
                                                    // get value de check radio
                                                    $quoteId = $quote->getId();
                                                    $productIdCurrent = $product->getId(); 
                                                    $quoteItem = $quoteFactory->create()->addFieldToFilter('quote_id', $quoteId)
                                                    ->addFieldToFilter('cart_promo_parent_id', $productIdCurrent)
                                                    ->addFieldToFilter('cart_promo_qty', $value['qty'][$i])
                                                    ->addFieldToFilter('cart_promo_ids',$key)
                                                    ->addFieldToFilter('cart_promo_option', 'ampromo_items');
                                                    $dataOptions = $quoteItem->getData();
                                                }
                                                foreach($value['sku'] as $item){
                                                    $productRepository = $objectManager->get('\Magento\Catalog\Model\Product');
                                                    $productData = $productRepository->loadByAttribute('sku',$item);
                                                    $nameproductChecked = '';
                                                    $checked = '';
                                                    $disabled = '';
                                                    if (!empty($productData)){
                                                        $visibility = $helperData->hasProductUrl($productData);

                                                        $productId = $productData->getId();
                                                        $nameproduct = $productData->getName();
                                                        $urlProduct = $productData->getProductUrl();
                                                       
                                                        $defaultStockCustom = $productData->getDefaultStockCustom();
                                                        $sumQtyCurrentInQuoteItem = $productData->sumQtyCurrentInQuoteItem();
                                                        $class = $productId.'-'.$key;
                                                        $requestQty = 0;
                                                        // $requestQty = (int)$value['qty'][$i];
                                                        $defaultStock = $defaultStockCustom - ($sumQtyCurrentInQuoteItem + $requestQty);
                                                        if ($defaultStock < 0 || $defaultStockCustom == 0 || ($defaultStockCustom - $sumQtyCurrentInQuoteItem == 0)) {
                                                            $disabled = 'disabled';
                                                        }

                                                        if (count($dataOptions) > 0) {
                                                            foreach ($dataOptions as $_key => $_value) {
                                                                if ($_key == $i) {
                                                                    if ($_value['sku'] == $sku) {
                                                                        $checked = 'checked';
                                                                        $nameproductChecked = $_value['name'];
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        $html .= '<li><label class="label-radio">';

                                                        if ($disabled == 'disabled'){
                                                            $html .= '<input type="radio" defaultStock-minicart="'.$defaultStock.'" class="'.$class.'-disabled"'. $disabled.' data-value="'.$nameproduct.'"/>'.$nameproduct;
                                                        }else{
                                                            $html .= '<input type="hidden" name="qty-gift-'.$key.'" class="cl-qty-gift '.$class.'-hidden"  value="'.$value['qty'][$i].'" />
                                                            <input type="radio" defaultStock-minicart="'.$defaultStock.'" name="sku-gift-'.$key.'" '.$checked.' class="cl-sku-radio-'.$key.' '.$class.'" data-value="'.$nameproduct.'" value= "'.$item.'" />'.$nameproduct;
                                                        }
                                                        
                                                        $html .='</label>';
                                                        if($visibility){
                                                            $html .= '<a class="see-more-rules" target="_blank" href="'.$urlProduct.'" title="'.$nameproduct.'"><span class="span-load-more">'.__("Load more details").' &raquo;</span></a>';
                                                        }
                                                        $html .= '</li>';
                                                    }else{
                                                        $html .= "<li><label class=\"label-radio\"><input type=\"radio\" disabled name=\"sku-gift-$key\" class=\"cl-sku-radio-$key\" />$item</label></li>";
                                                    }
                                                }
                                            $html .= '</ul>';
                                            $html .= '<ul class="box-noti-gift gift-'.$key.'" > <li>  </li> </ul>';
                                            $html .= "<script type='text/javascript'>
                                                            require(['jquery','mage/translate'], function($, \$t) {
                                                                $(document).ready( function() { 
                                                                    $('.rules-skus-done-$key .cl-sku-radio-$key').change(function(e) { 
                                                                        $(\".gift-$key li\").html(\"Bạn đang chọn quà tặng: \"+ $(this).attr('data-value'));
                                                                    });
                                                                    var checked = '$checked';
                                                                    var nameproductChecked  = '$nameproductChecked';
                                                                    
                                                                    if(checked == ''){
                                                                        $(jQuery.unique(
                                                                        $('.rules-skus-done-$key INPUT:radio')
                                                                                .map(function(i,e){ 
                                                                                    return $(e).attr('name') }
                                                                                ).get()
                                                                            )).each(function(i,e){
                                                                                $(\".gift-$key li\").html(\"Bạn đang chọn quà tặng: \"+ $('.rules-skus-done-$key INPUT:radio[name=\"'+e+'\"]:visible:first').attr('data-value'));
                                                                                $('.rules-skus-done-$key INPUT:radio[name=\"'+e+'\"]:visible:first')
                                                                                    .attr('checked','checked');
                                                                        });
                                                                    }else{
                                                                        $(\".gift-$key li\").html(\"Bạn đang chọn quà tặng: \"+ nameproductChecked);
                                                                        
                                                                    }
                                                                });
                                                                
                                                            });
                                                    </script>";
                                            $i++;
                                            $countRules++;
                                            $html .= '</li>';
                                        }   
                                    }
                                    break;
                                default:
                                    $promoUrl = $salesRuleChottvn->getPromoUrlBySalesRuleId($value['rule_id'][0]);
                                    $promoUrlHtml = '';
                                    if($promoUrl){
                                        $promoUrlHtml .= '<a class="promo-url" href="'.$promoUrl.'" target="_blank" title="'.__('Load more details').'">'.__('Load more details').' »</a>';
                                    }

                                    $html .= '<li><i class="fas fa-gift"></i><span>'.$value['name_rule'][0].$promoUrlHtml.'</span></li>';
                                    break;
                            }
                        }
                    }
                $html .= '</ul>';
            $html .= '</div>';
        }
        $html .= "<script type='text/javascript'>
                require(['jquery','mage/translate'], function($, \$t) {
                    $(document).ready( function() { 
                        var array = []
                        var checkboxes = document.querySelectorAll('input[type=radio]:checked')
                        for (var i = 0; i < checkboxes.length; i++) {
                            array.push(checkboxes[i].value)
                        }
                        var qtyCart = $('.control-qty-cart #qty').val();
                        var qty = $('#hidden-qty').val();
                        var rulesid = $('#hidden-rulesid').val();
                        $('#sku-gift-cart').val(array);	
                        $('#qty-gift-cart').val(qty);
                        $('#rulesid-gift-cart').val(rulesid);
                        $('input[type=radio]').click(function() {
                            var array = []
                            var checkboxes = document.querySelectorAll('input[type=radio]:checked')
                            for (var i = 0; i < checkboxes.length; i++) {
                                array.push(checkboxes[i].value)
                            }
                            $('#sku-gift-cart').val(array);
                        });
                    });
                });
        </script>";
        return $html;
    }

    /**
     * Retrieve stock qty whether product
     *
     * @param int $productId
     * @param int $websiteId
     * @return float
     */
    public function getStockQty($product)
    {
        return $this->stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
    }

    /**
     * Retrieve selected options whether product
     *
     * @param int $productId
     * @param int $websiteId
     * @return float
     */
    public function getSelectedOptions($product)
    {
        // initialize data
        $data = array();
        // get attributes by parent product
        $productAttributeOptions = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
        foreach ($productAttributeOptions as $productAttributeKey => $productAttributeValue) {
            if($attributeValue = $product->getResource()->getAttribute($productAttributeValue['attribute_code'])){
                $defaultValue = $attributeValue->getFrontend()->getAttribute()->getDefaultValue();
                $data[$productAttributeKey] = $defaultValue;
            }
        }

        return $data;
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Promo_QuoteItem.log');
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