<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\CustomCatalogSearch\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Category as CategoryDataProvider;

/**
 * Layer category filter
 */
class Category extends AbstractFilter
{
    protected $_request;
    protected $_categoryCollectionFactory;
    protected $_listcates;
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var CategoryDataProvider
     */
    private $dataProvider;

    /**
     * Category constructor.
     *
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->_request = $request;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->escaper = $escaper;
        $this->_requestVar = 'cat';
        $this->_listcates = array();
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    /**
     * Apply category filter to product collection
     *
     * @param   \Magento\Framework\App\RequestInterface $request
     * @return  $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $categoryId = $request->getParam($this->_requestVar) ?: $request->getParam('id');
        if (empty($categoryId)) {
            return $this;
        }

        $this->dataProvider->setCategoryId($categoryId);

        $category = $this->dataProvider->getCategory();

        $this->getLayer()->getProductCollection()->addCategoryFilter($category);

        if ($request->getParam('id') != $category->getId() && $this->dataProvider->isValid()) {
            $this->getLayer()->getState()->addFilter($this->_createItem($category->getName(), $categoryId));
        }
        return $this;
    }

    /**
     * Get filter value for reset current filter state
     *
     * @return mixed|null
     */
    public function getResetValue()
    {
        return $this->dataProvider->getResetValue();
    }

    /**
     * Get filter name
     *
     * @return \Magento\Framework\Phrase
     */
    public function getName()
    {
        return __('Category');
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        $optionsFacetedData = $productCollection->getFacetedData('category');
        $category = $this->dataProvider->getCategory();

        $query = $this->_request->getParam('q') ? trim($this->_request->getParam('q')) : '';

        // check query in search page
        if($query){
            //$categories = explode(',', $category->getAllChildren());
            $tmp_categories = $category->getChildrenCategories();
            $this->getChildrenCategoryIds($tmp_categories);

            // get categories sort by 
            $categories = $this->_listcates;
            $collectionSize = $productCollection->getSize();

            // load object manager
            $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            // check category is active
            if ($category->getIsActive()) {
                foreach ($categories as $category_id) {
                    $category = $_objectManager->create('Magento\Catalog\Model\Category')->load($category_id);
                    $is_primary_category = false;
                    if(null !== $category->getCustomAttribute('chottvn_is_category_nh_product_attribute')){
                        $is_primary_category = $category->getCustomAttribute('chottvn_is_category_nh_product_attribute')->getValue();
                    }
                    if ($category->getIsActive()
                        && isset($optionsFacetedData[$category->getId()])
                        && $this->isOptionReducesResults($optionsFacetedData[$category->getId()]['count'], $collectionSize)
                        && $is_primary_category == true
                    ) {
                        $this->itemDataBuilder->addItemData(
                            $this->escaper->escapeHtml($category->getName()),
                            $category->getId(),
                            $optionsFacetedData[$category->getId()]['count']
                        );
                    }
                }
            }

        }else{

            // old function
            $categories = $category->getChildrenCategories();

            $collectionSize = $productCollection->getSize();

            if ($category->getIsActive()) {
                foreach ($categories as $category) {
                    if ($category->getIsActive()
                        && isset($optionsFacetedData[$category->getId()])
                        && $this->isOptionReducesResults($optionsFacetedData[$category->getId()]['count'], $collectionSize)
                    ) {
                        $this->itemDataBuilder->addItemData(
                            $this->escaper->escapeHtml($category->getName()),
                            $category->getId(),
                            $optionsFacetedData[$category->getId()]['count']
                        );
                    }
                }
            }
        }
        return $this->itemDataBuilder->build();
    }

    public function getCatgoryIdsSortByPosition($cateid){
        $collection = $this->_categoryCollectionFactory->create();

        // get by list ids
        $collection->addFieldToFilter('entity_id', array(
                                            'eq' => $cateid)
                                         );

        // select only active categories
        $collection->addIsActiveFilter();

        // sort categories by some value
        $collection->addAttributeToSort('position');

        return $collection;
    }

    public function getChildrenCategoryIds($categories){
        foreach ($categories as $category) {
            $cate_id = $category->getId();
            $this->_listcates[] = $cate_id;
            $children_categories = $category->getChildrenCategories($cate_id);
            if($children_categories->count() > 0){
                $this->getChildrenCategoryIds($children_categories);
            }
        }
    }

}
