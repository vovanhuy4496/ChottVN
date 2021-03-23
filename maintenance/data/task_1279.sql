-- Update default name
--- region
UPDATE directory_country_region a,  directory_country_region_name b
SET a.default_name = b.name
WHERE b.locale = 'vi_VN' and a.region_id = b.region_id;

--- city
UPDATE directory_region_city a,  directory_region_city_name b
SET a.default_name = b.name
WHERE b.locale = 'vi_VN' and a.city_id = b.city_id;

--- township
UPDATE directory_city_township a,  directory_city_township_name b
SET a.default_name = b.name
WHERE b.locale = 'vi_VN' and a.township_id = b.township_id;


-- Update Phuoc/Xa -- Quan Huyen
--- [CaoBang] huyen Thong Nong
UPDATE directory_city_township
SET city_id = 45
WHERE township_id BETWEEN 814 AND 822;

UPDATE directory_city_township a, directory_region_city b
SET a.postcode  = b.postcode
WHERE a.city_id = b.city_id AND a.township_id BETWEEN 814 AND 822;

--- [CaoBang] huyen Tra Linh
UPDATE directory_city_township
SET city_id = 47
WHERE township_id BETWEEN 835 AND 841;

UPDATE directory_city_township a, directory_region_city b
SET a.postcode  = b.postcode
WHERE a.city_id = b.city_id AND a.township_id BETWEEN 835 AND 841;

--- [BinhDinh] huyen An Lao
UPDATE directory_city_township
SET city_id = 399
WHERE township_id BETWEEN 6881 AND 6890;

UPDATE directory_city_township a, directory_region_city b
SET a.postcode  = b.postcode
WHERE a.city_id = b.city_id AND a.township_id BETWEEN 6881 AND 6890;

--- [LamDong] huyen Bao Lam
UPDATE directory_city_township
SET city_id = 502
WHERE township_id BETWEEN 8135 AND 8148;

UPDATE directory_city_township a, directory_region_city b
SET a.postcode  = b.postcode
WHERE a.city_id = b.city_id AND a.township_id BETWEEN 8135 AND 8148;

--- [DongThap] huyen Tam Nong
UPDATE directory_city_township
SET city_id = 635
WHERE township_id BETWEEN 9826 AND 9837;

UPDATE directory_city_township a, directory_region_city b
SET a.postcode  = b.postcode
WHERE a.city_id = b.city_id AND a.township_id BETWEEN 9826 AND 9837;


--- [AnGiang] huyen Cho Moi
UPDATE directory_city_township
SET city_id = 651
WHERE township_id BETWEEN 10040 AND 10057;

UPDATE directory_city_township a, directory_region_city b
SET a.postcode  = b.postcode
WHERE a.city_id = b.city_id AND a.township_id BETWEEN 10040 AND 10057;

--- [CanTho] huyen Vinh Thanh
UPDATE directory_city_township
SET city_id = 673
WHERE township_id BETWEEN 10262 AND 10272;

UPDATE directory_city_township a, directory_region_city b
SET a.postcode  = b.postcode
WHERE a.city_id = b.city_id AND a.township_id BETWEEN 10262 AND 10272;


--- [CanTho] huyen Phong Dien
UPDATE directory_city_township
SET city_id = 675
WHERE township_id BETWEEN 10283 AND 10289;

UPDATE directory_city_township a, directory_region_city b
SET a.postcode  = b.postcode
WHERE a.city_id = b.city_id AND a.township_id BETWEEN 10283 AND 10289;

---  [CaMau] huyen Phu Tan
UPDATE directory_city_township
SET city_id = 710
WHERE township_id BETWEEN 10636 AND 10644;

UPDATE directory_city_township a, directory_region_city b
SET a.postcode  = b.postcode
WHERE a.city_id = b.city_id AND a.township_id BETWEEN 10636 AND 10644;

--- [HaiPhong] huyen Bach Long Vi
insert into `directory_city_township` (`township_id`, `city_id`, `code`, `default_name`) values(10652,'227','','Xã Bạch Long Vĩ');
insert into `directory_city_township_name` (`locale`, `township_id`, `name`) values('vi_VN',10652,'Xã Bạch Long Vĩ');

