<?php
/**
 * Copyright (c) 2019 ChottVN
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

namespace Chottvn\Inventory\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 *
 * @package Chottvn\Inventory\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), "0.2.9", "<")) {
            $this->runOnVer029($setup);
        }

        $setup->endSetup();
    }

    protected function runOnVer029($setup){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $entityAttribute = $objectManager->get('Magento\Eav\Model\ResourceModel\Entity\Attribute');

        $attributeId_price = $entityAttribute->getIdByCode('catalog_product', 'price');
        $attributeId_model = $entityAttribute->getIdByCode('catalog_product', 'model');
        $attributeId_distributor = $entityAttribute->getIdByCode('catalog_product', 'distributor');
        $attributeId_product_code_distributor = $entityAttribute->getIdByCode('catalog_product', 'product_code_distributor');
        $attributeId_product_name_distributor = $entityAttribute->getIdByCode('catalog_product', 'product_name_distributor');
        $attributeIdWeight = $entityAttribute->getIdByCode('catalog_product', 'weight');
        $attributeIdWeightNet  = $entityAttribute->getIdByCode('catalog_product', 'net_weight');
        $attributeIdWeightGross = $entityAttribute->getIdByCode('catalog_product', 'gross_weight');
        $attributeId_brand = $entityAttribute->getIdByCode('catalog_product', 'product_brand');

        // $this->writeLog($attributeId_brand);
        // $this->writeLog($attributeId_model);

        /**
        * create view product_distributor
        */
        $createViewProductNameDistributorSql = "CREATE OR REPLACE VIEW vw_chottvn_product_distributor
            AS (
                SELECT entity_id AS product_id,
                    value AS product_distributor_id
                FROM catalog_product_entity_int
                WHERE attribute_id = $attributeId_distributor
            );";
        $setup->run($createViewProductNameDistributorSql);

        /**
        * create view product_name_distributor
        */
        $createViewProductNameDistributorSql = "CREATE OR REPLACE VIEW vw_chottvn_product_name_distributor AS ( SELECT entity_id AS product_id, value AS product_name FROM catalog_product_entity_varchar WHERE attribute_id = $attributeId_product_name_distributor );";
        $setup->run($createViewProductNameDistributorSql);
        
        /**
        * create view product_code_distributor
        */
        $createViewProductCodeDistributorSql = "CREATE OR REPLACE VIEW vw_chottvn_product_code_distributor AS ( SELECT entity_id AS product_id, value AS product_code FROM catalog_product_entity_varchar WHERE attribute_id = $attributeId_product_code_distributor );";
        $setup->run($createViewProductCodeDistributorSql);
        
        /**
        * create view product_model
        */
        $createViewProductModelSql = "CREATE OR REPLACE VIEW vw_chottvn_product_model AS ( SELECT entity_id AS product_id, value AS product_model FROM catalog_product_entity_varchar WHERE attribute_id = $attributeId_model );";
        $setup->run($createViewProductModelSql);

        /**
        * create view product_brand
        */
        $createViewProductBrandSql = "CREATE OR REPLACE VIEW vw_chottvn_product_brand
        AS (
            SELECT cpei.entity_id AS product_id,
                cpei.value AS product_brand_id,
                vb.name AS product_brand_name
            FROM catalog_product_entity_int as cpei
            JOIN ves_brand vb ON vb.brand_id = cpei.value
            WHERE attribute_id = $attributeId_brand AND vb.status = 1
        );";
        $setup->run($createViewProductBrandSql);

        /**
        * create view product_price
        */
        $createViewProductPriceSql = "CREATE OR REPLACE VIEW vw_chottvn_product_price AS ( SELECT entity_id AS product_id, value AS product_price FROM catalog_product_entity_decimal WHERE attribute_id = $attributeId_price );";
        $setup->run($createViewProductPriceSql);

        /**
        * create view product_weight
        */
        $createViewProductWeightSql = "CREATE OR REPLACE VIEW vw_chottvn_product_weight AS ( SELECT entity_id AS product_id, value AS product_weight FROM catalog_product_entity_decimal WHERE attribute_id = $attributeIdWeight );";
        $setup->run($createViewProductWeightSql);

        $createViewProductWeightNetSql = "CREATE OR REPLACE VIEW vw_chottvn_product_weight_net AS ( SELECT entity_id AS product_id, value AS product_weight_net FROM catalog_product_entity_varchar WHERE attribute_id = $attributeIdWeightNet );";
        $setup->run($createViewProductWeightNetSql);

        $createViewProductWeightGrossSql = "CREATE OR REPLACE VIEW vw_chottvn_product_weight_gross AS ( SELECT entity_id AS product_id, value AS product_weight_gross FROM catalog_product_entity_varchar WHERE attribute_id = $attributeIdWeightGross );";
        $setup->run($createViewProductWeightGrossSql);
        
        /**
        * create view grid_sale_product
        */
        $createViewGridSaleProductSql = "CREATE OR REPLACE VIEW vw_grid_sale_product AS ( SELECT itm.product_id AS product_id , sum(itm.qty_ordered) - sum(itm.qty_shipped) as qty_on_selling FROM sales_order ord JOIN  sales_order_item itm on (ord.entity_id = itm.order_id) WHERE ord.status not in ('canceled', 'complete') GROUP BY itm.product_id );";
        $setup->run($createViewGridSaleProductSql);
        
        /**
        * create view grid_catalog_inventory
        */
        $createViewGridCatalogInventorySql = "CREATE OR REPLACE VIEW vw_chottvn_grid_catalog_inventory
            AS (
                SELECT cpe.entity_id AS product_id, 
                    cpe.sku AS sku, 
                    vcpnd.product_name AS product_name,
                vcpcd.product_code AS product_code,
                vcpm.product_model AS product_model,
                vcpb.product_brand_id AS product_brand_id,
                vcpb.product_brand_name AS product_brand,
                vcpd.product_distributor_id AS product_distributor_id,
                    csi.qty AS qty_current, 
                COALESCE(vgsp.qty_on_selling, 0)  AS qty_on_selling, 
                csi.qty  -  COALESCE(vgsp.qty_on_selling, 0) AS qty_saleable,
                    css.stock_status AS stock_status,
                    vcpp.product_price AS product_price,
                    vcpw.product_weight AS product_weight,
                    vcpwn.product_weight_net AS product_weight_net,
                    vcpwg.product_weight_gross AS product_weight_gross
                FROM catalog_product_entity cpe
                JOIN cataloginventory_stock_item  csi on cpe.entity_id = csi.product_id
                JOIN cataloginventory_stock_status css on cpe.entity_id = css.product_id
                LEFT JOIN vw_chottvn_product_distributor vcpd ON cpe.entity_id = vcpd.product_id
              LEFT JOIN vw_chottvn_product_name_distributor  vcpnd ON cpe.entity_id = vcpnd.product_id
              LEFT JOIN vw_chottvn_product_code_distributor vcpcd ON cpe.entity_id = vcpcd.product_id
              LEFT JOIN vw_chottvn_product_model  vcpm ON cpe.entity_id = vcpm.product_id
              LEFT JOIN vw_chottvn_product_brand  vcpb ON cpe.entity_id = vcpb.product_id
              LEFT JOIN vw_chottvn_product_price  vcpp ON cpe.entity_id = vcpp.product_id
              LEFT JOIN vw_chottvn_product_weight  vcpw ON cpe.entity_id = vcpw.product_id
              LEFT JOIN vw_chottvn_product_weight_net  vcpwn ON cpe.entity_id = vcpwn.product_id
              LEFT JOIN vw_chottvn_product_weight_gross vcpwg ON cpe.entity_id = vcpwg.product_id
              LEFT JOIN vw_grid_sale_product vgsp ON cpe.entity_id = vgsp.product_id 
                WHERE type_id = 'simple'
            );";
        // $this->writeLog($createViewGridCatalogInventorySql);
        $setup->run($createViewGridCatalogInventorySql);
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

