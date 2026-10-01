<?php
/**
 * \file       custom/stockalert/class/stockalert.class.php
 * \ingroup    stockalert
 * \brief      Computation of stock alerts, supplier back-orders (reliquats) and late deliveries
 */

/**
 * Class StockAlert
 *
 * Reliquat of a supplier order line = ordered qty - qty already received
 * (llx_commande_fournisseur_dispatch.qty), never below 0.
 * Orders taken into account are the ones in progress (validated, approved, ordered, partially received).
 */
class StockAlert
{
	/** @var DoliDB */
	public $db;

	/** @var string */
	public $error = '';

	/** @var array Rows computed by fetchAll() */
	public $rows = array();

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
	 * Load all products having a threshold or a pending reliquat, with computed figures.
	 *
	 * Each row: id, ref, label, stock, seuil, souhaitee, reliquat, nb_reliquats, nb_late,
	 * next_date (oldest planned delivery date with a pending reliquat), is_alert, is_late, to_order.
	 *
	 * @return int  Number of rows, <0 if error
	 */
	public function fetchAll()
	{
		$this->rows = array();
		$now = dol_now();
		$today = dol_mktime(0, 0, 0, (int) dol_print_date($now, '%m'), (int) dol_print_date($now, '%d'), (int) dol_print_date($now, '%Y'));

		$sql = "SELECT p.rowid, p.ref, p.label, COALESCE(p.stock, 0) as stock,";
		$sql .= " p.seuil_stock_alerte as seuil_alerte, p.desiredstock as quantite_souhaitee,";
		$sql .= " COALESCE(r.reliquat, 0) as reliquat, COALESCE(r.nb_reliquats, 0) as nb_reliquats,";
		$sql .= " COALESCE(r.nb_late, 0) as nb_late, r.next_date";
		$sql .= " FROM ".MAIN_DB_PREFIX."product as p";
		$sql .= " LEFT JOIN (";
		$sql .= "   SELECT d.fk_product,";
		$sql .= "   SUM(d.qty - COALESCE(x.recv, 0)) as reliquat,";
		$sql .= "   COUNT(d.rowid) as nb_reliquats,";
		$sql .= "   SUM(CASE WHEN c.date_livraison IS NOT NULL AND c.date_livraison < '".$this->db->idate($today)."' THEN 1 ELSE 0 END) as nb_late,";
		$sql .= "   MIN(c.date_livraison) as next_date";
		$sql .= "   FROM ".MAIN_DB_PREFIX."commande_fournisseurdet as d";
		$sql .= "   INNER JOIN ".MAIN_DB_PREFIX."commande_fournisseur as c ON c.rowid = d.fk_commande";
		$sql .= "   LEFT JOIN (SELECT fk_commandefourndet, SUM(qty) as recv FROM ".MAIN_DB_PREFIX."commande_fournisseur_dispatch GROUP BY fk_commandefourndet) as x ON x.fk_commandefourndet = d.rowid";
		$sql .= "   WHERE c.fk_statut IN (1, 2, 3, 4) AND d.fk_product > 0";
		$sql .= "   AND d.qty > COALESCE(x.recv, 0)";
		$sql .= "   AND c.entity IN (".getEntity('supplier_order').")";
		$sql .= "   GROUP BY d.fk_product";
		$sql .= " ) as r ON r.fk_product = p.rowid";
		$sql .= " WHERE p.entity IN (".getEntity('product').")";
		$sql .= " AND p.fk_product_type = 0";
		$sql .= " AND (p.seuil_stock_alerte > 0 OR p.desiredstock > 0 OR r.reliquat > 0)";

		$resql = $this->db->query($sql);
		if (!$resql) {
			$this->error = $this->db->lasterror();
			return -1;
		}

		while ($obj = $this->db->fetch_object($resql)) {
			$stock = (float) $obj->stock;
			// Native product fields (Stock limit / Desired stock): 0 or empty means "not set"
			$seuil = ((float) $obj->seuil_alerte > 0 ? (float) $obj->seuil_alerte : null);
			$souhaitee = ((float) $obj->quantite_souhaitee > 0 ? (float) $obj->quantite_souhaitee : null);
			$reliquat = max(0, (float) $obj->reliquat);

			$row = new stdClass();
			$row->id = (int) $obj->rowid;
			$row->ref = $obj->ref;
			$row->label = $obj->label;
			$row->stock = $stock;
			$row->seuil = $seuil;
			$row->souhaitee = $souhaitee;
			$row->reliquat = $reliquat;
			$row->nb_reliquats = (int) $obj->nb_reliquats;
			$row->nb_late = (int) $obj->nb_late;
			$row->next_date = $obj->next_date ? $this->db->jdate($obj->next_date) : '';
			$row->is_alert = ($seuil !== null && $stock < $seuil);
			$row->is_late = ($reliquat > 0 && $row->nb_late > 0);
			// Quantity to order = desired - (physical stock + pending reliquats)
			$row->to_order = ($souhaitee !== null ? max(0, $souhaitee - ($stock + $reliquat)) : 0);

			if ($row->is_alert || $reliquat > 0) {
				$this->rows[] = $row;
			}
		}
		$this->db->free($resql);

		// Most urgent first: late deliveries, then alerts, then biggest quantity to order
		usort($this->rows, function ($a, $b) {
			if ($a->is_late != $b->is_late) {
				return $a->is_late ? -1 : 1;
			}
			if ($a->is_alert != $b->is_alert) {
				return $a->is_alert ? -1 : 1;
			}
			return $b->to_order <=> $a->to_order;
		});

		return count($this->rows);
	}

	/**
	 * Global KPIs from loaded rows
	 *
	 * @return array  nb_alert, nb_reliquats, nb_late
	 */
	public function getKpis()
	{
		$k = array('nb_alert' => 0, 'nb_reliquats' => 0, 'nb_late' => 0);
		foreach ($this->rows as $r) {
			$k['nb_alert'] += $r->is_alert ? 1 : 0;
			$k['nb_reliquats'] += $r->nb_reliquats;
			$k['nb_late'] += $r->nb_late;
		}
		return $k;
	}
}
