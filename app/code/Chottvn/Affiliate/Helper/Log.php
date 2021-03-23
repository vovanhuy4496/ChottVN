<?php

/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 *
 *
 * @category    Chottvn
 * @package     Chottvn_Affiliate
 * 
 */

namespace Chottvn\Affiliate\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Account
 * @package Chottvn\Affiliate\Helper
 */
class Log extends  AbstractHelper
{
    /**
     * @var DateTime
     */
    protected $date;

    public function __construct(
        Context $context,
        \Chottvn\Affiliate\Model\LogFactory $logFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->_logFactory = $logFactory;
        $this->date = $date;
    }

    public function saveLog($data)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $log = $objectManager->create('Chottvn\Affiliate\Model\Log');
        $log->setData([
            "account_id" => $data['account_id'],
            "event" => $data['event'],
            "value" => isset($data['value']) ? $data['value'] : $this->date->gmtTimestamp(),
            "created_at" => $this->date->gmtDate()
        ]);

        $log->save();
    }

    public function saveLogWithResource($data)
    {
        $oldLog = $this->getLog($data['account_id'], $data['event']);
        $valueOld = json_encode((object)[]);
        if($oldLog->getId()) {
            $valueOld = $oldLog->getData('value');
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $log = $objectManager->create('Chottvn\Affiliate\Model\Log');
        $log->setData([
            "account_id" => $data['account_id'],
            "event" => $data['event'],
            "resource_type" => $data['resource_type'],
            "resource_id" => $data['resource_id'],
            "value" => json_encode($data['value']),
            "value_old" => $valueOld,
            "created_at" => $this->date->gmtDate()
        ]);

        $log->save();
    }

    public function getLog($accountId, $event) {
        $log = $this->_logFactory->create();

        $collection = $log->getCollection()
        ->addFieldToFilter('account_id', $accountId)
        ->addFieldToFilter('event', $event)
        ->setOrder('created_at', 'ASC');

        return $collection->getLastItem();
    }
}
