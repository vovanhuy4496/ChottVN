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

namespace Mageplaza\Blog\Block\Widget;

use Magento\Framework\App\ObjectManager;
use Mageplaza\Blog\Block\Adminhtml\Category\Tree;
use Magento\Widget\Block\BlockInterface;
use Mageplaza\Blog\Block\Frontend;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Block\Listpost;
/**
 * Class Posts
 * @package Mageplaza\Blog\Block\Widget
 */
class Posts extends Listpost implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = "widget/posts.phtml";
    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|\Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getCollection()
    {
        $cate_ids = $this->getData('category_id');
        if ($this->hasData('show_type') && $this->getData('show_type') === 'category') {
            $collection = $this->helperData->getPostList();
            $collection->getSelect()
            ->joinLeft(
                ['category' => $collection->getTable('mageplaza_blog_post_category')],
                'main_table.post_id=category.post_id',
                ['position']
            ) ->distinct(true)
            ->limit($this->getData('post_count') ?: 5);
            if($cate_ids !== "all"){
                $collection->getSelect()->where('category.category_id IN (' . $cate_ids  . ')');
            }
        } else {
            $collection = $this->helperData->getPostList();
        }
        return $collection;
    }

    /**
     * @return \Mageplaza\Blog\Helper\Data
     */
    public function getHelperData()
    {
        return $this->helperData;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * @param $code
     * @return string
     */
    public function getBlogUrl($code)
    {
        return $this->helperData->getBlogUrl($code);
    }

    public function getDataCategory()
    {
        $collection = $this->helperData->getObjectByParam($this->getData('category_id'), null, $this->getData('show_type'));

        return $collection;
    }

    public function getCategoryUrl($url_key)
    {
        return $this->helperData->getBlogUrl($url_key, $this->getData('show_type'));
    }

    public function getNewPost()
    {
        $category_not_ids = $this->getData('category_not_ids');
        $limit = $this->getData('post_count');
        $collection = $this->helperData->getPostList();
        $collection->getSelect()
            ->joinLeft(
                ['category' => $collection->getTable('mageplaza_blog_post_category')],
                'main_table.post_id=category.post_id',
                ['position']
            ) ->distinct(true)
            ->limit($limit ?: 5);
            if($category_not_ids  <> "" && $category_not_ids <> null ){
                $collection->getSelect()->where('category.category_id NOT IN (' . $category_not_ids  . ')');
            }
        // echo $collection->getSelect()->__toString();
        return $collection;
    }
     /**
     * @return array|string
     */
    public function getTree()
    {
        $tree = ObjectManager::getInstance()->create(Tree::class);
        $tree = $tree->getTree(null, $this->store->getStore()->getId());

        return $tree;
    }

    public function getCategoryTreeHtml($tree){
        if (!$tree) {
            return __('Không có thể loại.');
        }
        $html = '';
        foreach ($tree as $value) {
            if (!$value) {
                continue;
            }
            if ($value['enabled']) {
                $level = count(explode('/', ($value['path'])));
                $hasChild = isset($value['children']) && $level < 4;
                $html .= '<ul class="list-group list-group-sm category-level' . $level . '" style="margin-bottom:0px;">';
                $html .= '<li class="list-group-item">';
                $html .= $hasChild ? '<i class="fa fa-plus-square-o mp-blog-expand-tree-' . $level . '"></i>' : '';
                $html .= '<a class="list-categories" href="' . $this->getCategoryUrlcustom($value['url']) . '">';
                // $html .= '<i class="fa fa-folder-open-o">&nbsp;&nbsp;</i>';
                $html .= ucfirst($value['text']) . '</a>';
                $html .= $hasChild ? $this->getCategoryTreeHtml($value['children']) : '';
                $html .= '</li>';
                $html .= '</ul>';

            }
        }
        return $html;
    }
    /**
     * @param $category
     * @return string
     */
    public function getCategoryUrlcustom($category)
    {
        return $this->helperData->getBlogUrl($category, Data::TYPE_CATEGORY);
    }
    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getMostViewPosts()
    {
        $collection = $this->helperData->getPostList();
        $limit = $this->getData('post_count');
        $collection->getSelect()
            ->joinLeft(
                ['traffic' => $collection->getTable('mageplaza_blog_post_traffic')],
                'main_table.post_id=traffic.post_id',
                'numbers_view'
            )
            ->order('numbers_view DESC')
            ->limit( $limit ?: 5);
        return $collection;
    }

    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getRecentPost()
    {
        $limit = $this->getData('post_count');
        $collection = $this->helperData->getPostList();
        $collection->getSelect()
        ->limit( $limit ?: 5);
        return $collection;
    }
}
