BEGIN;
-- City: Same Name but difference entity
UPDATE `directory_region_city` SET `default_name` = 'Ky Anh (H)' WHERE `directory_region_city`.`city_id` = 328; 
UPDATE `directory_region_city` SET `default_name` = 'Ky Anh (TX)' WHERE `directory_region_city`.`city_id` = 330; 
UPDATE `directory_region_city` SET `default_name` = 'Cai Lay (TX)' WHERE `directory_region_city`.`city_id` = 595; 
UPDATE `directory_region_city` SET `default_name` = 'Cai Lay (H)' WHERE `directory_region_city`.`city_id` = 598; 
UPDATE `directory_region_city` SET `default_name` = 'Duyen Hai (H)' WHERE `directory_region_city`.`city_id` = 620; 
UPDATE `directory_region_city` SET `default_name` = 'Duyen Hai (TX)' WHERE `directory_region_city`.`city_id` = 621; 
UPDATE `directory_region_city` SET `default_name` = 'Hong Ngu (TX)' WHERE `directory_region_city`.`city_id` = 632; 
UPDATE `directory_region_city` SET `default_name` = 'Hong Ngu (H)' WHERE `directory_region_city`.`city_id` = 634; 
UPDATE `directory_region_city` SET `default_name` = 'Long My (H)' WHERE `directory_region_city`.`city_id` = 683; 
UPDATE `directory_region_city` SET `default_name` = 'Long My (TX)' WHERE `directory_region_city`.`city_id` = 684;
-- City: Correct Name
UPDATE `directory_region_city` SET `default_name` = 'Quan Ba' WHERE `directory_region_city`.`city_id` = 35; 
UPDATE `directory_region_city` SET `default_name` = 'Quang Hoa' WHERE `directory_region_city`.`city_id` = 50; 
UPDATE `directory_region_city` SET `default_name` = 'Bach Long Vy' WHERE `directory_region_city`.`city_id` = 227; 
UPDATE `directory_region_city` SET `default_name` = 'Quan Hoa' WHERE `directory_region_city`.`city_id` = 274; 
UPDATE `directory_region_city` SET `default_name` = 'Quan Son' WHERE `directory_region_city`.`city_id` = 276; 
UPDATE `directory_region_city` SET `default_name` = 'Quy Nhon' WHERE `directory_region_city`.`city_id` = 398; 
UPDATE `directory_region_city` SET `default_name` = 'Phu Quy' WHERE `directory_region_city`.`city_id` = 443; 
UPDATE `directory_region_city` SET `default_name` = 'Ia H\'Drai' WHERE `directory_region_city`.`city_id` = 453; 
UPDATE `directory_region_city` SET `default_name` = 'Ea H\'leo' WHERE `directory_region_city`.`city_id` = 473; 
UPDATE `directory_region_city` SET `default_name` = 'Cu M\'gar' WHERE `directory_region_city`.`city_id` = 476; 
UPDATE `directory_region_city` SET `default_name` = 'M\'drak' WHERE `directory_region_city`.`city_id` = 480; 
UPDATE `directory_region_city` SET `default_name` = 'Krong Pak' WHERE `directory_region_city`.`city_id` = 482; 
UPDATE `directory_region_city` SET `default_name` = 'Krong Ana' WHERE `directory_region_city`.`city_id` = 483; 
UPDATE `directory_region_city` SET `default_name` = 'Dak Rlap' WHERE `directory_region_city`.`city_id` = 492; 

COMMIT;