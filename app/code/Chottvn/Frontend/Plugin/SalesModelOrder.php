<?php
 
namespace Chottvn\Frontend\Plugin;
 
class SalesModelOrder
{

    public function afterGetFrontendStatusLabel(\Magento\Sales\Model\Order $subject, $result)
    {
        return __($result);
        //array_map('__', $result);
    }
}