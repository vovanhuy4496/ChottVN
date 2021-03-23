<?php
namespace Chottvn\SalesRule\Model;

class SalesRuleRepository
{

  protected $_salesRuleFactory;

  public function __construct(
    \Chottvn\SalesRule\Model\SalesRuleFactory $salesRuleFactory
    )
  {
    $this->_salesRuleFactory = $salesRuleFactory;
  }

  /**
     * {@inheritdoc}
   */
  public function getSalesRuleById($salesrule_id)
  {
    $salesRule = $this->_salesRuleFactory->create();
    $result = array();

    // collection
    $collection = $salesRule->getCollection()
                            ->addFieldToFilter('salesrule_id', ['eq' => $salesrule_id]);

    if($collection){
      $result = $collection->getFirstItem();
    }

    // return data
    return $result;
  }

  /**
     * {@inheritdoc}
   */
  public function isHideOnProductDetailPage($salesrule_id)
  {
    $salesRule = $this->_salesRuleFactory->create();
    $result = 0;

    // collection
    $collection = $salesRule->getCollection()
                            ->addFieldToFilter('salesrule_id', ['eq' => $salesrule_id]);

    if($collection){
      $result = $collection->getFirstItem()->getIsHideCatalogProductDetail();
    }

    // return data
    return $result;
  }

  /**
     * {@inheritdoc}
   */
  public function isHideOnCheckoutPage($salesrule_id)
  {
    $salesRule = $this->_salesRuleFactory->create();
    $result = 0;

    // collection
    $collection = $salesRule->getCollection()
                            ->addFieldToFilter('salesrule_id', ['eq' => $salesrule_id]);

    if($collection){
      $result = $collection->getFirstItem()->getIsHideCheckout();
    }

    // return data
    return $result;
  }

  /**
    * get Promo URL by Salesrule ID
   */
  public function getPromoUrlBySalesRuleId($salesrule_id)
  {
    $salesRule = $this->_salesRuleFactory->create();
    $result = 0;

    // collection
    $collection = $salesRule->getCollection()
                            ->addFieldToFilter('salesrule_id', ['eq' => $salesrule_id]);

    if($collection){
      $salesRule = $collection->getFirstItem();
      // get is_show_promo_url
      $isShowPromoUrl = $salesRule->getIsShowPromoUrl();

      if($isShowPromoUrl){
        $promoUrl = $salesRule->getPromoUrl();

        if($promoUrl){
          return $promoUrl;
        }else{
          return 1;
        }
      }
    }

    // return data
    return $result;
  }


}