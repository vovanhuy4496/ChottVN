<?php
declare(strict_types=1);

namespace Chottvn\Notification\Model\Message;

class Action extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                    'label' => 'All',
                    'value' => 0
                ],
                1 => [
                    'label' => 'Reward Rules',
                    'value' => 1
                ],
                2  => [
                    'label' => 'Level Rules',
                    'value' => 2
                ],
                3  => [
                    'label' => 'Rma Rules',
                    'value' => 3
                ]
            ];
        }
        return $this->_options;
    }
}
