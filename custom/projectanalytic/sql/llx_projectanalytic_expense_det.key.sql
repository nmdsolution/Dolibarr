-- Copyright (C) 2026

-- Column added after the first version of the table (already existing tables): error ignored if it exists
ALTER TABLE llx_projectanalytic_expense_det ADD COLUMN fk_product integer DEFAULT NULL AFTER fk_facture_fourn;

ALTER TABLE llx_projectanalytic_expense_det ADD INDEX idx_projectanalytic_expense_det_fk_product (fk_product);
ALTER TABLE llx_projectanalytic_expense_det ADD INDEX idx_projectanalytic_expense_det_fk_expense (fk_expense);
ALTER TABLE llx_projectanalytic_expense_det ADD INDEX idx_projectanalytic_expense_det_fk_soc (fk_soc);
ALTER TABLE llx_projectanalytic_expense_det ADD INDEX idx_projectanalytic_expense_det_fk_facture_fourn (fk_facture_fourn);

ALTER TABLE llx_projectanalytic_expense_det ADD CONSTRAINT fk_projectanalytic_expense_det_fk_expense FOREIGN KEY (fk_expense) REFERENCES llx_projectanalytic_expense (rowid);
