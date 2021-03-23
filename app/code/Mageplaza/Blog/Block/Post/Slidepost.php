<?php
namespace Mageplaza\Blog\Block\Post;
use Mageplaza\Blog\Block\Frontend;
class Slidepost extends Frontend
{
    public function getSlideCollection($categories_id)
    {
        $collection_post = $this->helperData->getPostCollection('category',$categories_id);
        $collection_post->getSelect()->limit(4);
        return $collection_post;
    }

}