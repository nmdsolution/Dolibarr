-- Copyright (C) 2026
-- Expenses entered before the detail lines existed (one supplier, one total):
-- turn each of them into a single detail line. Safe to run several times.
-- When the old TTC total is missing or lower than the HT total, the VAT rate is set to 0 (TTC = HT).

INSERT INTO llx_projectanalytic_expense_det (fk_expense, fk_soc, fk_facture_fourn, description, qty, unit_price_ht, tva_tx, total_ht, total_tva, total_ttc, rang, date_creation)
SELECT e.rowid, e.fk_soc, e.fk_facture_fourn_source, e.label, 1, e.total_ht,
	CASE WHEN e.total_ht > 0 AND e.total_ttc >= e.total_ht THEN ROUND((e.total_ttc / e.total_ht - 1) * 100, 4) ELSE 0 END,
	e.total_ht,
	CASE WHEN e.total_ht > 0 AND e.total_ttc >= e.total_ht THEN e.total_ttc - e.total_ht ELSE 0 END,
	CASE WHEN e.total_ht > 0 AND e.total_ttc >= e.total_ht THEN e.total_ttc ELSE e.total_ht END,
	1, NOW()
FROM llx_projectanalytic_expense as e
WHERE (e.total_ht <> 0 OR e.total_ttc <> 0)
AND NOT EXISTS (SELECT 1 FROM llx_projectanalytic_expense_det as d WHERE d.fk_expense = e.rowid);
