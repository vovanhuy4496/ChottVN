<?php
declare(strict_types=1);

namespace Chottvn\Notification\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface MessageTypeRepositoryInterface
{

    /**
     * Save MessageType
     * @param \Chottvn\Notification\Api\Data\MessageTypeInterface $messageType
     * @return \Chottvn\Notification\Api\Data\MessageTypeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Chottvn\Notification\Api\Data\MessageTypeInterface $messageType
    );

    /**
     * Retrieve MessageType
     * @param string $id
     * @return \Chottvn\Notification\Api\Data\MessageTypeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($id);

    /**
     * Retrieve MessageType matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Chottvn\Notification\Api\Data\MessageTypeSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete MessageType
     * @param \Chottvn\Notification\Api\Data\MessageTypeInterface $messageType
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Chottvn\Notification\Api\Data\MessageTypeInterface $messageType
    );

    /**
     * Delete MessageType by ID
     * @param string $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}

