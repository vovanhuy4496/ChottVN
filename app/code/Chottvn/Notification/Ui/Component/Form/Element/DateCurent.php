<?php

namespace Chottvn\Notification\Ui\Component\Form\Element;

class DateCurent extends \Magento\Ui\Component\Form\Element\Input
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

        if (isset($config['dataScope']) && ($config['dataScope']=='updated_at' || $config['dataScope']=='created_at')) {
            $config['default']= date('Y-m-d');
            $this->setData('config', (array)$config);
        }
    }
}