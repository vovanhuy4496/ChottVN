<?php

namespace Chottvn\Address\Controller\Data;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Chottvn\Address\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Chottvn\Address\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Chottvn\Address\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->helper = $helper;
    }

    /**
     * Get Json data action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return string
     */
    public function execute()
    {
        $postdata = $this->getRequest()->getParams();
        $result = [
            // 'city' => $this->helper->getCityJson(),
            'township' => $this->helper->getTownshipDataProvider()
        ];
        $json = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        return $json->setData($result);
    }
}
