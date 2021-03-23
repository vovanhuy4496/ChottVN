<?php
namespace Chottvn\SalesRule\Model\ResourceModel;


class SalesRule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
  
  public function __construct(
    \Magento\Framework\Model\ResourceModel\Db\Context $context
  )
  {
    parent::__construct($context);
  }
  
  protected function _construct()
  {
    $this->_init(\Chottvn\SalesRule\Api\Data\SalesRuleInterface::TABLE_NAME, \Chottvn\SalesRule\Api\Data\SalesRuleInterface::COLUMN_ENTITY_ID);
  }
  
}