<?php
declare(strict_types=1);

namespace Chottvn\Notification\Model\Message;

class Status extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                0 => [
                    'label' => 'Inactive',
                    'value' => 0
                ],
                1 => [
                    'label' => 'Active',
                    'value' => 1
                ]
                // 0 => [
                //     'label' => 'Pending',
                //     'value' => 0
                // ],
                // 1 => [
                //     'label' => 'Processing',
                //     'value' => 1
                // ],
                // 2  => [
                //     'label' => 'Completed',
                //     'value' => 10
                // ],
                // 3 => [
                //     'label' => 'Canceled',
                //     'value' => 20
                // ],
            ];
        }
        return $this->_options;
    }
}

