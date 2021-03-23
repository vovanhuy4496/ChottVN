<?php

namespace Chottvn\Affiliate\Block;


class CaptchaForm extends \Magento\Framework\View\Element\Template
{
    public function getFormAction()
    {
        return $this->getUrl('affiliate/register/create', ['_secure' => true]);
    }
}
