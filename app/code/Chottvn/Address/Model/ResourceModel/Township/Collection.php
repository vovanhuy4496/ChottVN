<?php

namespace Chottvn\Address\Model\ResourceModel\Township;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * Locale township name table name
     *
     * @var string
     */
    private $townshipNameTable;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param mixed $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->localeResolver = $localeResolver;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(\Chottvn\Address\Model\Township::class, \Chottvn\Address\Model\ResourceModel\Township::class);

        $this->_idFieldName = 'township_id';
        $this->townshipNameTable = $this->getTable('directory_city_township_name');

        $this->addOrder('name', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $this->addOrder('default_name', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
    }

    /**
     * Initialize select object
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $locale = $this->localeResolver->getLocale();
        $this->addBindParam(':locale', $locale);
        $this->getSelect()->joinLeft(
            ['dctn' => $this->townshipNameTable],
            'main_table.township_id = dctn.township_id AND dctn.locale = :locale',
            ['name' => new \Zend_Db_Expr('IF(dctn.township_id IS NULL, main_table.default_name, dctn.name)')]
        );
        $this->addFilterToMap(
            'township_id',
            'main_table.township_id'
        );
        return $this;
    }

    /**
     * Convert collection items to select options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this as $item) {
            $option = [];
            $option['value'] = $item->getTownshipId();
            $option['city_id'] = $item->getCityId();
            // $option['title'] = ($item->getName() != '') ? $item->getName() : $item->getDefaultName();
            $option['label'] = ($item->getName() != '') ? $item->getName() : $item->getDefaultName();
            $options[] = $option;
        }

        if (!empty($options) > 0) {
            array_unshift(
                $options,
                ['title' => '', 'value' => '', 'label' => __('Please select a township.')]
            );
        }
        return $options;
    }

    /**
     * Get township data
     *
     * @return array
     */
    public function getTownshipData()
    {
        $townships = [];
        foreach ($this as $township) {
            if (!$township->getTownshipId() || !$township->getCityId()) {
                continue;
            }
            $townships[$township->getCityId()][$township->getTownshipId()] = [
                'code' => ($township->getCode() != null) ? $township->getCode() : '',
                'name' => ($township->getName() != '') ? $township->getName() : $township->getDefaultName()
            ];
        }
        return $townships;
    }
}