--- [QuangTri] huyen Con Co
insert into `directory_city_township` (`township_id`, `city_id`, `code`, `default_name`) values(10653,'348','','Xã Cồn Cỏ');
insert into `directory_city_township_name` (`locale`, `township_id`, `name`) values('vi_VN',10653,'Xã Cồn Cỏ');

--- [BaRiaVungTau] huyen Con Dao
insert into `directory_city_township` (`township_id`, `city_id`, `code`, `default_name`) values(10654,'553','','Xã Côn Đảo');
insert into `directory_city_township_name` (`locale`, `township_id`, `name`) values('vi_VN',10654,'Xã Côn Đảo');

--- [DaNang] huyen Hoang Sa
insert into `directory_city_township` (`township_id`, `city_id`, `code`, `default_name`) values(10655,'365','','Xã Hoàng Sa');
insert into `directory_city_township_name` (`locale`, `township_id`, `name`) values('vi_VN',10655,'Xã Hoàng Sa');

--- [CaoBang] huyen Phuc Hoa
UPDATE directory_region_city
SET default_name = 'Huyện Phục Hòa'
WHERE city_id  = 51;

UPDATE directory_region_city_name
SET name = 'Huyện Phục Hòa'
WHERE city_id  = 51  AND locale='vi_VN';

insert into `directory_city_township` (`township_id`, `city_id`, `code`, `default_name`) values(10656,'51','','Thị trấn Hòa Thuận');	insert into `directory_city_township_name` (`locale`, `township_id`, `name`) values('vi_VN',10656,'Thị trấn Hòa Thuận');
insert into `directory_city_township` (`township_id`, `city_id`, `code`, `default_name`) values(10657,'51','','Thị trấn Tà Lùng');	insert into `directory_city_township_name` (`locale`, `township_id`, `name`) values('vi_VN',10657,'Thị trấn Tà Lùng');
insert into `directory_city_township` (`township_id`, `city_id`, `code`, `default_name`) values(10658,'51','','Xã Cách Linh');	insert into `directory_city_township_name` (`locale`, `township_id`, `name`) values('vi_VN',10658,'Xã Cách Linh');
insert into `directory_city_township` (`township_id`, `city_id`, `code`, `default_name`) values(10659,'51','','Xã Đại Sơn');	insert into `directory_city_township_name` (`locale`, `township_id`, `name`) values('vi_VN',10659,'Xã Đại Sơn');
insert into `directory_city_township` (`township_id`, `city_id`, `code`, `default_name`) values(10660,'51','','Xã Hồng Đại');	insert into `directory_city_township_name` (`locale`, `township_id`, `name`) values('vi_VN',10660,'Xã Hồng Đại');
insert into `directory_city_township` (`township_id`, `city_id`, `code`, `default_name`) values(10661,'51','','Xã Lương Thiện');	insert into `directory_city_township_name` (`locale`, `township_id`, `name`) values('vi_VN',10661,'Xã Lương Thiện');
insert into `directory_city_township` (`township_id`, `city_id`, `code`, `default_name`) values(10662,'51','','Xã Mỹ Hưng');	insert into `directory_city_township_name` (`locale`, `township_id`, `name`) values('vi_VN',10662,'Xã Mỹ Hưng');
insert into `directory_city_township` (`township_id`, `city_id`, `code`, `default_name`) values(10663,'51','','Xã Tiên Thành');	insert into `directory_city_township_name` (`locale`, `township_id`, `name`) values('vi_VN',10663,'Xã Tiên Thành');
insert into `directory_city_township` (`township_id`, `city_id`, `code`, `default_name`) values(10664,'51','','Xã Triệu Ẩu');	insert into `directory_city_township_name` (`locale`, `township_id`, `name`) values('vi_VN',10664,'Xã Triệu Ẩu');

--- Set post code
UPDATE directory_city_township a, directory_region_city b
SET a.postcode  = b.postcode
WHERE a.city_id = b.city_id AND a.township_id IN (10652, 10653, 10654, 10655, 10656, 10657, 10658, 10659, 10660, 10661, 10662, 10663, 10664);
