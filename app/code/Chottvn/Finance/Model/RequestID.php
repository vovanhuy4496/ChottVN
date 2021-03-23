<?php
declare(strict_types=1);

namespace Chottvn\Finance\Model;

class RequestID extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
			$query = "SELECT *  FROM `chottvn_finance_request` ORDER BY `request_id` DESC";

			$queryCollection = $this->_connection->fetchAll($query);
            $setData = [];
            foreach ($queryCollection as $item) {
                $getData = ['value' => $item["request_id"], 'label' => $item["request_id"], 'sort' => $item["request_id"]];
                // var_dump($getData);
                array_push($setData, $getData);
            }
            $this->_options = $setData;
            // var_dump($queryCollection);
        }
        return $this->_options;
    }
}

