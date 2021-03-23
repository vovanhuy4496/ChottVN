<?php
namespace Sm\ListingTabs\Controller\Ajax;
 
 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
 
 
class Index extends Action
{
 
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
 
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;
 
 
    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, JsonFactory $resultJsonFactory)
    {
 
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
 
        parent::__construct($context);
    }
 
 
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();
        $block_data = (array)json_decode($this->getRequest()->getParam('ajax_listingtab_data'));
        $page_layout = $this->getRequest()->getParam('page_layout');
        
        $block = $resultPage->getLayout()
                ->createBlock('Sm\ListingTabs\Block\ListingTabs')
                ->setTemplate('Sm_ListingTabs::'.$page_layout.'.phtml')
                ->setData('ajax_listingtab_data',$block_data)
                ->toHtml();
 
        $result->setData(['output' => $block]);
        return $result;
    }
 
}