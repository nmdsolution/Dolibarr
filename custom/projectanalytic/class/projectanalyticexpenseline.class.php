<?php
/* Copyright (C) 2026
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file        custom/projectanalytic/class/projectanalyticexpenseline.class.php
 * \ingroup     projectanalytic
 * \brief       Detail line of a project analytic expense (consumable, supplier, quantity, unit price, VAT)
 *
 * Totals of the line are always computed here from quantity, unit price and VAT rate,
 * they are never typed by the user. Like the expense itself, a line is never read by the accountancy.
 */

/**
 * Class ProjectAnalyticExpenseLine
 */
class ProjectAnalyticExpenseLine
{
	/** @var DoliDB */
	public $db;

	/** @var string */
	public $table_element = 'projectanalytic_expense_det';

	/** @var string */
	public $error = '';

	public $id;
	public $fk_expense;
	public $fk_soc;
	public $fk_facture_fourn;
	public $description;
	public $qty = 1;
	public $unit_price_ht = 0;
	public $tva_tx = 0;
	public $total_ht = 0;
	public $total_tva = 0;
	public $total_ttc = 0;
	public $rang = 0;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Compute line totals from quantity, unit price and VAT rate.
	 *
	 * @return void
	 */
	public function computeTotals()
	{
		$this->total_ht = price2num($this->qty * $this->unit_price_ht, 'MT');
		$this->total_tva = price2num($this->total_ht * $this->tva_tx / 100, 'MT');
		$this->total_ttc = price2num($this->total_ht + $this->total_tva, 'MT');
	}

	/**
	 * Check data before saving.
	 *
	 * @return int  1 if OK, <0 if KO (error in $this->error)
	 */
	public function check()
	{
		global $langs;

		if (empty($this->fk_expense)) {
			$this->error = 'Missing expense';
			return -1;
		}
		if (trim((string) $this->description) === '') {
			$this->error = $langs->trans('ErrorFieldRequired', $langs->transnoentities('ProjectAnalyticLineDescription'));
			return -1;
		}
		if (!is_numeric($this->qty) || !is_numeric($this->unit_price_ht) || !is_numeric($this->tva_tx)) {
			$this->error = $langs->trans('ErrorBadValueForParameter', '', '');
			return -1;
		}
		return 1;
	}

	/**
	 * Insert the line (totals are computed).
	 *
	 * @return int  Id of the new line, <0 if KO
	 */
	public function create()
	{
		if ($this->check() < 0) {
			return -1;
		}
		$this->computeTotals();

		if (empty($this->rang)) {
			$resql = $this->db->query("SELECT MAX(rang) as m FROM ".$this->db->prefix().$this->table_element." WHERE fk_expense = ".((int) $this->fk_expense));
			$obj = $resql ? $this->db->fetch_object($resql) : null;
			$this->rang = ((int) ($obj ? $obj->m : 0)) + 1;
		}

		$sql = "INSERT INTO ".$this->db->prefix().$this->table_element;
		$sql .= " (fk_expense, fk_soc, fk_facture_fourn, description, qty, unit_price_ht, tva_tx, total_ht, total_tva, total_ttc, rang, date_creation)";
		$sql .= " VALUES (".((int) $this->fk_expense).", ".($this->fk_soc > 0 ? ((int) $this->fk_soc) : "NULL").", ".($this->fk_facture_fourn > 0 ? ((int) $this->fk_facture_fourn) : "NULL").",";
		$sql .= " '".$this->db->escape(dol_trunc(trim($this->description), 255, 'right', 'UTF-8', 1))."',";
		$sql .= " ".((float) $this->qty).", ".((float) $this->unit_price_ht).", ".((float) $this->tva_tx).",";
		$sql .= " ".((float) $this->total_ht).", ".((float) $this->total_tva).", ".((float) $this->total_ttc).", ".((int) $this->rang).", '".$this->db->idate(dol_now())."')";

		if (!$this->db->query($sql)) {
			$this->error = $this->db->lasterror();
			return -1;
		}
		$this->id = $this->db->last_insert_id($this->db->prefix().$this->table_element);
		return $this->id;
	}

	/**
	 * Update the line (totals are computed).
	 *
	 * @return int  >0 if OK, <0 if KO
	 */
	public function update()
	{
		if ($this->check() < 0) {
			return -1;
		}
		$this->computeTotals();

		$sql = "UPDATE ".$this->db->prefix().$this->table_element." SET";
		$sql .= " fk_soc = ".($this->fk_soc > 0 ? ((int) $this->fk_soc) : "NULL").",";
		$sql .= " fk_facture_fourn = ".($this->fk_facture_fourn > 0 ? ((int) $this->fk_facture_fourn) : "NULL").",";
		$sql .= " description = '".$this->db->escape(dol_trunc(trim($this->description), 255, 'right', 'UTF-8', 1))."',";
		$sql .= " qty = ".((float) $this->qty).", unit_price_ht = ".((float) $this->unit_price_ht).", tva_tx = ".((float) $this->tva_tx).",";
		$sql .= " total_ht = ".((float) $this->total_ht).", total_tva = ".((float) $this->total_tva).", total_ttc = ".((float) $this->total_ttc);
		$sql .= " WHERE rowid = ".((int) $this->id)." AND fk_expense = ".((int) $this->fk_expense);

		if (!$this->db->query($sql)) {
			$this->error = $this->db->lasterror();
			return -1;
		}
		return 1;
	}

	/**
	 * Delete the line.
	 *
	 * @return int  >0 if OK, <0 if KO
	 */
	public function delete()
	{
		$sql = "DELETE FROM ".$this->db->prefix().$this->table_element." WHERE rowid = ".((int) $this->id)." AND fk_expense = ".((int) $this->fk_expense);
		if (!$this->db->query($sql)) {
			$this->error = $this->db->lasterror();
			return -1;
		}
		return 1;
	}
}
