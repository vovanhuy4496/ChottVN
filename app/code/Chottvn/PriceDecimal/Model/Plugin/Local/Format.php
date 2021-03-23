<?php
/**
 *
 * @package package Chottvn\PriceDecimal\Model\Plugin\Local
 *
 * @author  Chottvn Developer <devops@chotructuyen.co>
 */

namespace Chottvn\PriceDecimal\Model\Plugin\Local;

use Chottvn\PriceDecimal\Model\Plugin\PriceFormatPluginAbstract;

class Format extends PriceFormatPluginAbstract
{

    /**
     * {@inheritdoc}
     *
     * @param $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterGetPriceFormat($subject, $result)
    {
        $precision = $this->getPricePrecision();

        if ($this->getConfig()->isEnable()) {
            $result['precision'] = $precision;
            $result['requiredPrecision'] = $precision;
        }

        return $result;
    }
}
