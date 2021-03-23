<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block\Post;

use Mageplaza\Blog\Block\Frontend;
use Mageplaza\Blog\Block\Listpost;

/**
 * Class Listpost
 * @package Mageplaza\Blog\Block\Post
 */
class ListPostNew extends Frontend
{
    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getNewPost()
    {
        $category_id_video = $this->getData('category_id_video');
        $category_id_khuyen_mai = $this->getData('category_id_khuyen_mai');
        $limit = $this->getData('post_count');

        $collection = $this->helperData->getPostList();

        $collection->getSelect()
            ->joinLeft(
                ['category' => $collection->getTable('mageplaza_blog_post_category')],
                'main_table.post_id=category.post_id',
                ['position']
            )
            ->where('category.category_id != ' . $category_id_video)
            ->where('category.category_id != ' . $category_id_khuyen_mai)

            ->limit($limit);

        return $collection;
    }
}
