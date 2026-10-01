<?php
/**
 * \file       custom/stockalert/core/boxes/box_stock_alertes.php
 * \ingroup    stockalert
 * \brief      Home widget: stock alert KPIs and the 5 most urgent items
 */

include_once DOL_DOCUMENT_ROOT.'/core/boxes/modules_boxes.php';
dol_include_once('/stockalert/class/stockalert.class.php');

/**
 * Class to manage the stock alerts box
 */
class box_stock_alertes extends ModeleBoxes
{
	public $boxcode = "stockalertes";
	public $boximg = "stock";
	public $boxlabel = "StockAlertBoxTitle";
	public $depends = array("product", "stock", "fournisseur");

	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	public $param;
	public $info_box_head = array();
	public $info_box_contents = array();

	/**
	 *  Constructor
	 *
	 *  @param  DoliDB  $db         Database handler
	 *  @param  string  $param      More parameters
	 */
	public function __construct($db, $param = '')
	{
		global $user;

		$this->db = $db;
		$this->hidden = empty($user->rights->stockalert->report->read);
	}

	/**
	 *  Load data into info_box_contents array to show array later.
	 *
	 *  @param	int		$max        Maximum number of records to load
	 *  @return	void
	 */
	public function loadBox($max = 5)
	{
		global $langs;
		$langs->loadLangs(array("boxes", "stockalert@stockalert"));

		$this->max = $max;
		$this->info_box_head = array(
			'text' => $langs->trans("StockAlertBoxTitle"),
			'sublink' => dol_buildpath('/stockalert/index.php', 1),
			'subtext' => $langs->trans("StockAlertFullReport"),
			'subpicto' => 'object_stock',
		);

		if (empty($this->hidden)) {
			$alert = new StockAlert($this->db);
			if ($alert->fetchAll() < 0) {
				$this->info_box_contents[0][0] = array('td' => '', 'maxlength' => 500, 'text' => $alert->error);
				return;
			}
			$kpi = $alert->getKpis();

			$l = 0;
			$items = array(
				'StockAlertKpiAlert' => $kpi['nb_alert'],
				'StockAlertKpiReliquats' => $kpi['nb_reliquats'],
				'StockAlertKpiLate' => $kpi['nb_late'],
			);
			foreach ($items as $label => $value) {
				$this->info_box_contents[$l][0] = array('td' => 'class="tdoverflowmax300"', 'text' => $langs->trans($label));
				$this->info_box_contents[$l][1] = array('td' => 'class="right nowraponall"', 'text' => '<strong>'.((int) $value).'</strong>', 'asis' => 1);
				$l++;
			}

			// Top most urgent items
			$this->info_box_contents[$l][0] = array('td' => 'class="liste_titre"', 'text' => $langs->trans("StockAlertTop5"));
			$this->info_box_contents[$l][1] = array('td' => 'class="liste_titre right"', 'text' => $langs->trans("StockAlertSuggested"));
			$l++;
			$n = 0;
			foreach ($alert->rows as $r) {
				if ($n >= $max) {
					break;
				}
				if (!$r->is_alert && !$r->is_late) {
					continue;
				}
				$url = DOL_URL_ROOT.'/product/card.php?id='.$r->id;
				$late = $r->is_late ? ' '.img_warning($langs->trans("StockAlertLate")) : '';
				$this->info_box_contents[$l][0] = array(
					'td' => 'class="tdoverflowmax300"',
					'text' => '<a href="'.$url.'">'.dol_escape_htmltag($r->ref).'</a> '.dol_escape_htmltag($r->label).$late,
					'asis' => 1,
				);
				$this->info_box_contents[$l][1] = array('td' => 'class="right nowraponall"', 'text' => price2num($r->to_order, 'MS'));
				$l++;
				$n++;
			}
			if ($n == 0) {
				$this->info_box_contents[$l][0] = array('td' => 'class="center opacitymedium"', 'text' => $langs->trans("StockAlertNothing"));
			}
		} else {
			$this->info_box_contents[0][0] = array(
				'td' => 'class="nohover opacitymedium left"',
				'text' => $langs->trans("ReadPermissionNotAllowed"),
			);
		}
	}

	/**
	 *  Method to show box
	 *
	 *  @param  array   $head       Array with properties of box title
	 *  @param  array   $contents   Array with properties of box lines
	 *  @param  int     $nooutput   No print, only return string
	 *  @return string
	 */
	public function showBox($head = null, $contents = null, $nooutput = 0)
	{
		return parent::showBox($this->info_box_head, $this->info_box_contents, $nooutput);
	}
}
