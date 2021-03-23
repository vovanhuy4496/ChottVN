<?php
namespace Chottvn\CustomCatalogSearch\Block;

class Search extends \Magento\Framework\View\Element\Template
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
        $title = $this->getSearchQueryText();
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
                'search',
                ['label' => $title, 'title' => $title]
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Get search query text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSearchQueryText()
    {
        return __("Search results for: '%1'", $this->getRequestQuery());
    }


    /**
     * Get request seach query
     *
     * @return String
     */
    public function getRequestQuery(){
    	// get request and trim
    	$query = $this->_request->getParam('q') ? trim($this->_request->getParam('q')):'';

    	// redirect homepage if query empty
    	if($query == ''){
    		$this->_response->setRedirect('/');
    	}

    	return $query;
    }
}