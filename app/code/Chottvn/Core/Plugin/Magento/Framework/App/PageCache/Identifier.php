<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\Core\Plugin\Magento\Framework\App\PageCache;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class CachePlugin
 * Should add design exceptions o identifier for built-in cache
 */
class Identifier
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\DesignExceptions $designExceptions
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\PageCache\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\View\DesignExceptions $designExceptions,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Http\Context $context,
        \Magento\PageCache\Model\Config $config
    ) {
        $this->designExceptions = $designExceptions;
        $this->request = $request;
        $this->context = $context;
        $this->config = $config;
    }

    /**
     * Adds a theme key to identifier for a built-in cache if user-agent theme rule is actual
     *
     * @param \Magento\Framework\App\PageCache\Identifier $identifier
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetValue(\Magento\Framework\App\PageCache\Identifier $identifier, $result)
    {
        if ($this->config->getType() == \Magento\PageCache\Model\Config::BUILT_IN && $this->config->isEnabled()) {
          $userAgent = $this->request->getHeader('User-Agent');
          $server  = $this->request->getServer();
          $isMobileDevice = \Zend_Http_UserAgent_Mobile::match($userAgent, $server);
          $device = "desktop";
          if($isMobileDevice){
            $device = "mobile";
          }
          //$this->writeLog(json_encode($userAgent));
          $data = [
              $this->request->isSecure(),
              $this->request->getUriString(),
              $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
                  ?: $this->context->getVaryString(),
              $device
          ];
          $serializer = ObjectManager::getInstance()->get(Json::class);
          $result =  sha1($serializer->serialize($data));
          $ruleDesignException = $this->designExceptions->getThemeByRequest($this->request);
          if ($ruleDesignException !== false) {
              return $ruleDesignException . $result;
          }
        }
        //$this->writeLog("FINAL cache_key:  ". $result);
        return $result;
    }

    /**
   * @param $info
   * @param $type  [error, warning, info]
   * @return
   */
    private function writeLog($info, $type = "info") {
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/chottvn_core.log');
      $logger = new \Zend\Log\Logger();
      $logger->addWriter($writer);
      switch ($type) {
          case "error":
              $logger->err($info);
          break;
          case "warning":
              $logger->notice($info);
          break;
          case "info":
              $logger->info($info);
          break;
          default:
             $logger->info($info);
      }
    }
}
