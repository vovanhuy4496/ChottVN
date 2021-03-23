<?php
/**
* ChottVN
*/
namespace Chottvn\Brand\Controller\Brand;

class Ajax extends \Magento\Framework\App\Action\Action
{
  /**
  * @var PageFactory
  */
  protected $resultPageFactory;
  protected $resultJsonFactory;

  /**
  * @param Context $context
  * @param \RB\Vendor\Model\Design $design
  * @param PageFactory $resultPageFactory
  */
  public function __construct(
    \Magento\Framework\App\Action\Context $context, 
    \Magento\Framework\View\Result\PageFactory $resultPageFactory,
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
  ) {        
    $this->resultPageFactory = $resultPageFactory;
    $this->resultJsonFactory = $resultJsonFactory;
    $this->_resultLayoutFactory = $resultLayoutFactory;

    parent::__construct($context);
  }

  public function execute(){
    $response =  $this->resultJsonFactory->create();
    $resultPage = $this->resultPageFactory->create();
    if ($this->getRequest()->isAjax()) {
      $data = $this->getRequest()->getPostValue();
      $page = $data['page'];
      $block = $resultPage->getLayout()
      ->createBlock('Chottvn\Brand\Block\Ves\Brand\Block\BrandList')
      ->setTemplate('Ves_Brand::brand/ajax.phtml')
      ->setPage($page)
      ->toHtml();
      $response->setData($block);
    }

    return $response;
  }
}
