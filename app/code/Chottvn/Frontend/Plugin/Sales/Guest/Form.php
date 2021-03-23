<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Frontend\Plugin\Sales\Guest;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Helper\Guest as GuestHelper;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Form
 */
class Form
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CustomerSession|null
     */
    private $customerSession;

    /**
     * @var GuestHelper|null
     */
    private $guestHelper;

    protected $_objectManager;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CustomerSession|null $customerSession
     * @param GuestHelper|null $guestHelper
     */
    public function __construct(
        // Context $context,
        PageFactory $resultPageFactory,
        CustomerSession $customerSession = null,
        GuestHelper $guestHelper = null,
        \Magento\Framework\ObjectManagerInterface $objectManager

    ) {
        // parent::__construct($context);
        $this->_objectManager = $objectManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession ?: ObjectManager::getInstance()->get(CustomerSession::class);
        $this->guestHelper = $guestHelper ?: ObjectManager::getInstance()->get(GuestHelper::class);
    }

    /**
     * Order view form page
     *
     * @return Redirect|Page
     */
    public function aroundExecute(\Magento\Sales\Controller\Guest\Form $subject, \Closure $method)
    {
        if ($this->customerSession->isLoggedIn()) {
            // return $this->resultRedirectFactory->create()->setPath('/sales/order/history/');
            //echo 'vao';exit;
            $redirect = $this->_objectManager->get('\Magento\Framework\App\Response\Http');
            $redirect->setRedirect('/sales/order/history/');
        }else{
            return $method();
        }
    }
}
