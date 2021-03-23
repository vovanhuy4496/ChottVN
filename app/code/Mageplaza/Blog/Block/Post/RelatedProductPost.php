<?php

namespace Mageplaza\Blog\Block\Post;
use Mageplaza\Blog\Block\Post\RelatedPost;
// use Magento\Framework\Registry;
// use Magento\Framework\View\Element\Template;
// use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Blog\Helper\Image;

/**
 * Class RelatedPost
 * @package Mageplaza\Blog\Block\Post
 */
class RelatedProductPost extends RelatedPost
{
     /**
     * Resize Image Function
     *
     * @param $image
     * @param null $size
     * @param string $type
     * @return string
     */
    public function resizeImage($image, $size = null, $type = Image::TEMPLATE_MEDIA_TYPE_POST)
    {
        if (!$image) {
            return $this->getDefaultImageUrl();
        }

        return $this->helperData->getImageHelper()->resizeImage($image, $size, $type);
    }

    /**
     * get default image url
     */
    public function getDefaultImageUrl()
    {
        return $this->getViewFileUrl('Mageplaza_Blog::media/images/mageplaza-logo-default.png');
    }
    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getRelatedPostList()
    {
        if ($this->_relatedPosts == null) {
            /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection */
            $collection = $this->helperData->getPostList();
           
            $collection->getSelect()
                ->join([
                    'related' => $collection->getTable('mageplaza_blog_post_product')],
                    'related.post_id = main_table.post_id AND related.entity_id=' . $this->getProductId()
                )
                ->join([
                    'author' => $collection->getTable('mageplaza_blog_author')],
                    'author.user_id = main_table.author_id',
                    'author.name as name_author'
                )->order('publish_date DESC');
                    $this->_relatedPosts = $collection;
        }
        return $this->_relatedPosts;
    }

}
