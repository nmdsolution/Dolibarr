-- Copyright (C) 2026
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.

ALTER TABLE llx_projectanalytic_expense ADD INDEX idx_projectanalytic_expense_fk_projet (fk_projet);
ALTER TABLE llx_projectanalytic_expense ADD INDEX idx_projectanalytic_expense_entity (entity);
ALTER TABLE llx_projectanalytic_expense ADD INDEX idx_projectanalytic_expense_fk_soc (fk_soc);
ALTER TABLE llx_projectanalytic_expense ADD INDEX idx_projectanalytic_expense_fk_facture_fourn_source (fk_facture_fourn_source);

ALTER TABLE llx_projectanalytic_expense ADD CONSTRAINT fk_projectanalytic_expense_fk_projet FOREIGN KEY (fk_projet) REFERENCES llx_projet (rowid);
ALTER TABLE llx_projectanalytic_expense ADD CONSTRAINT fk_projectanalytic_expense_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user (rowid);
