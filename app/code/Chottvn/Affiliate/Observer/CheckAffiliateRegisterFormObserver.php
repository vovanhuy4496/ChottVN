<?php
namespace Chottvn\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class CheckAffiliateRegisterFormObserver implements ObserverInterface
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var CaptchaStringResolver
     */
    protected $captchaStringResolver;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
 
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param CaptchaStringResolver $captchaStringResolver
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        PageFactory $resultPageFactory, 
        JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        CaptchaStringResolver $captchaStringResolver
    ) {
        $this->_helper = $helper;
        $this->_actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * Check CAPTCHA on Custom Form
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $formId = 'affiliate_create';
        $captcha = $this->_helper->getCaptcha($formId);

        // controller request
        $controller = $observer->getControllerAction();
        $attempt_log = $controller->getRequest()->getParam('phone_number') ? $controller->getRequest()->getParam('phone_number'):null;

        // check request phone number
        if ($this->customerSession->isLoggedIn()) {
            $phone_number = $this->customerSession->getCustomerData()->getCustomAttribute('phone_number')->getValue();
        } else {
            $phone_number = $controller->getRequest()->getParam('phone_number');
        }

        // log request from phone number
        $captcha->logAttempt($phone_number);

        // var_dump($captcha->isRequired($attempt_log));
        // echo '<pre>';print_r($controller->getRequest()->getParams());echo '</pre>';
        // exit;
        if ($captcha->isRequired($phone_number)) {
            /** @var \Magento\Framework\App\Action\Action $controller */
            // $controller = $observer->getControllerAction();
            $captcha_request = $controller->getRequest()->getParam('captcha') !== NULL ? $controller->getRequest()->getParam('captcha'):'';

            if($captcha_request != ''){
                if (!$captcha->isCorrect($this->captchaStringResolver->resolve($controller->getRequest(), $formId))) {
                    // error message
                    $data = [
                                'status'  => "error",
                                'message' => __('Incorrect CAPTCHA.')
                            ];

                    echo json_encode($data);die();
                }
            }else{
                    $this->messageManager->addError(__('Many requests, please try again.'));
                    $this->getDataPersistor()->set($formId, $controller->getRequest()->getPostValue());
                    $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                    // $this->redirect->redirect($controller->getResponse(), 'affiliate/register/index');

                    $data = [
                                'status'  => "error",
                                'error_code' => 'empty_captcha',
                                'message' => __('Empty CAPTCHA')
                            ];

                    echo json_encode($data);die();
            }
        }
    }

    /**
     * Get Data Persistor
     *
     * @return DataPersistorInterface
     */
    private function getDataPersistor()
    {
        if ($this->dataPersistor === null) {
            $this->dataPersistor = ObjectManager::getInstance()
                ->get(DataPersistorInterface::class);
        }

        return $this->dataPersistor;
    }
}