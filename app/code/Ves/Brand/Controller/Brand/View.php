<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Brand
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Brand\Controller\Brand;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Ves\Brand\Model\Layer\Resolver;
use Magento\Framework\Controller\Result\JsonFactory;

class View extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Ves\Brand\Model\Brand
     */
    protected $_brandModel;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Ves\Brand\Helper\Data
     */
    protected $_brandHelper;

    protected $_jsonResultFactory;

    /**
     * @param Context                                             $context              [description]
     * @param \Magento\Store\Model\StoreManager                   $storeManager         [description]
     * @param \Magento\Framework\View\Result\PageFactory          $resultPageFactory    [description]
     * @param \Ves\Brand\Model\Brand                              $brandModel           [description]
     * @param \Magento\Framework\Registry                         $coreRegistry         [description]
     * @param Resolver                                            $layerResolver        [description]
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory [description]
     * @param \Ves\Brand\Helper\Data                              $brandHelper          [description]
     * @param \Ves\Brand\Helper\Data                              $brandHelper          [description]
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManager $storeManager,        
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ves\Brand\Model\Brand $brandModel,
        \Magento\Framework\Registry $coreRegistry,
        Resolver $layerResolver,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Ves\Brand\Helper\Data $brandHelper,
        JsonFactory $resultJsonFactory
        ) {
        parent::__construct($context);        
        $this->resultPageFactory = $resultPageFactory;
        $this->_brandModel = $brandModel;
        $this->layerResolver = $layerResolver;
        $this->_coreRegistry = $coreRegistry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_brandHelper = $brandHelper;  
        $this->_resultJsonFactory = $resultJsonFactory;      
    }

    public function _initBrand()
    {
        $brandId = (int)$this->getRequest()->getParam('brand_id', false);
        if (!$brandId) {
            return false;
        }
        try{
            $brand = $this->_brandModel->load($brandId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
        $this->_coreRegistry->register('current_brand', $brand);
        return $brand;
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function executeBK()
    {
        if(!$this->_brandHelper->getConfig('general_settings/enable')){
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $brand = $this->_initBrand();
        if ($brand) {
            $this->layerResolver->create('brand');
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $page = $this->resultPageFactory->create();
            // apply custom layout (page) template once the blocks are generated
            if ($brand->getPageLayout()) {
                $page->getConfig()->setPageLayout($brand->getPageLayout());
            }
            $page->addHandle(['type' => 'VES_BRAND_'.$brand->getId()]);
            if (($layoutUpdate = $brand->getLayoutUpdateXml()) && trim($layoutUpdate)!='') {
                $page->addUpdate($layoutUpdate);
            }

            /*$collectionSize = $brand->getProductCollection()->getSize();
            if($collectionSize){
                $page->addHandle(['type' => 'vesbrand_brand_layered']);
            }*/
            $page->getConfig()->addBodyClass('page-products')
            ->addBodyClass('brand-' . $brand->getUrlKey());
            return $page;
        }elseif (!$this->getResponse()->isRedirect()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }
    public function execute()
    {
        if(!$this->_brandHelper->getConfig('general_settings/enable')){
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $brand = $this->_initBrand();
        if ($brand) {
            $this->layerResolver->create('brand');

            if($this->getRequest()->getParam('ajax') == 1){
                $this->_objectManager->get('Magento\CatalogSearch\Helper\Data')->checkNotes();
                $_layout  = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
                $resultsBlockHtml = $this->resultPageFactory->create()->getLayout()->getBlock('brand.products.list');
                $resultsBlockHtml = !empty($resultsBlockHtml) ? $resultsBlockHtml->toHtml() : '';
                $leftNavBlockHtml = $_layout->getBlock('catalogsearch.leftnav');
                $leftNavBlockHtml = !empty($leftNavBlockHtml) ? $leftNavBlockHtml->toHtml() : '';
                $data = ['success' => true, 'html' => [
                    'products_list' => $resultsBlockHtml,
                    'filters' => $leftNavBlockHtml
                ]];              
                return $this->_resultJsonFactory->create()->setData($data);
            }else{
                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $page = $this->resultPageFactory->create();
                // apply custom layout (page) template once the blocks are generated
                if ($brand->getPageLayout()) {
                    $page->getConfig()->setPageLayout($brand->getPageLayout());
                }
                $page->addHandle(['type' => 'VES_BRAND_'.$brand->getId()]);
                if (($layoutUpdate = $brand->getLayoutUpdateXml()) && trim($layoutUpdate)!='') {
                    $page->addUpdate($layoutUpdate);
                }

                /*$collectionSize = $brand->getProductCollection()->getSize();
                if($collectionSize){
                    $page->addHandle(['type' => 'vesbrand_brand_layered']);
                }*/
                $page->getConfig()->addBodyClass('page-products')
                ->addBodyClass('brand-' . $brand->getUrlKey());
                return $page;
            }
            
        }elseif (!$this->getResponse()->isRedirect()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }
}