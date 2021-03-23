<?php
namespace Chottvn\Blog\Model;

class Post extends \Mageplaza\Blog\Model\Post
{

    /**
     * @param null $limit
     * @return ResourceModel\Post\Collection|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRelatedPostsCollection($limit = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $timefc = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $date = $timefc->date();
        $topicIds = $this->_getResource()->getTopicIds($this);
        if (sizeof($topicIds)) {
            $collection = $this->postCollectionFactory->create()
            ->addFieldToFilter('start_date', ["to" => $date])
            ->addFieldToFilter(
                ['end_date','end_date'],
                [
                    ['from' => $date],
                    ['null' => true]
                ]
            );
            $collection->getSelect()
                ->join(
                    ['topic' => $this->getResource()->getTable('mageplaza_blog_post_topic')],
                    'main_table.post_id=topic.post_id AND topic.post_id != "' . $this->getId() . '" AND topic.topic_id IN (' . implode(',', $topicIds) . ')',
                    ['position']
                )->where("main_table.enabled='1'")->group('main_table.post_id');
            if ($limit = (int)$this->helperData->getBlogConfig('general/related_post')) {
                $collection->getSelect()
                    ->limit($limit);
            }
            $collection->getSelect()->order('publish_date DESC');

            return $collection;
        }

        return null;
    }

    /**
     * @param null $limit
     * @return ResourceModel\Post\Collection|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRelatedPostsByHashTagIdsCollection($tag_ids = array(), $limit = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $timefc = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $date = $timefc->date();
        $categoryIds = $this->_getResource()->getCategoryIds($this);
        if (sizeof($categoryIds) && sizeof($tag_ids)) {
            $collection = $this->postCollectionFactory->create()
            ->addFieldToFilter('start_date', ["to" => $date])
            ->addFieldToFilter(
                ['end_date','end_date'],
                [
                    ['from' => $date],
                    ['null' => true]
                ]
            );
            $collection->getSelect()
                ->join(
                    ['category' => $this->getResource()->getTable('mageplaza_blog_post_category')],
                    'main_table.post_id=category.post_id AND category.post_id != "' . $this->getId() . '" AND category.category_id IN (' . implode(',', $categoryIds) . ')',
                    ['category_id']
                );

            // query with tags
            if($tag_ids){
                $collection->getSelect()
                ->join(
                    ['tag' => $this->getResource()->getTable('mageplaza_blog_post_tag')],
                    'main_table.post_id=tag.post_id AND tag.post_id != "' . $this->getId() . '" AND tag.tag_id IN (' . implode(',', $tag_ids) . ')',
                    ['tag_id']
                );
            }

            $collection->getSelect()->where("main_table.enabled='1'")->group('main_table.post_id');
            
            // limit
            if ($limit == null) {
                $limit = (int)$this->helperData->getBlogConfig('general/related_post');
            }
            $collection->getSelect()->limit($limit);

            // sort by order
            $collection->getSelect()->order('publish_date DESC');
            
            return $collection;
        }

        return null;
    }
}
