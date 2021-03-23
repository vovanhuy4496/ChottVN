<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\Affiliate\Model\ResourceModel\Grid;

use Magento\Customer\Ui\Component\DataProvider\Document;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @inheritdoc
     */
    protected $document = Document::class;

    /**
     * @inheritdoc
     */
    protected $_map = ['fields' => ['entity_id' => 'main_table.entity_id']];

    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'customer_grid_flat',
        $resourceModel = \Magento\Customer\Model\ResourceModel\Customer::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * This is the function that will add the filter
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();

        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $customerGroupsCollection = $objectManager->create('\Magento\Customer\Model\ResourceModel\Group\Collection');

        // $customerGroupsCollection->addFieldToFilter('customer_group_code',['eq'=>'Affiliate']);

        // $collection = $customerGroupsCollection->getFirstItem();

        // if($collection) {
        //     $this->addFieldToFilter('main_table.group_id',['eq'=>$collection['customer_group_id']]);
        // }

        // $this->addFieldToFilter('main_table.group_id', ['eq' => 4]);
        $this->addFieldToFilter('vw_affiliate_info.affiliate_status', ['neq' => NULL]);
        // $this->addFieldToFilter('attribute_code', ['eq' => 'affiliate_status']);
        // $this->addFieldToFilter('attribute_value', ['neq' => 'NULL']);

        return $this;
    }

    // protected function _renderFiltersBefore()
    // {
    //     $joinTable = $this->getTable('customer_entity');
    //     $this->getSelect()->joinLeft($joinTable, 'main_table.entity_id = customer_entity.entity_id', ['affiliate_code', 'affiliate_status']);
    //     parent::_renderFiltersBefore();
    // }

    protected function _initSelect()
    {
        parent::_initSelect();
        // Get Customer info
        // $this->getSelect()->joinLeft(
        //     ['customer_entity_varchar' => $this->getTable('customer_entity_varchar')],
        //     'main_table.entity_id = customer_entity_varchar.entity_id',
        //     ['attribute_value' => 'customer_entity_varchar.value', 'attribute_id']
        // );
        // $this->getSelect()->joinLeft(
        //     ['eav_attribute' => $this->getTable('eav_attribute')],
        //     'customer_entity_varchar.attribute_id = eav_attribute.attribute_id',
        //     ['attribute_code']
        // );
        // $this->addFilterToMap('attribute_value', 'customer_entity_varchar.value');
        $this->getSelect()->joinLeft(
            ['vw_affiliate_info' => $this->getTable('vw_affiliate_info')],
            'main_table.entity_id = vw_affiliate_info.entity_id'
        );
    }
}
