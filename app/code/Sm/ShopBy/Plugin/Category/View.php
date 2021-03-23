<?php
/**
 *
 * SM Shop By - Version 2.0.0
 * Copyright (c) 2017 YouTech Company. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: YouTech Company
 * Websites: http://www.magentech.com
 */
 
namespace Sm\ShopBy\Plugin\Category;


use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\Page;

class View
{
    protected $_resultJsonFactory;
    protected $_blockFactory;

    public function __construct(
        JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Element\BlockFactory $blockFactory
    ){
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_blockFactory = $blockFactory;
    }

    public function aroundExecute(\Magento\Catalog\Controller\Category\View $subject, \Closure $method){
        $response = $method();
        if($response instanceof Page){
            if($subject->getRequest()->getParam('ajax') == 1){
                $subject->getRequest()->getQuery()->set('ajax', null);
                $requestUri = $subject->getRequest()->getRequestUri();
                $requestUri = explode('?', $requestUri)[0];
                $params = $subject->getRequest()->getParams();
                if (!empty($params)){
                    $variable_url = array();
                    foreach ($params as $key => $value) {
                        switch ($key) {
                            case 'config':
                            case 'ajax':
                            case 'id':
                            case 'type_ajax':
                                break;
                            
                            default:
                                $variable_url[] = $key.'='.$value;
                                break;
                        }
                    }

                    if (!empty($variable_url)){
                        $variable_url = implode('&', $variable_url);
                        $requestUri .= '?'.$variable_url;
                    }
                }
                $requestUri = preg_replace('/(\?|&)ajax=1/', '', $requestUri);
                $subject->getRequest()->setRequestUri($requestUri);
                $productsBlockHtml = $response->getLayout()->getBlock('category.products');
				$productsBlockHtml = !empty($productsBlockHtml) ? $productsBlockHtml->toHtml() : '';
                $leftNavBlockHtml = $response->getLayout()->getBlock('catalog.leftnav');
                $leftNavBlockHtml = !empty($leftNavBlockHtml) ? $leftNavBlockHtml->toHtml() : '';

                // lam them
                $resultsBlockHtmlListingTabs = $response->getLayout()->getBlock('category.products.using.listingtabs');
                $resultsBlockHtmlListingTabs = !empty($resultsBlockHtmlListingTabs) ? $resultsBlockHtmlListingTabs->toHtml() : '';
                return $this->_resultJsonFactory->create()->setData(['success' => true, 'html' => [
                    'products_list' => $resultsBlockHtmlListingTabs,
                    'filters' => $leftNavBlockHtml
                ]]);
            }
        }
        return $response;
    }
}