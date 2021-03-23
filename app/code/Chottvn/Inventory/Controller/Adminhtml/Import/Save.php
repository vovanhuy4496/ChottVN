<?php

/**
 * Copyright © © 2020 chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Chottvn\Inventory\Controller\Adminhtml\Import;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Save extends \Magento\Backend\App\Action
{
    public $date;

    protected $tempImportRepository;
    protected $resultPageFactory;
    protected $logRepository;
    protected $uploaderFactory;
    protected $directoryList;
    protected $csv;
    protected $eavConfig;
    protected $_scopeConfig;
	protected $_logFactory;

    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $indexerFactory;


    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Chottvn\Inventory\Api\LogRepositoryInterface $logRepository,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\File\Csv $csv,
        \Chottvn\Inventory\Api\TempImportRepositoryInterface $tempImportRepository,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Chottvn\Inventory\Model\LogFactory $logFactory,
        DateTime $date
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->logRepository = $logRepository;
        $this->uploaderFactory = $uploaderFactory;
        $this->directoryList = $directoryList;
        $this->csv = $csv;
        $this->tempImportRepository = $tempImportRepository;
        $this->eavConfig = $eavConfig;
        $this->_scopeConfig = $scopeConfig;
        $this->indexerFactory = $indexerFactory;
        $this->_logFactory = $logFactory;
        $this->date = $date;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        $distributorId = $this->getRequest()->getParam('distributor_id');
        if (!$formKeyIsValid || !$isPost || empty($distributorId)) {
            $this->messageManager->addError(__('Bad Request'));
            return $resultRedirect->setPath(
                '*/*/new',
                ['_current' => true]
            );
        }
        // Check have processing process
        if($this->isProcessing()){
            $this->messageManager->addError(__('There is import process running'));
            return $resultRedirect->setPath(
                '*/*/new',
                ['_current' => true]
            );
        }
        $importType = $this->getRequest()->getParam('import_type');

        // Save log
        $log = $this->saveLog([
            'log_type' => 'import',
            'distributor_id' => $distributorId,
            'status' => 'processing',
            'feature_type' => $importType
        ]);

        try {

            // Get all files uploading
            $files = $this->getRequest()->getFiles();

            if (!isset($files["import_file"]))
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
            $full_name = null;
            if (isset($_FILES)) {
                if ($_FILES['import_file']['name']) {
                    $date = $this->date->date();
                    $date = str_replace('-', '', $date);
                    $date = str_replace(':', '', $date);
                    $date = str_replace(' ', '_', $date);

                    $file_name = $_FILES['import_file']['name'];
                    $get_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $file_name = str_replace('.'.$get_ext, '', $file_name);

                    $full_name = $file_name.'_'.$date.'.'.$get_ext;
                    // $this->writeLog($full_name);
                }
            }
            // Upload file
            $resultUploadFile = $this->uploadFile($files["import_file"], $full_name);
            // $this->writeLog($resultUploadFile);
            // Save log
            $csvData = $this->csv->getData($resultUploadFile["path"] . '/' . $resultUploadFile["file"]);
            $log = $this->saveLog([
                'file_name' => $resultUploadFile["file"],
                'file_row_count' => count($csvData)
            ], $log);
            // Insert to tmp table
            // $this->writeLog($importType);
            // if ($importType == 'price') {
            //     $this->updateInventoryPrice($resultUploadFile, $distributorId, $importType);
            // } else {
            //     $this->updateInventoryQty($resultUploadFile, $distributorId, $importType);
            // }

            switch ($importType) {
                case "price":
                    $this->updateInventoryPrice($resultUploadFile, $distributorId, $importType);
                    break;
                case "quantity":
                    $this->updateInventoryQty($resultUploadFile, $distributorId, $importType);
                    break;
                case "weight":
                    $this->updateInventoryWeight($resultUploadFile, $distributorId, $importType);
                    break;
                default:
                    $this->messageManager->addError(__('Bad Request'));
                    return $resultRedirect->setPath(
                        '*/*/new',
                        ['_current' => true]
                    );
            }

            // Save log
            $log = $this->saveLog([
                'status' => 'success'
            ], $log);

            return $this->resultPageFactory->create();
        }catch(\Exception $e){
            // Save log
            $log = $this->saveLog([
                'status' => 'error'
            ], $log);

            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath(
                '*/*/new',
                ['_current' => true]
            );
        }        
    }

    private function updateInventoryPrice($file, $distributorId, $importType)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        // Truncate Tmp Table        
        $queryTruncate  = "TRUNCATE chottvn_inventory_temp_import";
        $connection->exec($queryTruncate);

        // Load CSV to Tmp Table
        $csvData = $this->csv->getData($file["path"] . '/' . $file["file"]);
        // $getTitle = explode('|', $csvData[0][0]);

        // if (!in_array("product_code", $getTitle) || !in_array("product_price", $getTitle)) {
        if (!in_array("product_code", $csvData[0]) || !in_array("product_price", $csvData[0])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file format attempt.'));
        }else{
            $colProductCode = array_search('product_code', $csvData[0]);
            $colProductPrice = array_search('product_price', $csvData[0]);
            $colProductModel = array_search('product_model', $csvData[0]);
            $colProductBrand = array_search('product_brand', $csvData[0]);

            // $colProductCode = array_search('product_code', $getTitle);
            // $colProductPrice = array_search('product_price', $getTitle);
            foreach ($csvData as $row => $value) {
                if ($row > 0) {
                    // $getValue = explode('|', $value[0]);
                    $data = [
                        "product_code" => $value[$colProductCode],
                        "product_price" => intval($value[$colProductPrice]),
                        "product_model" => $value[$colProductModel],
                        "product_brand" => $value[$colProductBrand]

                        // "product_code" => $getValue[$colProductCode],
                        // "product_price" => intval($getValue[$colProductPrice])
                    ];
                    // $this->writeLog($data);
    
                    $this->tempImportRepository->saveTempImport(json_encode($data), $importType);
                }
            }
        }
        $entityAttribute = $objectManager->get('Magento\Eav\Model\ResourceModel\Entity\Attribute');
        $attributeId_price = $entityAttribute->getIdByCode('catalog_product', 'price');

        // Update Price
        $queryUpdatePrice  = "UPDATE catalog_product_entity_decimal cped
            LEFT JOIN vw_chottvn_grid_catalog_inventory cgci ON cped.entity_id = cgci.product_id
            LEFT JOIN chottvn_inventory_temp_import citi ON cgci.product_code = citi.product_code
            SET cped.value = citi.product_price
            WHERE cgci.product_distributor_id = ". $distributorId ." AND 
                cgci.product_brand = citi.product_brand AND 
                cgci.product_model = citi.product_model AND 
                cped.attribute_id = $attributeId_price AND 
                cgci.product_code = citi.product_code";
        // $this->writeLog($queryUpdatePrice);
        $connection->exec($queryUpdatePrice);

        // Reindex
        $this->reindex();
    }

    private function updateInventoryWeight($file, $distributorId, $importType)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        // Truncate Tmp Table        
        $queryTruncate  = "TRUNCATE chottvn_inventory_temp_import";
        $connection->exec($queryTruncate);

        // Load CSV to Tmp Table
        $csvData = $this->csv->getData($file["path"] . '/' . $file["file"]);

        if (!in_array("product_code", $csvData[0]) || 
            ( !in_array("product_weight", $csvData[0])
                && !in_array("product_weight_net", $csvData[0])
                && !in_array("product_weight_gross", $csvData[0])
            )   
         ) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file format attempt.'));
        }else{
            $colProductCode = array_search('product_code', $csvData[0]);
            $colProductWeight = array_search('product_weight', $csvData[0]);
            $colProductModel = array_search('product_model', $csvData[0]);
            $colProductBrand = array_search('product_brand', $csvData[0]);
            $colProductWeightNet = array_search('product_weight_net', $csvData[0]);
            $colProductWeightGross = array_search('product_weight_gross', $csvData[0]);

            foreach ($csvData as $row => $value) {
                if ($row > 0) {

                    $data = [
                        "product_code" => $value[$colProductCode],
                        "product_weight" => $colProductWeight ? intval($value[$colProductWeight]) : null,
                        "product_model" => $value[$colProductModel],
                        "product_brand" => $value[$colProductBrand],
                        "product_weight_net" => $colProductWeightNet ? $value[$colProductWeightNet] : null,
                        "product_weight_gross" => $colProductWeightGross ? $value[$colProductWeightGross] : null
                    ];
    
                    $this->tempImportRepository->saveTempImport(json_encode($data), $importType);
                }
            }
        }
        $entityAttribute = $objectManager->get('Magento\Eav\Model\ResourceModel\Entity\Attribute');
        

        // Update Weight
        $attributeIdWeight = $entityAttribute->getIdByCode('catalog_product', 'weight');
        $queryUpdateWeight  = "UPDATE catalog_product_entity_decimal cped
            LEFT JOIN vw_chottvn_grid_catalog_inventory cgci ON cped.entity_id = cgci.product_id
            LEFT JOIN chottvn_inventory_temp_import citi ON cgci.product_code = citi.product_code
            SET cped.value = citi.product_weight
            WHERE citi.product_weight IS NOT NULL AND 
                cgci.product_distributor_id = ". $distributorId ." AND 
                cped.attribute_id = $attributeIdWeight AND 
                cgci.product_brand = citi.product_brand AND 
                cgci.product_model = citi.product_model AND 
                cgci.product_code = citi.product_code";
            // $this->writeLog($queryUpdateWeight);
        $connection->exec($queryUpdateWeight);

        // Update Weight Net
        $attributeIdWeightNet = $entityAttribute->getIdByCode('catalog_product', 'net_weight');
        $queryUpdateWeightNet  = "UPDATE catalog_product_entity_varchar cpev
            LEFT JOIN vw_chottvn_grid_catalog_inventory cgci ON cpev.entity_id = cgci.product_id
            LEFT JOIN chottvn_inventory_temp_import citi ON cgci.product_code = citi.product_code
            SET cpev.value = citi.product_weight_net
            WHERE citi.product_weight_net IS NOT NULL AND 
                LENGTH(citi.product_weight_net) > 0 AND 
                cgci.product_distributor_id = ". $distributorId ." AND 
                cpev.attribute_id = $attributeIdWeightNet AND 
                cgci.product_brand = citi.product_brand AND 
                cgci.product_model = citi.product_model AND 
                cgci.product_code = citi.product_code";
            // $this->writeLog($queryUpdateWeightNet);
        $connection->exec($queryUpdateWeightNet);

        // Update Weight Gross
        $attributeIdWeightNet = $entityAttribute->getIdByCode('catalog_product', 'gross_weight');
        $queryUpdateWeightNet  = "UPDATE catalog_product_entity_varchar cpev
            LEFT JOIN vw_chottvn_grid_catalog_inventory cgci ON cpev.entity_id = cgci.product_id
            LEFT JOIN chottvn_inventory_temp_import citi ON cgci.product_code = citi.product_code
            SET cpev.value = citi.product_weight_gross
            WHERE citi.product_weight_gross IS NOT NULL AND 
                LENGTH(citi.product_weight_gross) > 0 AND 
                cgci.product_distributor_id = ". $distributorId ." AND 
                cpev.attribute_id = $attributeIdWeightNet AND 
                cgci.product_brand = citi.product_brand AND 
                cgci.product_model = citi.product_model AND 
                cgci.product_code = citi.product_code";
            // $this->writeLog($queryUpdateWeightNet);
        $connection->exec($queryUpdateWeightNet);

        // Reindex
        $this->reindex();
    }

    private function updateInventoryQty($file, $distributorId, $importType)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        // Truncate Tmp Table        
        $queryTruncate  = "TRUNCATE chottvn_inventory_temp_import";
        $connection->exec($queryTruncate);

        // Load CSV to Tmp Table
        $csvData = $this->csv->getData($file["path"] . '/' . $file["file"]);
        // $getTitle = explode('|', $csvData[0][0]);

        // if (!in_array("product_code", $getTitle) || !in_array("qty_saleable", $getTitle)) {
        if (!in_array("product_code", $csvData[0]) || !in_array("qty_saleable", $csvData[0])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file format attempt.'));
        }else{
            $colProductCode = array_search('product_code', $csvData[0]);
            $colQtySaleable = array_search('qty_saleable', $csvData[0]);
            $colProductModel = array_search('product_model', $csvData[0]);
            $colProductBrand = array_search('product_brand', $csvData[0]);

            // $colProductCode = array_search('product_code', $getTitle);
            // $colQtySaleable = array_search('qty_saleable', $getTitle);
            foreach ($csvData as $row => $value) {
                if ($row > 0) {
                    // $getValue = explode('|', $value[0]);
                    $data = [
                        "product_code" => $value[$colProductCode],
                        "qty_saleable" => intval($value[$colQtySaleable]),
                        "product_model" => $value[$colProductModel],
                        "product_brand" => $value[$colProductBrand]
                        // "product_code" => $getValue[$colProductCode],
                        // "qty_saleable" => intval($getValue[$colQtySaleable])
                    ];
    
                    $this->tempImportRepository->saveTempImport(json_encode($data), $importType);
                }
            }
        }

        // Update Qty
        $queryUpdateQty  = "UPDATE cataloginventory_stock_item csi
            LEFT JOIN vw_chottvn_grid_catalog_inventory cgci ON  csi.product_id = cgci.product_id
            LEFT JOIN chottvn_inventory_temp_import citi ON cgci.product_code = citi.product_code
            SET csi.qty = cgci.qty_on_selling + COALESCE(citi.qty_saleable, ".$this->getMaxDefaultStock().")
            WHERE cgci.product_distributor_id = ". $distributorId ." AND 
                cgci.product_brand = citi.product_brand AND 
                cgci.product_model = citi.product_model AND 
                cgci.product_code = citi.product_code";
        $connection->exec($queryUpdateQty);

        // Reindex
        $this->reindex();
    }

    private function reindex() {
        /*
        'catalog_category_product',
        'catalog_product_category',
        'catalog_product_price',
        'catalog_product_attribute',
        'cataloginventory_stock',
        'catalogrule_product',
        'catalogsearch_fulltext',
        */
        $indexerIds = array(
            'cataloginventory_stock'
        );
        foreach ($indexerIds as $indexerId) {            
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexerId);
            $indexer->reindexAll();
        }
    }

    private function getMaxDefaultStock()
    {
        return $this->_scopeConfig->getValue('cataloginventory/item_options/default_stock_max');
    }


    private function uploadFile($file, $full_name)
    {
        $filePathSub = 'chottvn_inventory/import';
        $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            . "/" . $filePathSub;
        $uploader = $this->uploaderFactory->create(['fileId' => $file]);
        $uploader->setFilesDispersion(false);
        $uploader->setFilenamesCaseSensitivity(true);
        $uploader->setAllowRenameFiles(true);
        if (isset($full_name)) {
            return $uploader->save($filePath, $full_name);
        }
        return $uploader->save($filePath);
    }

    private function saveLog($data = [], $log = null)
    {
        // Create new log
        if(!$log) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $log = $objectManager->create('Chottvn\Inventory\Api\Data\LogInterface');

            /*Get attribute details*/
            $distributorName = "";
            $distributors = $this->eavConfig->getAttribute("catalog_product", 'distributor')->getSource()->getAllOptions();
            foreach ($distributors as $distributor) {
                if ($distributor['value'] == $data['distributor_id']) {                
                    $distributorName = $distributor['label'];
                    break;
                }
            }
            $details = [
                "distributor_id" => $data['distributor_id'],
                "distributor_name" => $distributorName
            ];
            $log->setData('details', json_encode($details));

            $currentUser = $objectManager->get('Magento\Backend\Model\Auth\Session')->getUser();
            $log->setData('user_id', $currentUser->getId());
            $log->setData('log_type', $data['log_type']);
            $log->setData('status', $data['status']);
            $log->setData('feature_type', $data['feature_type']);
            $log->setData('created_at', $this->date->date());
            
            $log = $this->logRepository->saveAndGetModel($log);
            return $log;
        }

        // Update existed log
        if(isset($data['file_name'])){
            $log->setData('file_name', $data['file_name']);
        }

        if(isset($data['file_row_count'])){
            $log->setData('file_row_count', $data['file_row_count']);
        }

        if(isset($data['status'])){
            $log->setData('status', $data['status']);
        }

        $log->save();

        // $this->logRepository->save($log);
        return $log;
    }

    private function isProcessing() {
		$log = $this->_logFactory->create();
		$collection = $log->getCollection()
            ->addFieldToFilter('log_type', 'import')
			->addFieldToFilter('status', 'processing')
			->setOrder('log_id', 'ASC');
		$itemLog = $collection->getLastItem();

		if ($itemLog->getId()) {
			return true;
        }
        
        return false;
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Import_Distributor_Data.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        switch($type){
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
