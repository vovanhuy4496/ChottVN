<?php
declare(strict_types=1);

namespace Chottvn\Finance\Model;

class AccountId extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $_connection;
	protected $_resourceConnection;
    
    /**
     * * @param CollectionFactory $transactionTypeCollectionFactory
     */

    public function __construct(
		\Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
		$this->_resourceConnection = $resourceConnection;
    }

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_connection = $this->_resourceConnection->getConnection();
			$query = "SELECT *  FROM `vw_affiliate_info` WHERE `affiliate_status` IS NOT NULL ORDER BY `entity_id` DESC";

			$queryCollection = $this->_connection->fetchAll($query);
            $setData = [];
            foreach ($queryCollection as $item) {
                $getData = ['value' => $item["entity_id"], 'label' => $item["phone_number"], 'sort' => $item["entity_id"]];
                // var_dump($getData);
                array_push($setData, $getData);
            }
            $this->_options = $setData;
            // var_dump($queryCollection);
        }
        return $this->_options;
    }
}

