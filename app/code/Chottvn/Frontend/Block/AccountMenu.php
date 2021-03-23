<?php
/**
 * Copyright (c) 2019 ChottVN
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

namespace Chottvn\Frontend\Block;

use Magento\Customer\Model\Session as CustomerSession;
use Mageplaza\SocialLogin\Helper\Social as SocialHelper;

/**
 * Class AccountMenu
 *
 * @package Chottvn\Frontend\Block
 */
class AccountMenu extends \Magento\Framework\View\Element\Template
{

    protected $customerSession;
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CustomerSession $customerSession,
        SocialHelper $socialHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->socialHelper = $socialHelper;
        $this->_isScopePrivate = true;
    }

    /**
     * @return string
     */
    public function getAccountMenu()
    {
        //Your block code
        return "Menu";
        //return __('Hello Developer! This how to get the storename: %1 and this is the way to build a url: %2', $this->_storeManager->getStore()->getName(), $this->getUrl('contacts'));
    }

    /**
     * @return {boolean}
     */
    public function isLoggedIn(){
        return $this->customerSession->isLoggedIn();
    }
    /**
     * @return {Object}
     */
    public function getCustomer(){
        return $this->customerSession->getCustomer();
    }
    /**
     * @return {String}
     */
    public function getAccountLabel(){
        if ($this->isLoggedIn()){
            $name = $this->getCustomer()->getFirstname();
            $name = explode(" ", $name);
            $name = array_slice($name, -2);

            return '<strong>'.implode(' ', $name).'</strong>';
        }else{
            // return __("Account");
            return __("Sign up");
        }
    }

    /**
     * @return {String}
     */
    public function getSayHiAccountLabel(){
        if ($this->isLoggedIn()){
            $prefix = $this->getCustomer()->getPrefix() ? strtolower($this->getCustomer()->getPrefix()):'';
            // $genderName = '';
            // $gender = $this->getCustomer()->getGender();
            // switch ($gender) {
            //     case 1:
            //         $genderName = strtolower(__('Male'));
            //         break;
            //     case 2:
            //         $genderName = strtolower(__('Female'));
            //         break;
            // }
            // return __('Hi').' '.$prefix;
            return __('Hi').' ';
        }else{
            return __('Sign in / Sign up');
        }
    }


    /**
     * @return array
     */
    public function getAvailableSocials()
    {
        $availableSocials = [];

        foreach ($this->socialHelper->getSocialTypes() as $socialKey => $socialLabel) {
            $this->socialHelper->setType($socialKey);
            if ($this->socialHelper->isEnabled()) {
                $availableSocials[$socialKey] = [
                    'label'     => $socialLabel,
                    'login_url' => $this->getLoginUrl($socialKey),
                ];
            }
        }

        return $availableSocials;
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function getBtnKey($key)
    {
        switch ($key) {
            case 'vkontakte':
                $class = 'vk';
                break;
            default:
                $class = $key;
        }

        return $class;
    }

    /**
     * @return array
     */
    public function getSocialButtonsConfig()
    {
        $availableButtons = $this->getAvailableSocials();
        foreach ($availableButtons as $key => &$button) {
            $button['url']     = $this->getLoginUrl($key, ['authen' => 'popup']);
            $button['key']     = $key;
            $button['btn_key'] = $this->getBtnKey($key);
        }

        return $availableButtons;
    }


    /**
     * @param $socialKey
     * @param array $params
     *
     * @return string
     */
    public function getLoginUrl($socialKey, $params = [])
    {
        $params['type'] = $socialKey;

        return $this->getUrl('sociallogin/social/login', $params);
    }
}
