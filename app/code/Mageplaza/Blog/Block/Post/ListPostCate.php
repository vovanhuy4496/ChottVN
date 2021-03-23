<?php
namespace Mageplaza\Blog\Block\Post;

use Mageplaza\Blog\Block\Frontend;

class ListPostCate extends Frontend
{
    public function getListPostCates($categories_id)
    {
        $collection = $this->helperData->getPostCollection('category', $categories_id);

        $collection->getSelect()
            ->limit(5);

        return $collection;
    }

    public function getDataCategory($categories_id)
    {
        $collection = $this->helperData->getObjectByParam($categories_id, null, 'category');

        return $collection;
    }

    public function getCategoryUrl($url_key)
    {
        return $this->helperData->getBlogUrl($url_key, 'category');
    }

    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getNewsByCateIds($cate_ids, $limit = 5)
    {
        // Get Collection News
        $collection = $this->helperData->getPostList();

        $collection->getSelect()
            ->joinLeft(
                ['category' => $collection->getTable('mageplaza_blog_post_category')],
                'main_table.post_id=category.post_id',
                ['position']
            )
            ->distinct(true)
            ->where('category.category_id IN (' . $cate_ids . ')')
            ->limit($limit);
        return $collection;
    }

}