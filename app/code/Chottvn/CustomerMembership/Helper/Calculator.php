<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_RulesLoyalty
 */

namespace Chottvn\CustomerMembership\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Calculator extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $sessionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->priceCurrency = $priceCurrency;
        $this->resource = $resource;
        $this->sessionFactory = $sessionFactory;
        $this->customerFactory = $customerFactory;
    }
    
    public function getThisMonthTotal($customerId)
    {
        $from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $to = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('t'), date('Y')));

        $conditions[] = ['date' => ' >= "' . $from . '"'];
        $conditions[] = ['date' => ' <= "' . $to . '"'];
        $conditions[] = ['status' => ' = "complete"'];

        return $this->_getTotals($customerId, $conditions);
    }

    public function getLastMonthTotal($customerId)
    {
        $y = date('Y');
        $m = date('m');
        if (0 == $m - 1) {
            $y = $y - 1;
            $m = 12;
        } else {
            $m = $m - 1;
        }
        $last = mktime(0, 0, 0, $m, 1, $y);

        $from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $last), 1, date('Y', $last)));
        $to = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $last), date('t', $last), date('Y', $last)));

        $conditions[] = ['date' => ' >= "' . $from . '"'];
        $conditions[] = ['date' => ' <= "' . $to . '"'];
        $conditions[] = ['status' => ' = "complete"'];

        return $this->_getTotals($customerId, $conditions);
    }

    public function getAllPeriodTotal($chottCustomerPhoneNumber)
    {
        $conditions[] = ['status' => ' = "complete"'];

        return $this->_getTotals($chottCustomerPhoneNumber, $conditions);
    }

    public function getSingleTotalField($customerId, $fieldName, $conditions, $conditionType)
    {
        $result = $this->_getTotals($customerId, $conditions, $conditionType);

        return $result[$fieldName];
    }

    /**
     * Calculates aggregated order values for given customer
     *
     * @param int $customerId
     * @param array $conditions e.g. array( 0=> array('date'=>'>2013-12-04'),  1=>array('status'=>'>2013-12-04'))
     * @param string $conditionType "all"  or "any"
     */
    protected function _getTotals($chottCustomerPhoneNumber, $conditions = [], $conditionType = 'all')
    {
        return $this->getTotalsV2($chottCustomerPhoneNumber, $conditions, $conditionType);
    }

    public function getTotals($chottCustomerPhoneNumber, $conditions, $conditionType)
    {
        //$customerId = $this->getCustomerSession()->getCustomerId();
        $db = $this->resource->getConnection('default');

        $select = $db->select()
            ->from(['o' => $this->resource->getTableName('sales_order')], [])
            ->where('o.chott_customer_phone_number = ?', $chottCustomerPhoneNumber);

        $map = [
            'date' => 'o.created_at',
            'status' => 'o.status',
        ];

        foreach ($conditions as $element) {
            $value = current($element);
            $field = $map[key($element)];
            $w = $field . ' ' . $value;

            if ($conditionType == 'all') {
                $select->where($w);
            } else {
                $select->orWhere($w);
            }
        }

        $select->from(
            null,
            [
                'count' => 'COUNT(*)',
                'amount' => 'SUM(o.base_grand_total)',
                'shipping' => 'SUM(o.base_shipping_amount)',
                'refunded' => 'SUM(o.base_total_refunded)'
            ]
        );
        $row = $db->fetchRow($select);

        return [
            'average_order_value' => $row['count'] ? ($row['amount'] - $row['refunded']) / $row['count'] : 0,
            'total_orders_amount' => $row['amount'] - $row['refunded'] - $row['shipping'],
            'of_placed_orders' => $row['count'],
        ];
    }

     public function getTotalsV2($chottCustomerPhoneNumber, $conditions, $conditionType)
    {
        //$customerId = $this->getCustomerSession()->getCustomerId();
        $db = $this->resource->getConnection('default');
        // Get Order Ids
        $selectOrderIds = $db->select()
            ->from(['o' => $this->resource->getTableName('sales_order')], [])
            ->where('o.chott_customer_phone_number = ?', $chottCustomerPhoneNumber);
        
        $map = [
            'date' => 'o.created_at',
            'status' => 'o.status',
            'custom' => 'custom',
        ];
        foreach ($conditions as $element) {
            $value = current($element);
            $field = $map[key($element)];
            $w = $field . ' ' . $value;

            // add custom where query
            $where = '';
            if($field == 'custom'){
                $where = $value;
                $selectOrderIds->where($where);
            }else{
                if ($conditionType == 'all') {
                    $selectOrderIds->where($w);
                } else {
                    $selectOrderIds->orWhere($w);
                }
            }
        }        
        $selectOrderIds->from(
            null,
            [
                'o.entity_id'
            ]
        );

        $this->writeLog('Query sale order: '.$selectOrderIds->__toString());

        $orderIds = $db->fetchAll($selectOrderIds);
        if (empty($orderIds)){
            $orderIds = [];
        }
        // SUM on Order Items
        $select= $db->select()
            ->from(['oi' => $this->resource->getTableName('sales_order_item')], [])
            ->where('oi.order_id IN (?)', array_column($orderIds, 'entity_id'));

        $select->from(
            null,
            [
                'count' => 'COUNT(distinct(oi.order_id))',
                'amount' => 'SUM((oi.qty_ordered - oi.qty_refunded)  *  oi.base_price)'
            ]
        );

        $this->writeLog('Query sale order item: '.$select->__toString());

        $row = $db->fetchRow($select);

        return [            
            'total_orders_amount' => $row['amount'],
            'of_placed_orders' => $row['count'],
            'average_order_value' => $row['count'] ? ($row['amount']) / $row['count'] : 0,
        ];
    }

    public function getMembership($customerId)
    {
        $customer = $this->customerFactory->create();
        $customer->load($customerId);
        $created = $customer->getCreatedAt();

        return round((time() - strtotime($created)) / 60 / 60 / 24);
    }

    /**
     * Convert price
     *
     * @param float $value
     * @param bool $format
     *
     * @return float
     */
    public function convertPrice($value, $store, $format = true)
    {
        return $format
            ? $this->priceCurrency->convertAndFormat(
                $value,
                true,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $store
            )
            : $this->priceCurrency->convert($value, $this->getStore());
    }

    /**
      * @param $info
      * @param $type  [error, warning, info]
      * @return 
      */
      private function writeLog($info, $type = "info") {
          $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/chottvn_salesrule.log');
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
