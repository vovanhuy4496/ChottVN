<?php
namespace Chottvn\Affiliate\Ui\Component\DataProvider\SearchResult;

class RewardRules extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addCustomFilter();

        return $this;
    }

    public function addCustomFilter()
    {
        $this->addFieldToFilter('deleted_at', ['null' => true]);

        return $this;
    }
}