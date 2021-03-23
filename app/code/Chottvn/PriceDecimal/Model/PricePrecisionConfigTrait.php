<?php
/**
 *
 * @package Chottvn\PriceDecimal
 *
 * @author  Chottvn Developer <devops@chotructuyen.co>
 */

namespace Chottvn\PriceDecimal\Model;

trait PricePrecisionConfigTrait
{


    /**
     * @return \Chottvn\PriceDecimal\Model\ConfigInterface
     */
    public function getConfig()
    {
        return $this->moduleConfig;
    }

    /**
     * @return int|mixed
     */
    public function getPricePrecision()
    {
        if ($this->getConfig()->canShowPriceDecimal()) {
            return $this->getConfig()->getPricePrecision();
        }

        return 0;
    }
}
