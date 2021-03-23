<?php

namespace Chottvn\Address\Model\ResourceModel\City;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * Locale region name table name
     *
     * @var string
     */
    private $cityNameTable;

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
        $this->_init(\Chottvn\Address\Model\City::class, \Chottvn\Address\Model\ResourceModel\City::class);

        $this->_idFieldName = 'city_id';
        $this->cityNameTable = $this->getTable('directory_region_city_name');

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
            ['drcn' => $this->cityNameTable],
            'main_table.city_id = drcn.city_id AND drcn.locale = :locale',
            ['name' => new \Zend_Db_Expr('IF(drcn.city_id IS NULL, main_table.default_name, drcn.name)')]
        );
        $this->addFilterToMap(
            'city_id',
            'main_table.city_id'
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
            $option['value'] = $item->getCityId();
            $option['region_id'] = $item->getRegionId();
            // $option['title'] = ($item->getName() != '') ? $item->getName() : $item->getDefaultName();
            $option['label'] = ($item->getName() != '') ? $item->getName() : $item->getDefaultName();
            $options[] = $option;
        }

        if (!empty($options) > 0) {
            array_unshift(
                $options,
                ['title' => '', 'value' => '', 'label' => __('Please select a city.')]
            );
        }
        return $options;
    }

    /**
     * Get city data
     *
     * @return array
     */
    public function getCityData()
    {
        $cities = [];
        foreach ($this as $city) {
            if (!$city->getCityId() || !$city->getRegionId()) {
                continue;
            }
            $cities[$city->getRegionId()][$city->getCityId()] = [
                'code' => ($city->getCode() != null) ? $city->getCode() : '',
                'name' => ($city->getName() != '') ? $city->getName() : $city->getDefaultName()
            ];
        }
        return $cities;
    }
}
