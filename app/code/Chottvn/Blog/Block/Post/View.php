<?php
namespace Chottvn\Blog\Block\Post;

class View extends \Mageplaza\Blog\Block\Post\View
{
    /**
     * get tag list
     * @param Post $post
     * @return string
     */
    public function getTagList($post)
    {
        $tagCollection = $post->getSelectedTagsCollection();
        $result = '';
        if (!empty($tagCollection)) {
            $listTags = [];
            foreach ($tagCollection as $tag) {
                $listTags[] = '<a class="mp-info" href="' . $this->getTagUrl($tag) . '">' . $tag->getName() . '</a>';
            }
            $result = implode('', $listTags);
        }

        return $result;
    }

    /**
     * get tag ids list
     * @param Post $post
     * @return string
     */
    public function getTagIdsList($post)
    {
        $tagCollection = $post->getSelectedTagsCollection();
        $listTags = [];
        if (!empty($tagCollection)) {
            foreach ($tagCollection as $tag) {
                $listTags[] = $tag->getId();
            }
        }

        return $listTags;
    }
}
