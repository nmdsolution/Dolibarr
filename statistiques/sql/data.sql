-- <one line to give the program's name and a brief idea of what it does.>
-- Copyright (C) <year>  <name of author>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.

INSERT INTO `llx_extrafields` (`rowid`, `name`, `entity`, `elementtype`, `tms`, `label`, `type`, `size`, `fieldunique`, `fieldrequired`, `perms`, `pos`, `alwayseditable`, `param`,                                `list`, `totalizable`,`fieldcomputed`, `fielddefault`, `langs`, `fk_user_author`, `fk_user_modif`, `datec`, `enabled`, `help`) 
VALUES (NULL, 'marque', '1','product',CURRENT_TIMESTAMP,'Marque','varchar','25','0','0',NULL,'12','1','a:1:{s:7:"options";a:1:{s:0:"";N;}}', '1','0',NULL,NULL,NULL,1,1,NULL,'1', NULL);

ALTER TABLE `llx_product_extrafields` ADD `marque` VARCHAR(25) NOT NULL AFTER `import_key`;