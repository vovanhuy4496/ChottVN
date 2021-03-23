<?php
namespace Chottvn\PriceQuote\Block\Request;

class Listrequest extends \Magento\Framework\View\Element\Template
{
	// Request
	protected $_request;
	protected $_response;
	public $_helperAccount;
	public $_customer;
    protected $session;
    protected $requestQuote;
    protected $requestItems;
    protected $requestItemsFactory;
    protected $requestFactory;
    protected $helperData;
	/**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Response\Http $response,
        \Chottvn\PriceQuote\Model\ResourceModel\Items\CollectionFactory $requestItemsFactory,
        \Chottvn\PriceQuote\Model\ResourceModel\Request\CollectionFactory $requestFactory,
        \Chottvn\PriceQuote\Model\Items $requestItems,
        \Chottvn\PriceQuote\Model\Request $requestQuote,
        \Chottvn\PriceQuote\Helper\Data $helperData,
        array $data = []
    ) {
    	$this->_request = $request;
        $this->_response = $response;
        $this->session = $customerSession;
        $this->requestQuote = $requestQuote;
        $this->requestItems = $requestItems;
        $this->requestItemsFactory = $requestItemsFactory;
        $this->requestFactory = $requestFactory;
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }
     /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $title = __('My Price Quote');
        $this->pageConfig->getTitle()->set($title);
        $group = $this->session->getCustomer()->getGroupId();
        $paramtitle = '';
        $array = '';
        if($group == 4){
           $paramtitle = 'affiliate';
           $array =  [
            'label' => __('Affiliate Program'),
            'title' => __('Affiliate Program'),
            'link' => $this->_storeManager->getStore()->getBaseUrl().'/affiliate'
           ];
        }else{
           $paramtitle = 'account';
           $array =  [
            'label' => __('Account Information Customer'),
            'title' => __('Account Information Customer'),
            'link' => $this->_storeManager->getStore()->getBaseUrl().'/customer/account'
           ];
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
            )->addCrumb(
                $paramtitle,$array
            )->addCrumb(
                '',
                [
                    'label' => $title,
                    'title' => $title
                ]
            );
        }

        return parent::_prepareLayout();
    }
    
    public function getRequestExistItems(){
        $customerId = $this->session->getCustomer()->getId();
        $collection = $this->requestFactory->create();
        $collection->getSelect()->join(
            ['items'=> $collection->getTable('chottvn_pricequote_request_item')], 'main_table.request_id = items.request_id')
        ->where('main_table.customer_id=?',$customerId)
        ->group('main_table.request_id')
        ->order('main_table.created_at DESC');
        // $this->writeLog($collection->getSelect()->__toString());
        return $collection;
    }
    public function getResult(){
        $allRequest = $this->getRequestExistItems()->getData();
        $col = $this->helperData->getCollection()->getData();
        $arr = [];
        foreach($allRequest as $key => $value){
            $i = 0;
            foreach($col as $item){
                if($value['request_id'] == $item['request_id']){
                    $arr[$value['request_id']][$i] = $item;
                    $i++;
                }
            }
        }
        return $arr;
    }

    public function getTotalAmount($request){
        $collection = $this->helperData->getCollectionRequest($request);
        $total = $collection->getData('grand_total');
        return $total;
    }
    public function getQty($request){
        $collection = $this->helperData->getCollectionItems($request);
        $collection->addFieldToFilter('main_table.cart_promo_option', array('null' => true))
        ->addFieldToFilter('main_table.product_type', ['neq' =>'configurable']);
        $collection->getSelect()
        ->reset(\Zend_Db_Select::COLUMNS)
        ->columns("SUM(main_table.qty) as total_qty")->group('main_table.request_id');
        $lastItem = $collection->getLastItem();
        $qty = $lastItem->getData('total_qty');
        return $qty;
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/list_request.log');
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