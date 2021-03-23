<?php
namespace Chottvn\CustomReview\Plugin\Review\Model\ResourceModel;

/**
 * Review resource model
 *
 * @api
 * @since 100.0.2
 */
class Review
{
    protected $_authSession;

    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession
    ) { 
        $this->_authSession = $authSession;
    }
    /**
    * @param \Magento\Review\Model\ResourceModel\Review $subject
    * @param  $result
    * @return mixed
    * @throws \Exception
    */
    public function afterSave(
        \Magento\Review\Model\ResourceModel\Review $subject,
        $result, $object
    ){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $review_id = $object->getReviewId();

        // Get User Admin
        $admin_user = $this->getCurrentUser();

        $updated_by = $admin_user->getLastname().' '.$admin_user->getFirstname();

        // Connection
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');

        // Update data
        $connection = $resource->getConnection();
        $table_name = $resource->getTableName('review');
        $data = ["ctt_updated_by"=> $updated_by];
        $where = ['review_id = ?' => $review_id];
        $updated = $connection->update($table_name, $data, $where);

        return $result;
    }


    public function getCurrentUser()
    {
        return $this->_authSession->getUser();
    }
}
