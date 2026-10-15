-- Copyright (C) 2026

ALTER TABLE llx_projectanalytic_expense_det ADD INDEX idx_projectanalytic_expense_det_fk_expense (fk_expense);
ALTER TABLE llx_projectanalytic_expense_det ADD INDEX idx_projectanalytic_expense_det_fk_soc (fk_soc);
ALTER TABLE llx_projectanalytic_expense_det ADD INDEX idx_projectanalytic_expense_det_fk_facture_fourn (fk_facture_fourn);

ALTER TABLE llx_projectanalytic_expense_det ADD CONSTRAINT fk_projectanalytic_expense_det_fk_expense FOREIGN KEY (fk_expense) REFERENCES llx_projectanalytic_expense (rowid);
