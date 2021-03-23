<?php

namespace Chottvn\Finance\Ui\Component\Form\Element;

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

        if (isset($config['dataScope']) && ($config['dataScope']=='start_date' || $config['dataScope']=='processed_at')) {
            $config['default']= date('Y-m-d');
            $this->setData('config', (array)$config);
        }
    }
}