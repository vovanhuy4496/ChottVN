<?php
/**
 * ChottVN
 */
namespace Chottvn\Blog\Helper;

/**
 * Class Data
 * @package Mageplaza\Blog\Helper
 */
class Data extends \Mageplaza\Blog\Helper\Data
{
     /**
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getNameCategoryByIdPost($postId)
    {
        $collection = $this->getFactoryByType(self::TYPE_POST)
        ->create()
        ->getCollection()
        ->addFieldToFilter('main_table.enabled', 1);
        $collection->getSelect()->join(
            ['category' => 'mageplaza_blog_post_category'],
            'main_table.post_id=category.post_id'
        )->join(
            ['ncategory' => 'mageplaza_blog_category'],
            'ncategory.category_id=category.category_id'
        );
        $collection->addFieldToFilter('category.post_id', array('eq' => $postId));
        // var_dump($collection->getSelect()->__toString());die;
        return $collection;
    }
    /**
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getCustomPosts($data = array(), $storeId = null)
    {
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $timefc = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $date = $timefc->date();

        $category_ids = isset($data['category_ids']) ? $data['category_ids']:'';
        $limit = isset($data['limit']) ? (int)$data['limit']:'';

        $collection = $this->getObjectList(self::TYPE_POST, $storeId)
            ->addFieldToFilter('publish_date', ["to" => $date])
            ->addFieldToFilter('start_date', ["to" => $date])
            ->addFieldToFilter(
                ['end_date','end_date'],
                [
                    ['from' => $date],
                    ['null' => true]
                ]
            );

        if($category_ids){
            $collection->getSelect()->join(
                    ['category' => 'mageplaza_blog_post_category'],
                    'main_table.post_id=category.post_id AND category.category_id IN (' . $category_ids . ')',
                    ['category_id']
                );
        }

        // limit
        if($limit){
            $collection->getSelect()->limit($limit);
        }

        // order by
        $collection->getSelect()->order('publish_date DESC');

        // echo $collection->getSelect()->__toString(); 
        // echo "<br>";
        return $collection;
    }

    function convertYoutubeCode($string) {
        $pattern = '#(?:https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?:.+)?$#x';
        preg_match($pattern, $string, $matches);
        return (isset($matches[1])) ? $matches[1] : false;
    }
    /**
     * @param null $type
     * @param null $id
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getAllPostCollectionCTT($idr)
    {   
        $id = null; $storeId = null;
        $lastId = '';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->create('\Magento\Framework\App\RequestInterface');
        $request = $request->getPostValue();
        if($request){
            $lastId = $request['last_id'];  
            $id =$this->_request->getParam('id');
        }
        if (is_null($id)) {
            $id = $this->_request->getParam('id');
        }
        
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->getPostListCTT($idr);
        $collection->join(
            ['category' => $collection->getTable('mageplaza_blog_post_category')],
            'main_table.post_id=category.post_id AND category.category_id=' . $id,
            ['position']
        );
        if ($lastId && $lastId != null) {
            $collection->addFieldToFilter('main_table.publish_date', array('lt' => $lastId));
        }
        return $collection;
    }
     /**
     * @param null $type
     * @param null $id
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getPostCollectionCTT($idr,$type)
    {   
        $id = null; $storeId = null;
        $lastId = '';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->create('\Magento\Framework\App\RequestInterface');
        $request = $request->getPostValue();
        if($request){
            $lastId = $request['last_id'];  
            $id =$this->_request->getParam('id');
        }
        if (is_null($id)) {
            $id = $this->_request->getParam('id');
        }
        
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->getPostListCTT($idr);
        $collection->join(
            ['category' => $collection->getTable('mageplaza_blog_post_category')],
            'main_table.post_id=category.post_id AND category.category_id=' . $id,
            ['position']
        );
       
        if ($lastId && $lastId != null) {
            $collection->addFieldToFilter('main_table.publish_date', array('lt' => $lastId));
        }
        if($type == 'mobile'){
            $collection->getSelect()->order('publish_date DESC')->limit(5);
        }else{
            $collection->getSelect()->order('publish_date DESC')->limit(4);
        }
        
        return $collection;
    }
    
    /**
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getPostListCTT($idr)
    {
        
        $storeId = null;
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->getObjectList(self::TYPE_POST, $storeId)
            ->addFieldToFilter('publish_date', ["to" => $this->dateTime->date()])
            ->addFieldToFilter('start_date', ["to" => $this->dateTime->date()])
            ->addFieldToFilter(
                ['end_date','end_date'],
                [
                    ['from' => $this->dateTime->date()],
                    ['null' => true]
                ]
            )
            ->addFieldToFilter('main_table.post_id', ["neq" => $idr])
            ->setOrder('publish_date', 'desc');
        // echo $collection->getSelect()->__toString();
        // echo "<br>";
        return $collection;
    }
     /**
     * @param null $type
     * @param null $id
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getAllPostCollectionTagCTT()
    {   
        $id = null; $storeId = null;
        $lastId = '';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->create('\Magento\Framework\App\RequestInterface');
        $request = $request->getPostValue();
        if($request){
            $lastId = $request['last_id'];  
            $id =$this->_request->getParam('id');
        }
        if (is_null($id)) {
            $id = $this->_request->getParam('id');
        }
        
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->getPostListTagCTT();
        $collection->join(
            ['tag' => $collection->getTable('mageplaza_blog_post_tag')],
            'main_table.post_id=tag.post_id AND tag.tag_id=' . $id,
            ['position']
        );
      
        if ($lastId && $lastId != null) {
            $collection->addFieldToFilter('main_table.publish_date', array('lt' => $lastId));
        }
       
        return $collection;
    }
     /**
     * @param null $type
     * @param null $id
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getPostCollectionTagCTT($type)
    {   
        $id = null; $storeId = null;
        $lastId = '';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->create('\Magento\Framework\App\RequestInterface');
        $request = $request->getPostValue();
        if($request){
            $lastId = $request['last_id'];  
            $id =$this->_request->getParam('id');
        }
        if (is_null($id)) {
            $id = $this->_request->getParam('id');
        }
        
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->getPostListTagCTT();
        $collection->join(
            ['tag' => $collection->getTable('mageplaza_blog_post_tag')],
            'main_table.post_id=tag.post_id AND tag.tag_id=' . $id,
            ['position']
        );
        
        if ($lastId && $lastId != null) {
            $collection->addFieldToFilter('main_table.publish_date', array('lt' => $lastId));
        }
        if($type == 'mobile'){
            $collection->getSelect()->order('publish_date DESC')->limit(5);
        }else{
            $collection->getSelect()->order('publish_date DESC')->limit(4);
        }
       
        return $collection;
    }
    
    /**
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getPostListTagCTT()
    {
        $storeId = null;
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->getObjectList(self::TYPE_POST, $storeId)
            ->addFieldToFilter('publish_date', ["to" => $this->dateTime->date()])
            ->addFieldToFilter('start_date', ["to" => $this->dateTime->date()])
            ->addFieldToFilter(
                ['end_date','end_date'],
                [
                    ['from' => $this->dateTime->date()],
                    ['null' => true]
                ]
            )
            ->setOrder('publish_date', 'desc');
        // echo $collection->getSelect()->__toString();
        // echo "<br>";
        return $collection;
    }
     /**
     * @param null $type
     * @param null $id
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function checkPostCollectionTagCTT($lastVendorWebsiteId,$type)
    {   
        $id = null; $storeId = null;
        $lastId = '';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->create('\Magento\Framework\App\RequestInterface');
        $request = $request->getPostValue();
        if($request){
            $lastId = $lastVendorWebsiteId;
            $id =$this->_request->getParam('id');
        }
        if (is_null($id)) {
            $id = $this->_request->getParam('id');
        }
        
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->getPostListTagCTT();
        $collection->join(
            ['tag' => $collection->getTable('mageplaza_blog_post_tag')],
            'main_table.post_id=tag.post_id AND tag.tag_id=' . $id,
            ['position']
        );
       
        if ($lastId && $lastId != null) {
            $collection->addFieldToFilter('main_table.publish_date', array('lt' => $lastId));
        }
        if($type == 'mobile'){
            $collection->getSelect()->order('publish_date DESC')->limit(5);
        }else{
            $collection->getSelect()->order('publish_date DESC')->limit(4);
        }

        return $collection;
    }
    
     /**
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getIntervalMessage($now,$ref)
    {
        $interval = $now->diff($ref);
        $intervalMessage  = '';
        switch (true) {
            case $interval->y > 0:
                $intervalMessage = __('%1 year(s) ago', $interval->y);
                break;
            case $interval->m > 0:
                $intervalMessage = __('%1 month(s) ago', $interval->m);
                break;
            case $interval->d > 0:
                $intervalMessage = __('%1 day(s) ago', $interval->d);
                break;
            case $interval->h > 0:
                $intervalMessage = __('%1 hour(s) ago', $interval->h);
                break;
            case $interval->i > 0:
                $intervalMessage = __('%1 minute(s) ago', $interval->i);
                break;
            case $interval->s > 0:
                $intervalMessage = __('%1 second(s) ago', $interval->s);
                break;
            default:
                $intervalMessage = __('recently');
        }
        return  $intervalMessage;
    }
    
       /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/controller.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        switch ($type) {
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
