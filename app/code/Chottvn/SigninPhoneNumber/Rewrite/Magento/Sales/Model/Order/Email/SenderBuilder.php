<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\SigninPhoneNumber\Rewrite\Magento\Sales\Model\Order\Email;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;

use Magento\Framework\App\DeploymentConfig;

/**
 * Sender Builder
 */
class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{

    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Template $templateContainer
     * @param IdentityInterface $identityContainer
     * @param TransportBuilder $transportBuilder
     * @param TransportBuilderByStore $transportBuilderByStore
     */
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        TransportBuilder $transportBuilder,
        TransportBuilderByStore $transportBuilderByStore = null,
        DeploymentConfig $deploymentConfig
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $transportBuilder,
            $transportBuilderByStore);
        $this->templateContainer = $templateContainer;
        $this->identityContainer = $identityContainer;
        $this->transportBuilder = $transportBuilder;

        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Prepare and send email message
     *
     * @return void
     */
    public function send()
    {        
        $this->configureEmailTemplate();
        $isEmailNone = false;        
        // Check empty email
        $customerEmail = $this->identityContainer->getCustomerEmail();        
        if (empty ($customerEmail) ){
            $isEmailNone = true;
        }
        // Check guest mail     
            //$regex = $this->deploymentConfig->get('customer/guest_mail_regex');   
        $domainName = $_SERVER['SERVER_NAME'];
        $domainNameRegexEscape = str_replace(".","\\.",$_SERVER['SERVER_NAME'] );
        $regex = "/guest_.*?@".$domainNameRegexEscape."/i";
        if ( !empty($regex) ){               
            if (preg_match($regex, $customerEmail) ) {                       
                $isEmailNone = true;
            }
        }        

        $copyTo = $this->identityContainer->getEmailCopyTo();

        if (!empty($copyTo) && $this->identityContainer->getCopyMethod() == 'bcc') {
            foreach ($copyTo as $email) {
                $this->transportBuilder->addBcc($email);                
                if ($isEmailNone == true){
                    $customerEmail = $email;
                }
            }
        }        
        
        // Do nothing if no email
        if (empty ($customerEmail) ){
            return;
        }

        $this->transportBuilder->addTo(
            $customerEmail,
            $this->identityContainer->getCustomerName()
        );

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }

    /**
     * Prepare and send copy email message
     *
     * @return void
     */
    public function sendCopyTo()
    {
        $copyTo = $this->identityContainer->getEmailCopyTo();

        if (!empty($copyTo) && $this->identityContainer->getCopyMethod() == 'copy') {
            $this->configureEmailTemplate();
            foreach ($copyTo as $email) {
                $this->transportBuilder->addTo($email);
                $transport = $this->transportBuilder->getTransport();
                $transport->sendMessage();
            }
        }
    }

    /**
     * Configure email template
     *
     * @return void
     */
    protected function configureEmailTemplate()
    {
        $this->transportBuilder->setTemplateIdentifier($this->templateContainer->getTemplateId());
        $this->transportBuilder->setTemplateOptions($this->templateContainer->getTemplateOptions());
        $this->transportBuilder->setTemplateVars($this->templateContainer->getTemplateVars());
        $this->transportBuilder->setFromByScope(
            $this->identityContainer->getEmailIdentity(),
            $this->identityContainer->getStore()->getId()
        );
    }
}
