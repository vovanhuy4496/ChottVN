<?php

namespace Chottvn\Notification\Ui\Component\Form\Element;

class ProcessedBy extends \Magento\Ui\Component\Form\Element\Input
{
    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $config = $this->getData('config');

        if (isset($config['dataScope']) && $config['dataScope']=='created_by') {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $extensionUser = $objectManager->get('Magento\Backend\Model\Auth\Session')->getUser()->getId();
            $config['default']= $extensionUser;
            $this->setData('config', (array)$config);
        }
    }
}