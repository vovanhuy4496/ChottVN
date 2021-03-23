<?php
namespace Chottvn\Affiliate\Block;

class Affiliate extends \Magento\Framework\View\Element\Template
{
	// Request
	protected $_request;
	protected $_response;

	/**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Response\Http $response,
        array $data = []
    ) {
    	$this->_request = $request;
    	$this->_response = $response;
        parent::__construct($context, $data);
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $title = __('Affiliate Program');
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
                ['label' => $title, 'title' => $title]
            );
        }

        return parent::_prepareLayout();
    }

}