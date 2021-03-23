<?php
namespace Chottvn\SalesRule\Model\ResourceModel\SalesRule;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
  protected $_idFieldName = \Chottvn\SalesRule\Api\Data\SalesRuleInterface::COLUMN_ENTITY_ID;
  protected $_eventPrefix = \Chottvn\SalesRule\Api\Data\SalesRuleInterface::COLLECTION;
  protected $_eventObject = \Chottvn\SalesRule\Api\Data\SalesRuleInterface::OBJ_COLLECTION;

  /**
   * Define resource model
   *
   * @return void
   */
  protected function _construct()
  {
    $this->_init('Chottvn\SalesRule\Model\SalesRule', 'Chottvn\SalesRule\Model\ResourceModel\SalesRule');
  }

}