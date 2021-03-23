<?php
namespace Chottvn\Affiliate\Block\Affiliate\Withdrawal;

class Success extends \Magento\Framework\View\Element\Template
{
	// Request
	protected $_request;
	protected $_response;
	public $_helperAccount;
	public $_customer;
    /**
     * @var Session
     */
    protected $coreSession;
	/**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Customer\Model\Session $customer,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Chottvn\Affiliate\Helper\Account $helperAccount,
        array $data = []
    ) {
        $this->_customer = $customer;
        $this->_request = $request;
        $this->coreSession = $coreSession;
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
        $title = __('Withdrawal Success');
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
                'account affiliate',
                [
                    'label' => __('Account Affiliate'),
                    'title' => __('Account Affiliate'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl().'/customer/account/affiliate/'
                ]
            )
            ->addCrumb(
                '',
                [
                    'label' => $title,
                    'title' => $title
                ]
            );
        }
        return parent::_prepareLayout();
    }
    public function getNotification() {
        $request = $this->coreSession->getNotification();
        return ($request) ? $request : null;
    }
 
}