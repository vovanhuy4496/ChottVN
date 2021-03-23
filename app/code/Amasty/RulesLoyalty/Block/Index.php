<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_RulesLoyalty
 */

namespace Amasty\RulesLoyalty\Block;
use Amasty\RulesLoyalty\Helper\Calculator;
use Magento\Customer\Model\Session as CustomerSession;
/**
*
*/
class Index extends \Magento\Framework\View\Element\Template
{
    protected $_values = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

	/**
	 * @var Calculator
	 */
	private $calculator;

	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Amasty\RulesLoyalty\Helper\Calculator $calculator,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $context->getStoreManager();
        $this->_objectManager = $objectManager;
		parent::__construct($context, $data);
		$this->calculator = $calculator;
	}

	public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Loyalty Program'));
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
            );
        }else{
            $breadcrumbs->addCrumb(
                'account',
                [
                    'label' => __('Account Information Customer'),
                    'title' => __('Account Information Customer'),
                    'link' =>  $this->_storeManager->getStore()->getBaseUrl().'customer/account'
                ]
                );
        }
        $breadcrumbs->addCrumb(
            'loyalty',
            [
                'label' => __('Loyalty Program'),
                'title' => __('Loyalty Program')
            ]
        );
        return parent::_prepareLayout();
	}

	protected function _getCurrentStore()
	{
		return $this->storeManager->getStore()->getId();
	}

	protected function _getValues($key)
	{
		if (is_null($this->_values)) {
			$values = array();
			$store = $this->storeManager->getStore();
            $calc = $this->calculator;
			// membership
			$values['membership_days'] = $calc->getMembership();
			// all period
			$allPeriod = $calc->getAllPeriodTotal();
			$values['all_of_placed_orders']    = $allPeriod['of_placed_orders'];
			$values['all_total_orders_amount'] = $calc->convertPrice($allPeriod['total_orders_amount'], $store, true);
			$values['all_average_order_value'] = $calc->convertPrice($allPeriod['average_order_value'], $store, true);
			// this month
			$thisMonth = $calc->getThisMonthTotal();
			$values['this_of_placed_orders']    = $thisMonth['of_placed_orders'];
			$values['this_total_orders_amount'] = $calc->convertPrice($thisMonth['total_orders_amount'], $store, true);
			$values['this_average_order_value'] = $calc->convertPrice($thisMonth['average_order_value'], $store, true);
			// last month
			$lastMonth = $calc->getLastMonthTotal();
			$values['last_of_placed_orders']    = $lastMonth['of_placed_orders'];
			$values['last_total_orders_amount'] = $calc->convertPrice($lastMonth['total_orders_amount'], $store, true);
			$values['last_average_order_value'] = $calc->convertPrice($lastMonth['average_order_value'], $store, true);

			$this->_values = $values;
		}

		return isset($this->_values[$key]) ? $this->_values[$key] : 0;
	}

	public function getMembership()
	{
		return $this->_getValues('membership_days');
	}

	public function getOrdersCount()
	{
		return $this->_getValues('all_of_placed_orders');
	}

	public function getOrdersAve()
	{
		return $this->_getValues('all_average_order_value');
	}

	public function getOrdersAmount()
	{
		return $this->_getValues('all_total_orders_amount');
	}

	public function getThisMonthCount()
	{
		return $this->_getValues('this_of_placed_orders');
	}

	public function getThisMonthAve()
	{
		return $this->_getValues('this_average_order_value');
	}

	public function getThisMonthAmount()
	{
		return $this->_getValues('this_total_orders_amount');
	}

	public function getLastMonthCount()
	{
		return $this->_getValues('last_of_placed_orders');
	}

	public function getLastMonthAve()
	{
		return $this->_getValues('last_average_order_value');
	}

	public function getLastMonthAmount()
	{
		return $this->_getValues('last_total_orders_amount');
	}
}