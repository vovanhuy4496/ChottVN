<?php

/**
 * Copyright (c) 2019 2020 ChottVN
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Chottvn\SigninPhoneNumber\Controller\PhoneVerification;

use Chottvn\SigninPhoneNumber\Api\PhoneVerificationRepositoryInterface as PhoneVerificationRepository;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Model\Session;
use Magento\Framework\UrlFactory;

class Verify extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * @var PhoneVerificationRepository
     */
	protected $phoneVerificationRepository;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        PhoneVerificationRepository $phoneVerificationRepository,
        Validator $formKeyValidator = null,
        UrlFactory $urlFactory,
        Session $customerSession
	) {
        $this->session = $customerSession;
        $this->urlModel = $urlFactory->create();
        $this->phoneVerificationRepository = $phoneVerificationRepository;
        $this->formKeyValidator = $formKeyValidator ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Validator::class);
		return parent::__construct($context);
	}

    /**
     * Verify phone number
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute() {
		$resultRedirect = $this->resultRedirectFactory->create();
		$url = $this->urlModel->getUrl('*/*/display', ['_secure' => true]);

        if (!$this->getRequest()->isPost()
            || !$this->formKeyValidator->validate($this->getRequest())
        ){
            return $resultRedirect->setUrl($this->_redirect->error($url));
		}

		$data = [
			"authCode" => $this->getRequest()->getParam('auth_code'),
			"phoneNumber" => $this->getRequest()->getParam('phone_number')
		];

		$result = json_decode($this->phoneVerificationRepository->verifyPhoneByNumber(json_encode($data)));

		if(!$result->status) {
            $this->messageManager->addError($result->message);
            return $resultRedirect->setUrl($this->_redirect->error($url));
		}

		$this->messageManager->addSuccess($result->message);
		return $resultRedirect->setPath('customer/account');
	}
}
