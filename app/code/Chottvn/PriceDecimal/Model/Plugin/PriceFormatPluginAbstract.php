<?php
/**
 *
 * @package Chottvn\PriceDecimal\Model\Plugin
 *
 * @author  Chottvn Developer <devops@chotructuyen.co>
 */


namespace Chottvn\PriceDecimal\Model\Plugin;

use Chottvn\PriceDecimal\Model\ConfigInterface;
use Chottvn\PriceDecimal\Model\PricePrecisionConfigTrait;

abstract class PriceFormatPluginAbstract
{

    use PricePrecisionConfigTrait;

    /** @var ConfigInterface  */
    protected $moduleConfig;

    /**
     * @param \Chottvn\PriceDecimal\Model\ConfigInterface $moduleConfig
     */
    public function __construct(
        ConfigInterface $moduleConfig
    ) {
        $this->moduleConfig  = $moduleConfig;
    }
}
