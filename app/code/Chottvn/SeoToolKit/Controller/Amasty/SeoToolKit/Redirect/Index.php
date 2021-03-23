<?php
/**
 * @author Tuan Nguyen
 * @copyright Copyright (c) 2020 CTT (https://chotructuyen.co)
 * @package Chottvn_Sitemap
 */


namespace Chottvn\SeoToolKit\Controller\Amasty\SeoToolKit\Redirect;

use Magento\Framework\App\Action\Context;
use Magento\Search\Model\QueryFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Search\Helper\Data
     */
    private $searchHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * Index constructor.
     * @param Context $context
     * @param \Magento\Search\Helper\Data $searchHelper
     */
    public function __construct(
        \Magento\Search\Helper\Data $searchHelper,
        \Magento\Framework\UrlInterface $urlInterface,
        Context $context
    ) {
        $this->searchHelper = $searchHelper;
        $this->urlInterface = $urlInterface;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect
     * |\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        // $url = $this->searchHelper->getResultUrl($this->_request->getParam(QueryFactory::QUERY_VAR_NAME));

        $urlSearch = $this->urlInterface->getUrl('search');
        $query = $this->_request->getParam(QueryFactory::QUERY_VAR_NAME);
        $url = $urlSearch.'?q='.$query;

        // redirect
        $resultRedirect->setUrl($url);

        return $resultRedirect;
    }
}
