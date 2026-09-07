-- Copyright (C) 2026
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.

CREATE TABLE llx_projectanalytic_expense(
	rowid integer AUTO_INCREMENT PRIMARY KEY,
	entity integer DEFAULT 1 NOT NULL,
	fk_projet integer NOT NULL,
	fk_soc integer DEFAULT NULL,
	fk_facture_fourn_source integer DEFAULT NULL,
	label varchar(255) NOT NULL,
	note text DEFAULT NULL,
	datep date NOT NULL,
	total_ht double(24,8) DEFAULT 0 NOT NULL,
	total_ttc double(24,8) DEFAULT 0 NOT NULL,
	fk_user_creat integer NOT NULL,
	fk_user_modif integer DEFAULT NULL,
	date_creation datetime NOT NULL,
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=innodb;
