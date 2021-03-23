<?php

namespace Chottvn\Address\Observer\Adminhtml;

class TriggerUpdateTownship implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    ) {
        $this->date = $date;
        $this->configWriter = $configWriter;
    }

    /**
     * Save custom address attribute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $this->configWriter->save('chottvn/township/updateflag', $this->date->gmtTimestamp(), 'default', 0);
    }
}
