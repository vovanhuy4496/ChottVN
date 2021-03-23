<?php
declare(strict_types=1);

namespace Chottvn\Notification\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Data extends AbstractHelper
{
    public $date;
    
    protected $customerSession;
    
    protected $deliveryFactory;

    /**
     * @var MessageFactory
     */
    public $messageFactory;

    /**
     * @var MessageTypeFactory
     */
    public $messageTypeFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Chottvn\Notification\Model\MessageFactory $messageFactory,
        \Chottvn\Notification\Model\MessageTypeFactory $messageTypeFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Chottvn\Notification\Model\DeliveryFactory $deliveryFactory,
        DateTime $date
    ) {
        $this->messageFactory = $messageFactory;
        $this->messageTypeFactory = $messageTypeFactory;
        $this->customerSession = $customerSession;
        $this->deliveryFactory = $deliveryFactory;
        $this->date = $date;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    public function getMessageCollection()
    {
        $collection = '';

        try {
            $collectionDelivery = $this->deliveryFactory->create()->getCollection()
                                        ->addFieldToSelect("message_id")
                                        ->addFieldToFilter("customer_id", array("eq" => $this->customerSession->getCustomer()->getId()))
                                        ->load()
                                        ->getData();

            $collection = $this->messageFactory->create()->getCollection()
                                ->addFieldToSelect("*")
                                ->addFieldToFilter("id", array('in' => $collectionDelivery));
            $collection->getSelect()->limit(3)->order('created_at DESC');
            // $this->writeLog($collection->getSelect()->__toString());

            return $collection;
        } catch (LocalizedException $e) {
            $this->writeLog($e);
        } catch (\Exception $e) {
            $this->writeLog($e);
        }
        return $collection;
    }

    public function getLoadMoreMessageCollection($lastId)
    {
        $collection = '';

        try {
            $collectionDelivery = $this->deliveryFactory->create()->getCollection()
                                        ->addFieldToSelect("message_id")
                                        ->addFieldToFilter("customer_id", array("eq" => $this->customerSession->getCustomer()->getId()))
                                        ->load()
                                        ->getData();

            $collection = $this->messageFactory->create()->getCollection()
                                ->addFieldToSelect("*")
                                ->addFieldToFilter("id", array('in' => $collectionDelivery))
                                ->addFieldToFilter("id", array('lt' => $lastId));
            $collection->getSelect()->limit(3)->order('created_at DESC');
            // $this->writeLog($collection->getSelect()->__toString());
            
            return $collection;
        } catch (LocalizedException $e) {
            $this->writeLog($e);
        } catch (\Exception $e) {
            $this->writeLog($e);
        }
        return $collection;
    }

    public function getAllMessageCollection()
    {
        $collection = '';

        try {
            $collectionDelivery = $this->deliveryFactory->create()->getCollection()
                                        ->addFieldToSelect("message_id")
                                        ->addFieldToFilter("customer_id", array("eq" => $this->customerSession->getCustomer()->getId()))
                                        ->load()
                                        ->getData();

            $collection = $this->messageFactory->create()->getCollection()
                                ->addFieldToSelect("*")
                                ->addFieldToFilter("id", array('in' => $collectionDelivery))
                                ->getSize();
            return $collection;
        } catch (LocalizedException $e) {
            $this->writeLog($e);
        } catch (\Exception $e) {
            $this->writeLog($e);
        }
        return $collection;
    }

    public function getDeliveryCollection()
    {
        try {
            $collectionDelivery = $this->deliveryFactory->create()->getCollection()
                                        ->addFieldToSelect("*")
                                        ->addFieldToFilter("customer_id", array("eq" => $this->customerSession->getCustomer()->getId()))
                                        ->load()
                                        ->getData();
            
        } catch (LocalizedException $e) {
            $this->writeLog($e);
        } catch (\Exception $e) {
            $this->writeLog($e);
        }

        return $collectionDelivery;
    }

    public function getReadAtMessage($id)
    {
        try {
            $collectionDelivery = $this->deliveryFactory->create()->getCollection()
                                        ->addFieldToSelect("read_at")
                                        ->addFieldToFilter("customer_id", array("eq" => $this->customerSession->getCustomer()->getId()))
                                        ->addFieldToFilter("message_id", array("eq" => $id))
                                        ->load()
                                        ->getData();
        } catch (LocalizedException $e) {
            $this->writeLog($e);
        } catch (\Exception $e) {
            $this->writeLog($e);
        }

        return $collectionDelivery;
    }

    public function getDetailMessage($id)
    {
        try {
            $collection = $this->messageFactory->create()->getCollection()
                                ->addFieldToSelect("*")
                                ->addFieldToFilter("id", array('eq' => $id))
                                ->load()
                                ->getLastItem()
                                ->getData();
        } catch (LocalizedException $e) {
            $this->writeLog($e);
        } catch (\Exception $e) {
            $this->writeLog($e);
        }

        return $collection;
    }

    public function getMessageType($id)
    {
        try {
            $collection = $this->messageTypeFactory->create()->getCollection()
                                ->addFieldToSelect("name")
                                ->addFieldToFilter("id", array('eq' => $id))
                                ->load()
                                ->getData();
        } catch (LocalizedException $e) {
            $this->writeLog($e);
        } catch (\Exception $e) {
            $this->writeLog($e);
        }
        return $collection;
    }

    public function setReadAt($id)
    {
        try {
            $collectionDelivery = $this->deliveryFactory->create()->getCollection()
                                        ->addFieldToFilter("customer_id", array("eq" => $this->customerSession->getCustomer()->getId()))
                                        ->addFieldToFilter("message_id", array("eq" => $id))
                                        ->getFirstItem();
            if ($collectionDelivery->getData()) {
                $collectionDelivery->setReadAt($this->date->date());
                $collectionDelivery->save();
            }
            
        } catch (LocalizedException $e) {
            $this->writeLog($e);
        } catch (\Exception $e) {
            $this->writeLog($e);
        }
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/getDataMessages.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        switch($type){
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
