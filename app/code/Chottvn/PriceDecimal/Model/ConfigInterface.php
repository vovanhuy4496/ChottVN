<?php
/**
 *
 * @package Chottvn\PriceDecimal\Model
 *
 * @author  Chottvn Developer <devops@chotructuyen.co>
 */


namespace Chottvn\PriceDecimal\Model;

interface ConfigInterface
{
    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig();

    /**
     * @return mixed
     */
    public function isEnable();
}
