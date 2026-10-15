-- Copyright (C) 2026
-- Detail lines of a project analytic expense: one consumable per line,
-- with its own supplier, optional source supplier invoice, unit price, quantity and VAT.
-- Totals of the expense header are the sum of its lines.

CREATE TABLE llx_projectanalytic_expense_det(
	rowid integer AUTO_INCREMENT PRIMARY KEY,
	fk_expense integer NOT NULL,
	fk_soc integer DEFAULT NULL,
	fk_facture_fourn integer DEFAULT NULL,
	description varchar(255) NOT NULL,
	qty double(24,8) DEFAULT 1 NOT NULL,
	unit_price_ht double(24,8) DEFAULT 0 NOT NULL,
	tva_tx double(7,4) DEFAULT 0 NOT NULL,
	total_ht double(24,8) DEFAULT 0 NOT NULL,
	total_tva double(24,8) DEFAULT 0 NOT NULL,
	total_ttc double(24,8) DEFAULT 0 NOT NULL,
	rang integer DEFAULT 0 NOT NULL,
	date_creation datetime NOT NULL,
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=innodb;
