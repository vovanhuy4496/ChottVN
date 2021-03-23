<?php
declare(strict_types=1);

namespace Chottvn\Notification\Controller\Adminhtml\MessageType;

class InlineEdit extends \Magento\Backend\App\Action
{

    protected $jsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Inline edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        
        if ($this->getRequest()->getParam('isAjax')) {
            $messageItems = $this->getRequest()->getParam('items', []);
            if (!count($messageItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($messageItems) as $modelid) {
                    /** @var \Chottvn\Notification\Model\MessageType $model */
                    $model = $this->_objectManager->create(\Chottvn\Notification\Model\MessageType::class)->load($modelid);
                    try {
                        $model->setData(array_merge($model->getData(), $messageItems[$modelid]));
                        $model->save();
                    } catch (\Exception $e) {
                        $messages[] = "[MessageType ID: {$modelid}]  {$e->getMessage()}";
                        $error = true;
                    }
                }
            }
        }
        
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}

