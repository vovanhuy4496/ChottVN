<?php
/**
 * ChottVN
 */
namespace Chottvn\Blog\Controller\Post;


class UpdateListtag extends \Magento\Framework\App\Action\Action
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


public function execute()
{
   $response =  $this->resultJsonFactory->create();
   $resultPage = $this->resultPageFactory->create();
    if ($this->getRequest()->isAjax()) 
    {
        $data = $this->getRequest()->getPostValue();
        $lastid = (int) $data['last_id'];
        $namecategory = $data['name_category'];
        $url = $data['url'];
        $type = $data['type'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('Chottvn\Blog\Helper\Data');
        $data = $helper->getPostCollectionTagCTT($type);
        if($type == 'mobile'){
            $block = $resultPage->getLayout()
            ->createBlock('Mageplaza\Blog\Block\Frontend')
            ->setTemplate('Mageplaza_Blog::post/listajaxtagmobile.phtml')
            ->setCollection($data)
            ->setUrlCate($url)
            ->setNameCategory($namecategory)
            ->toHtml();
            $response->setData($block);
        }else{
            $block = $resultPage->getLayout()
            ->createBlock('Mageplaza\Blog\Block\Frontend')
            ->setTemplate('Mageplaza_Blog::post/listajaxtag.phtml')
            ->setCollection($data)
            ->setUrlCate($url)
            ->setNameCategory($namecategory)
            ->toHtml();
            $response->setData($block);
        }
        return $response;
    }
}

    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/controller.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        switch ($type) {
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
