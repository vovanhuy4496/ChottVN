<?php

/**
 * Copyright (c) 2019 2020 ChottVN
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Chottvn\Inventory\Model;

use Chottvn\Inventory\Api\TempImportRepositoryInterface;

class TempImportRepository implements TempImportRepositoryInterface
{

	protected $_tempImportFactory;

	protected $_resource;

	protected $_encryptor;

	protected $_smsConfigs;

	public function __construct(
		\Chottvn\Inventory\Model\TempImportFactory $TempImportFactory,
		\Chottvn\Inventory\Model\ResourceModel\TempImport $resource,
		\Magento\Framework\Encryption\EncryptorInterface $encryptor
	) {
		$this->_encryptor = $encryptor;
		$this->_tempImportFactory = $TempImportFactory;
		$this->_resource = $resource;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function index() {
		
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function saveTempImport($data, $importType) {
		// $this->writeLog('func:saveTempImport');
		// $this->writeLog('func:saveTempImport - params: '.$data);

		$data = json_decode($data);
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$itemTempImport = $objectManager->create('Chottvn\Inventory\Model\TempImport');
		// if ($importType == 'price') {
		// 	$itemTempImport->setData([
		// 		"product_code" => $data->product_code,
		// 		"product_price" => $data->product_price
		// 	]);
		// } else {
		// 	$itemTempImport->setData([
		// 		"product_code" => $data->product_code,
		// 		"qty_saleable" => $data->qty_saleable
		// 	]);
		// }
		switch ($importType) {
			case "price":
				$itemTempImport->setData([
					"product_code" => $data->product_code,
					"product_price" => $data->product_price,
					"product_model" => $data->product_model,
					"product_brand" => $data->product_brand
				]);
				break;
			case "quantity":
				$itemTempImport->setData([
					"product_code" => $data->product_code,
					"qty_saleable" => $data->qty_saleable,
					"product_model" => $data->product_model,
					"product_brand" => $data->product_brand
				]);
				break;
			case "weight":
				$itemTempImport->setData([
					"product_code" => $data->product_code,
					"product_weight" => $data->product_weight,
					"product_model" => $data->product_model,
					"product_brand" => $data->product_brand,
					"product_weight_net" => $data->product_weight_net,
					"product_weight_gross" => $data->product_weight_gross
				]);
				break;
		}

		// $this->writeLog('func:saveTempImport - entity: '.json_encode($itemTempImport));
		$itemTempImport->save();
	}

	/**
	 * @param $info
	 * @param $type  [error, warning, info]
	 * @return 
	 */
	private function writeLog($info, $type = "info")
	{
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/temp_import.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		switch ($type) {
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
