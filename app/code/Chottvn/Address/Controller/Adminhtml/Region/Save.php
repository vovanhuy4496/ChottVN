<?php

namespace Chottvn\Address\Controller\Adminhtml\Region;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Inspection\Exception;

class Save extends \Chottvn\Address\Controller\Adminhtml\Address
{
    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if (empty($data['region_id'])) {
                $data['region_id'] = null;
            }

            /** @var \Chottvn\Address\Model\Region $region */
            $region = $this->regionFactory->create();

            $region_id = $this->getRequest()->getParam('region_id');
            if ($region_id) {
                try {
                    $this->regionResource->load($region, $region_id);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This region no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $region->setData($data);

            try {
                $this->regionResource->save($region);
                $this->messageManager->addSuccessMessage(__('You saved the region.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['region_id' => $region->getRegionId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the region.'));
            }

            $this->_getSession()->setFormData($data);
            if ($this->getRequest()->getParam('region_id')) {
                return $resultRedirect->setPath('*/*/edit', ['region_id' => $region_id]);
            }
            return $resultRedirect->setPath('*/*/new');
        }
        return $resultRedirect->setPath('*/*/');
    }
}
