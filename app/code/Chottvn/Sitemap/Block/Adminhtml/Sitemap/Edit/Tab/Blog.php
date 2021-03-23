<?php
/**
 * @author Tuan Nguyen
 * @copyright Copyright (c) 2020 CTT (https://chotructuyen.co)
 * @package Chottvn_Sitemap
 */


namespace Chottvn\Sitemap\Block\Adminhtml\Sitemap\Edit\Tab;

class Blog extends \Amasty\XmlSitemap\Block\Adminhtml\Sitemap\Edit\Tab\Blog
{

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
        // return !$this->moduleManager->isEnabled('Amasty_Blog');
    }
}
