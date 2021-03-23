<?php
 
namespace Chottvn\SalesRule\Plugin\Amasty\Rgrid\Model;
 
class RuleActions
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    public function __construct(\Magento\Framework\Module\Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    public function afterToOptionArray(\Amasty\Rgrid\Model\RuleActions $subject, $result)
    {
        if ($this->moduleManager->isEnabled('Chottvn_Sales')) {
            $cttPromoOptions = [
                'cttpromo_order_voucher' => __('Get voucher for next order')
            ];

            $result = array_merge($result, $cttPromoOptions);
        }

        return $result;
    }
}