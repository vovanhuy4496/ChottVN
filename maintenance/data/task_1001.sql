-- View product_distributor
-- SELECT *
-- FROM eav_attribute
-- WHERE attribute_code = 'distributor'
CREATE OR REPLACE VIEW vw_chottvn_product_distributor
AS (
	SELECT entity_id AS product_id,
		value AS product_distributor_id
	FROM catalog_product_entity_int
	WHERE attribute_id = 249
);

-- View product_name_distributor
-- SELECT *
-- FROM eav_attribute
-- WHERE attribute_code = 'product_name_distributor'
CREATE OR REPLACE VIEW vw_chottvn_product_name_distributor
AS (
	SELECT entity_id AS product_id,
		value AS product_name
	FROM catalog_product_entity_varchar
	WHERE attribute_id = 402
);

-- View product_code_distributor
-- SELECT *
-- FROM eav_attribute
-- WHERE attribute_code = 'product_code_distributor'
CREATE OR REPLACE VIEW vw_chottvn_product_code_distributor
AS (
	SELECT entity_id AS product_id,
		value AS product_code
	FROM catalog_product_entity_varchar
	WHERE attribute_id = 250
);

-- View product_model
-- SELECT *
-- FROM eav_attribute
-- WHERE attribute_code = 'mode'
CREATE OR REPLACE VIEW vw_chottvn_product_model
AS (
	SELECT entity_id AS product_id,
		value AS product_model
	FROM catalog_product_entity_varchar
	WHERE attribute_id = 244
);

-- View grid_sale_product
CREATE OR REPLACE VIEW vw_grid_sale_product
AS (
SELECT itm.product_id AS product_id
	, sum(itm.qty_ordered) - sum(itm.qty_shipped) as qty_on_selling
FROM sales_order ord
JOIN  sales_order_item itm on (ord.entity_id = itm.order_id)
WHERE ord.status not in ('canceled', 'complete')
GROUP BY itm.product_id
);

-- View grid_catalog_inventory
CREATE OR REPLACE VIEW vw_chottvn_grid_catalog_inventory
AS (
	SELECT cpe.entity_id AS product_id, 
		cpe.sku AS sku, 
		vcpnd.product_name AS product_name,
    vcpcd.product_code AS product_code,
    vcpm.product_model AS product_model,	
    vcpd.product_distributor_id AS product_distributor_id,
		csi.qty AS qty_current, 
    COALESCE(vgsp.qty_on_selling, 0)  AS qty_on_selling, 
    csi.qty  -  COALESCE(vgsp.qty_on_selling, 0) AS qty_saleable,
		css.stock_status AS stock_status
	FROM catalog_product_entity cpe
	JOIN cataloginventory_stock_item  csi on cpe.entity_id = csi.product_id
	JOIN cataloginventory_stock_status css on cpe.entity_id = css.product_id
	LEFT JOIN vw_chottvn_product_distributor vcpd ON cpe.entity_id = vcpd.product_id
  LEFT JOIN vw_chottvn_product_name_distributor  vcpnd ON cpe.entity_id = vcpnd.product_id
  LEFT JOIN vw_chottvn_product_code_distributor vcpcd ON cpe.entity_id = vcpcd.product_id
  LEFT JOIN vw_chottvn_product_model  vcpm ON cpe.entity_id = vcpm.product_id
  LEFT JOIN vw_grid_sale_product vgsp ON cpe.entity_id = vgsp.product_id 
	WHERE type_id = 'simple'
);

-- ----------------------------
-- Table structure for chottvn_inventory_temp_import
-- ----------------------------
DROP TABLE IF EXISTS `chottvn_inventory_temp_import`;
CREATE TABLE `chottvn_inventory_temp_import` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_code` varchar(255) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `qty_saleable` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;