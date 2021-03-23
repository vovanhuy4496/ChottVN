<?php
namespace Chottvn\SalesRule\Model;

class SalesRule extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
  const CACHE_TAG = \Chottvn\SalesRule\Api\Data\SalesRuleInterface::CACHE_TAG;

  protected $_cacheTag = \Chottvn\SalesRule\Api\Data\SalesRuleInterface::CACHE_TAG;

  protected $_eventPrefix = \Chottvn\SalesRule\Api\Data\SalesRuleInterface::TABLE_NAME;

  protected function _construct()
  {
    $this->_init('Chottvn\SalesRule\Model\ResourceModel\SalesRule');
  }

  public function getIdentities()
  {
    return [self::CACHE_TAG . '_' . $this->getId()];
  }

  public function getDefaultValues()
  {
    $values = [];

    return $values;
  }
}