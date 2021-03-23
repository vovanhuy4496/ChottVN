<?php
namespace Chottvn\CustomCatalogSearch\Model\Layer;

class FilterableAttributeList extends \Magento\Catalog\Model\Layer\Category\FilterableAttributeList
{
    protected $collectionFactory;
    protected $storeManager;
    protected $_request;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->_request = $request;
        parent::__construct($collectionFactory, $storeManager);
    }

    protected function _prepareAttributeCollection($collection)
    {
        $collection->addIsFilterableFilter();
        // $query = $this->_request->getParam('q') ? trim($this->_request->getParam('q')):'';
        // if($query){
        //     $collection->addFieldToFilter('is_global',array('eq' => 1));
        // }
        return $collection;
    }
}