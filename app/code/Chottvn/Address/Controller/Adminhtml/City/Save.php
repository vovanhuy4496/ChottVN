<?php

namespace Chottvn\Address\Controller\Adminhtml\City;

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
            if (empty($data['city_id'])) {
                $data['city_id'] = null;
            }

            /** @var \Chottvn\Address\Model\City $city */
            $city = $this->cityFactory->create();

            $city_id = $this->getRequest()->getParam('city_id');
            if ($city_id) {
                try {
                    $this->cityResource->load($city, $city_id);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This city no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $city->setData($data);

            try {
                $this->cityResource->save($city);
                $this->messageManager->addSuccessMessage(__('You saved the city.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['city_id' => $city->getCityId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the city.'));
            }

            $this->_getSession()->setFormData($data);
            if ($this->getRequest()->getParam('city_id')) {
                return $resultRedirect->setPath('*/*/edit', ['city_id' => $city_id]);
            }
            return $resultRedirect->setPath('*/*/new');
        }
        return $resultRedirect->setPath('*/*/');
    }
}
