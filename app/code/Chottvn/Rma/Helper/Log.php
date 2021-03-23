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

namespace Chottvn\Rma\Helper;

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
        \Chottvn\Rma\Model\LogFactory $logFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->_logFactory = $logFactory;
        $this->date = $date;
    }

    public function saveLogWithResource($data)
    {
        try{
            $oldLog = $this->getLog($data['account_id'],$data['event']);
            $valueOld = json_encode((object)[]);
            if($oldLog->getId()) {
                $valueOld = $oldLog->getData('value');
            }
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $log = $objectManager->create('Chottvn\Rma\Model\Log');
            $log->setData([
                "account_id" => $data['account_id'],
                "resource_type" => $data['resource_type'],
                "event" => $data['event'],
                "resource_id" => $data['resource_id'],
                "value" => json_encode($data['value']),
                "value_old" => $valueOld
            ]);
            $log->save();
            // $this->writeLog($log->getData());
        }catch (\Exception $e) {
            $this->writeLog($e->getMessage());
        }
    }

    public function getLog($accountId,$event) {
        try{
        $log = $this->_logFactory->create();

        $collection = $log->getCollection()
        ->addFieldToFilter('account_id', $accountId)
        ->addFieldToFilter('event', $event)
        ->setOrder('created_at', 'ASC');
        }catch (\Exception $e) {
            $this->writeLog($e->getMessage());
        }
        return $collection->getLastItem();
    }
      /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/log_rma.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        switch ($type) {
            case "error":
                $logger->err($info);
                break;
            case "warning":
                $logger->notice($info);
                break;
            case "info":
                $logger->info($info);
                break;
            default:
                $logger->info($info);
        }
    }
}
