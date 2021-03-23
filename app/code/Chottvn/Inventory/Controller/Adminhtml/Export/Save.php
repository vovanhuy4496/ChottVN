<?php

/**
 * Copyright © © 2020 chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Chottvn\Inventory\Controller\Adminhtml\Export;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Save extends \Magento\Backend\App\Action 
{
    public $date;

    protected $resultPageFactory;

    protected $fileFactory;
    protected $csvProcessor;
    protected $directoryList;
    protected $logRepository;
    protected $eavConfig;

    /**
     * Notifier Pool
     *
     * @var NotifierPool
     */
    protected $messageManager;


    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Chottvn\Inventory\Api\LogRepositoryInterface $logRepository,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        DateTime $date
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->fileFactory = $fileFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->logRepository = $logRepository;
        $this->eavConfig = $eavConfig;
        $this->messageManager = $messageManager;
        $this->date = $date;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    // public function execute()
    // {
    //     return $this->resultPageFactory->create();
    // }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__('Bad Request'));
            return $resultRedirect->setPath(
                '*/*/new',
                ['_current' => true]
            );
        }
        try {
            $dateExport = $this->date->date();
            $dateExport = str_replace('-', '', $dateExport);
            $dateExport = str_replace(':', '', $dateExport);
            $dateExport = str_replace(' ', '_', $dateExport);
            $exportType = $this->getRequest()->getParam('export_type');

            $fileName = "inventory_" . $exportType . '_' . $dateExport . '.csv';
            $filePathSub = 'chottvn_inventory/export/' . $fileName;
            $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
                . "/" . $filePathSub;
            switch ($exportType) {
                case "price":
                    // $columnSelect = 'product_price';
                    // $columnQuery = 'FLOOR(product_price ) AS product_price';
                    $columnsSelect = ['product_price'];
                    $columnsQuery = ['FLOOR(product_price ) AS product_price'];
                    break;
                case "quantity":
                    // $columnSelect = 'qty_saleable';
                    // $columnQuery = 'FLOOR(qty_saleable ) AS qty_saleable';
                    $columnsSelect = ['qty_saleable'];
                    $columnsQuery = ['FLOOR(qty_saleable ) AS qty_saleable'];
                    break;
                case "weight":
                    // $columnSelect = 'product_weight';
                    // $columnQuery = 'CAST(product_weight AS decimal(10,2)) AS product_weight';
                    $columnsSelect = ['product_weight','product_weight_net','product_weight_gross'];
                    $columnsQuery = ['CAST(product_weight AS decimal(10,2)) AS product_weight','product_weight_net','product_weight_gross'];
                    break;
                default:
                    $this->messageManager->addError(__('Bad Request'));
                    return $resultRedirect->setPath(
                        '*/*/new',
                        ['_current' => true]
                    );
            }
            
            // $columns = ["product_model", "product_brand", "product_name", "product_code", $columnSelect];            
            // $columnsQuery = ["product_model", "product_brand", "product_name", "product_code", $columnQuery];
            $columns = array_merge(["product_model", "product_brand", "product_name", "product_code"], $columnsSelect );
            $columnsQuery = array_merge(["product_model", "product_brand", "product_name", "product_code"], $columnsQuery );

            $header = array();
            foreach ($columns as $column) {
                $header[$column] = $column;
            }
            $distributorId = $this->getRequest()->getParam('distributor_id');
            $filters = [
                "distributor_id" => $distributorId
            ];
            $data = $this->getExportData($columnsQuery, $filters);
            $exportData = array_merge([0 => $header], $data);
            
            $this->csvProcessor
                ->setDelimiter('|')
                ->setEnclosure('"')
                ->saveData(
                    $filePath,
                    $exportData
                );

            // Save log
            $this->saveLog([
                'log_type' => 'export',
                'file_name' => $fileName,
                'distributor_id' => $distributorId,
                'file_row_count' => count($exportData),
                'status' => 'success',
                'feature_type' => $exportType
            ]);

           
            $this->fileFactory->create(
                $fileName,
                [
                    'type' => "filename",
                    'value' => $filePathSub,
                    'rm' => false,
                ],
                \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                'application/octet-stream'
            );
            
            // Return to log page
            //$this->resultPageFactory->create();  
            $this->messageManager->addSuccess(__("Please check your download folder for export file."));
            $resultRedirect = $this->resultRedirectFactory->create();           
            return $resultRedirect->setPath(
                'chottvn_inventory/log'
            );
        }
        catch(\Exception $e){
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath(
                '*/*/new',
                ['_current' => true]
            );
        }
    }

    protected function _goBack($backUrl = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($backUrl || $backUrl = $this->getBackUrl($this->_redirect->getRefererUrl())) {
            $resultRedirect->setUrl($backUrl);
        }
        return $resultRedirect;
    }

    protected function getExportNewUrl(){
        return "admin_chott/inventory/export/new";
    }

    /**
     * Get resolved back url
     *
     * @param string|null $defaultUrl
     * @return mixed|null|string
     */
    protected function getBackUrl($defaultUrl = null)
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl && $this->_isInternalUrl($returnUrl)) {
            $this->messageManager->getMessages()->clear();
            return $returnUrl;
        }
        return $defaultUrl;
    }

    protected function getExportData($columns, $filters)
    {
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('vw_chottvn_grid_catalog_inventory');
        $select = "SELECT " . implode(", ", $columns) . "  FROM vw_chottvn_grid_catalog_inventory";        

        if(!empty($filters["distributor_id"])){
            $select .= " WHERE product_distributor_id = ". $filters["distributor_id"];
        }
        // $this->writeLog($select);
        
        return $connection->fetchAll($select);
    }

    private function saveLog($data = [])
    {
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
        // $this->writeLog($data['feature_type']);
        $log->setData('log_type', $data['log_type']);
        $log->setData('file_name', $data['file_name']);
        $log->setData('file_row_count', $data['file_row_count']);
        $log->setData('status', $data['status']);
        $log->setData('feature_type', $data['feature_type']);
        $log->setData('details', json_encode($details));
        $log->setData('created_at', $this->date->date());

        $currentUser = $objectManager->get('Magento\Backend\Model\Auth\Session')->getUser();
        $log->setData('user_id', $currentUser->getId());

        $this->logRepository->save($log);
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Export_Distributor_Data.log');
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
