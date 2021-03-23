<?php
namespace Mageplaza\Blog\Block\Post;

use Mageplaza\Blog\Block\Frontend;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Helper\Data as HelperData;

class Videopost extends Frontend
{
    public function getVideoCollection($id_cate)
    {
        $collection_post = $this->helperData->getPostCollection(Data::TYPE_CATEGORY,$id_cate);
        $collection_post->getSelect()
        ->limit(4);
        return $collection_post;
    }
    public function getCategoriesBlog($id_cate)
    {
        $collection = $this->helperData->getObjectByParam($id_cate,null,Data::TYPE_CATEGORY);
        $collection->getSelect();
        return $collection;
    }
    public function getBlogUrl($code)
    {
        return $this->helperData->getBlogUrl($code,Data::TYPE_CATEGORY);
    }

}