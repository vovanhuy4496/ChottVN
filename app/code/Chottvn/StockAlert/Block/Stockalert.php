<?php

namespace Chottvn\StockAlert\Block;

class Stockalert extends \Magento\Framework\View\Element\Template
{
    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    protected $_coreSession;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_coreSession = $coreSession;
    }

    /**
     * Get form action URL for POST booking request
     *
     * @return string
     */
    public function getFormAction()
    {
        return '/stockalert';
    }
    public function getMySession(){
        $this->_coreSession->start();
        return $this->_coreSession->getMessage();
    }
    public function unMySession(){
        $this->_coreSession->start();
        return $this->_coreSession->unsMessage();
    }
}