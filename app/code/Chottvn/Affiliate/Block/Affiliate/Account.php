<?php
namespace Chottvn\Affiliate\Block\Affiliate;

class Account extends \Magento\Framework\View\Element\Template
{
	// Request
	protected $_request;
	protected $_response;
	public $_helperAccount;
	public $_customer;

	/**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Customer\Model\Session $customer,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Response\Http $response,
        \Chottvn\Affiliate\Helper\Account $helperAccount,
        array $data = []
    ) {
        $this->_customer = $customer;
    	$this->_request = $request;
    	$this->_response = $response;
    	$this->_helperAccount = $helperAccount;
        parent::__construct($context, $data);
    }
     /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $title = __('Account Affiliate');
        $this->pageConfig->getTitle()->set($title);
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
                'affiliate',
                [
                    'label' => __('Affiliate Program'),
                    'title' => __('Affiliate Program'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl().'/affiliate'
                ]
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

    public function getProductBrandImageUrl($productBrandId){       
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productBrandFactory = $objectManager->create('Ves\Brand\Model\ResourceModel\Brand\CollectionFactory');
        try{
            $productBrand = $productBrandFactory->create()
                ->addFieldToFilter('brand_id', array('eq' => $productBrandId))
                ->getFirstItem();
            return $productBrand->getThumbnailUrl();
        }catch(\Exception $e){
            return "";
        }
       
    }
}