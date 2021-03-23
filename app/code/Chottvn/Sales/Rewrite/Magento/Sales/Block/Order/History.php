<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\Sales\Rewrite\Magento\Sales\Block\Order;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;

class History extends \Magento\Sales\Block\Order\History
{
    /**
     * @var string
     */
// protected $_template = 'order/history.phtml';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;

    /**
     * @var CollectionFactoryInterface
     */
    private $orderCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Orders'));
    }

    /**
     * @return CollectionFactoryInterface
     *
     * @deprecated 100.1.1
     */
    private function getOrderCollectionFactory()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->orderCollectionFactory;
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerRepository = $objectManager->create("Magento\Customer\Api\CustomerRepositoryInterface");
        
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }

        $phone_number = '';
        $phoneNumberAttr = $customerRepository->getById($customerId)->getCustomAttribute('phone_number');

        if(!empty($phoneNumberAttr)) {
            $phone_number = $phoneNumberAttr->getValue();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('sales_order');
            $customerObj = $objectManager->create('Magento\Customer\Model\ResourceModel\Customer\Collection');
            $collection = $customerObj->addAttributeToSelect('*')
                            ->addAttributeToFilter('phone_number', $phone_number)
                            ->load();
			$customerModel = $collection->getLastItem();
            
        }
        if (!$this->orders) {
            $orders = $this->getOrderCollectionFactory()
                            ->create()
                            ->addFieldToSelect(
                                new \Zend_Db_Expr("main_table.*, CASE main_table.status 
                                    WHEN 'complete' THEN 0 
                                    WHEN 'finished' THEN 0 
                                    WHEN 'returned' THEN 0 
                                    WHEN 'returned_and_finished' THEN 0 
                                    WHEN 'replaced' THEN 0 
                                    WHEN 'replaced_and_finished' THEN 0 
                                    WHEN 'canceled' THEN 0 
                                    ELSE 10 END AS status_order")
                            );

            // case affiliate account => filter by affiliate_account_id
            // case member store => filter by chott_customer_phone_number
            switch ($customerModel->getGroupId()) {
                case 4:
                    $orders->addFieldToFilter('affiliate_account_id',['eq' => $customerId]);
                    break;
                
                default:
                    $orders->addFieldToFilter('chott_customer_phone_number',['eq' => $phone_number]);
                    break;
            }

            // Query data
            $query = $this->getRequest()->getParam('q') ? trim($this->getRequest()->getParam('q')):'';

            // join with sales_order_address, get info shipping address
            $orders->getSelect()->joinLeft(
                array('soa' => 'sales_order_address'), 
                "main_table.entity_id = soa.parent_id AND soa.address_type='shipping'",
                array('soa_firstname' => 'soa.firstname')
            );

            // join with sales_order_item, get info list product
            $orders->getSelect()->joinLeft(
                array('soi' => 'sales_order_item'), 
                'main_table.entity_id = soi.order_id',
                array('soi_product_name' => 'soi.name')
            );

            if($query){
                // filter by product name
                // filter by customer name in shipping address
                $orders->addFieldToFilter(
                    [
                        'soi.name',
                        'soa.firstname',
                        'main_table.increment_id'
                    ],
                    [
                        ['like' => '%'.$query.'%'],
                        ['like' => '%'.$query.'%'],
                        ['like' => '%'.$query.'%']
                    ]
                );
            }
            $fromDate = $this->getRequest()->getParam('from') ? trim($this->getRequest()->getParam('from')).' 00:00:00':'';
            $toDate = $this->getRequest()->getParam('to') ? trim($this->getRequest()->getParam('to')).' 23:59:59':'';
            if($fromDate || $toDate){
                if($fromDate){
                    $fromDate = date("Y-m-d H:i:s", strtotime($fromDate));
                    $orders->addFieldToFilter('main_table.created_at', ["from" => $fromDate]);
                }
                if($toDate){
                    $toDate = date("Y-m-d H:i:s", strtotime($toDate));
                    $orders->addFieldToFilter(
                        ['main_table.created_at','main_table.created_at'],
                        [
                            ['to' => $toDate],
                            ['null' => true]
                        ]
                    );
                }
            }

            $status = $this->getRequest()->getParam('status') ? trim($this->getRequest()->getParam('status')):'';

            // don hang thanh cong
            if($status == 'completed'){
                $orders->getSelect()->where(
                    new \Zend_Db_Expr(
                        "main_table.affiliate_account_id IS NOT NULL 
                        AND main_table.status IN ('finished')
                        OR (main_table.status IN ('complete') AND ABS(DATEDIFF(main_table.updated_at, NOW())) > COALESCE(soi.return_period, 30))"
                    )
                );
            }else{
                if($status != 'all' && $status != ''){
                    $status = explode(',', $status);
                    $orders->addFieldToFilter(
                        'status',
                        ['in' => $status]
                    );
                }else{
                    $orders->addFieldToFilter(
                        'status',
                        ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
                    );
                }
            }
            
            // group by
            $orders->getSelect()->group('main_table.entity_id');

            // kind active or passive
            $kind_order = $this->getRequest()->getParam('kind') ? trim($this->getRequest()->getParam('kind')):'';
            if($kind_order){
                $rewardRule = $objectManager->get('Chottvn\Affiliate\Helper\RewardRule');
                $dateRange['start_date'] = $fromDate;
                $dateRange['end_date'] = $toDate;
                $activeOrderIds = $rewardRule->getStatisticAffiliateOrderTypeActiveOrderIds($customerId,$dateRange);

                switch ($kind_order) {
                    case 'active':
                        $orders->addFieldToFilter('entity_id', ['in' => $activeOrderIds]);
                        break;
                    
                    case 'passive':
                        $orders->addFieldToFilter('entity_id', ['nin' => $activeOrderIds]);
                        break;
                }
            }

            // set order
            $orders->getSelect()->order('status_order DESC');
            $orders->getSelect()->order('main_table.created_at DESC');

            // print_r($orders->getSelect()->__toString());exit;
            // print_r($activeOrderIds);exit;
            $this->orders = $orders;
        }
       
        return $this->orders;
    }

    

    public function initTotalHistory($order){
        $totals = [];
        $totals['original_total'] = new \Magento\Framework\DataObject(
            [
                'code' => 'original_total',
                'value' => $order->getOriginalTotal(),
                'label' => __('Original Total')
            ]
        );
        $totals['savings_amount'] = new \Magento\Framework\DataObject(
            [
                'code' => 'savings_amount',
                'value' => $order->getSavingsAmount() > 0 ? $order->getSavingsAmount() : 0,
                'label' => __('Savings Amount'),
            ]
        );
        /**
         * Add shipping
         */
        if (!$order->getIsVirtual() && ((double)$order->getShippingAmount() || $order->getShippingDescription())) {
            switch ($order->getShippingMethod()) {
                case 'flatrate_flatrate':
                    $shipping_amount = __("Shipping Contact Later");
                    break;

                case 'storepickupshipping_storepickupshipping':
                    $shipping_amount = __("Free Shipping");
                    break;
                
                default:
                    $shipping_amount = $order->getShippingAmount();
                    break;
            }
       
            $totals['shipping'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'shipping',
                    'field' => 'shipping_amount',
                    'value' => $shipping_amount,
                    'label' => __('Shipping & Handling'),
                ]
            );
        }
        $feeShipping = $order->getFeeShippingContact();
        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        if($feeShipping == 1){
            $shipping_amount = __('Price Contact');
            $grandtotal = __('Grand Total(temp)');
            $grandtotal_amount = $order->getGrandTotal();
        }else{
            $shipping_amount = $order->getShippingAmount();
            $grandtotal = __('Grand Total');
            $grandtotal_amount = $order->getGrandTotal();
        }
        $quoteid = $order->getQuoteId();
        $quote = $object_manager->create('Magento\Quote\Model\Quote')->load($quoteid);
        $freeshipping = $quote->getFlagShipping();
        if($freeshipping === 'freeshipping' && !is_null($freeshipping) && $freeshipping != ""){
            $shipping_amount = __('Free Shipping');
            $grandtotal = __('Grand Total');
            $grandtotal_amount = $order->getGrandTotal();
        }
        if($freeshipping === 'over'){
            $shipping_amount = __('Price Contact');
            $grandtotal = __('Grand Total(temp)');
            $grandtotal_amount = $order->getGrandTotal();
        }
        // $this->writeLog($shipping_amount);
        $totals['shipping'] = new \Magento\Framework\DataObject(
            [
                'code' => 'shipping',
                'field' => 'shipping_amount',
                'value' => $shipping_amount,
                'label' => __('Shipping & Handling'),
            ]
        );

        $totals['grand_total'] = new \Magento\Framework\DataObject(
            [
                'code' => 'grand_total',
                'field' => 'grand_total',
                'strong' => true,
                'value' => $grandtotal_amount,
                'label' => $grandtotal,
            ]
        );
        return $totals;
    }
     /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create("Magento\Customer\Model\Session");
        $groupId = 1;
        if($customerSession->isLoggedIn()){
            $groupId = $customerSession->getCustomer()->getGroupId();
        }
         // add Home breadcrumb
         $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
         if ($breadcrumbs) {
             $breadcrumbs->addCrumb(
                 'home',
                 [
                     'label' => __('Home'),
                     'title' => __('Go to Home Page'),
                     'link' => $this->_storeManager->getStore()->getBaseUrl()
                 ]
                 );
         }
        if($groupId == 4){
            $breadcrumbs->addCrumb(
                'affiliate',
                [
                    'label' => __('Account Information Affiliate'),
                    'title' => __('Account Information Affiliate'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl().'customer/account'
                ]
            )
            ->addCrumb(
                'affiliateorder',
                [
                    'label' => __('My Orders'),
                    'title' => __('My Orders')
                ]
            );
            
        }else{
            $breadcrumbs->addCrumb(
                'account',
                [
                    'label' => __('Account Information Customer'),
                    'title' => __('Account Information Customer'),
                    'link' =>  $this->_storeManager->getStore()->getBaseUrl().'customer/account'
                ]
            )->addCrumb(
                'accountorder',
                [
                    'label' => __('My Orders'),
                    'title' => __('My Orders')
                ]
            );
        }
        $this->getOrders()->load();
        return parent::_prepareLayout();
    }
    /**
     * @return string
     */
    public function getPagerHtml()
    {
        // return $this->getChildHtml('pager');
        return false;
    }
    
    /**
     * @param object $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getTrackUrl($order)
    {
        return $this->getUrl('sales/order/track', ['order_id' => $order->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getReorderUrl($order)
    {
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
        /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info"){
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/history.log');
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
