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
 * \file    custom/projectanalytic/class/actions_projectanalytic.class.php
 * \ingroup projectanalytic
 * \brief   Hooks used to plug "project analytic expenses" into the project
 *          Overview tab (projet/element.php) without touching any core file
 *          and without ever exposing these amounts to the accountancy module.
 */

dol_include_once('/projectanalytic/class/projectanalyticexpense.class.php');

/**
 * Class ActionsProjectanalytic
 */
class ActionsProjectanalytic
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

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
	 * Add our entry into the array of "referent" element types shown on the
	 * project Overview tab (context 'projectOverview'), with 'margin' set to
	 * 'minus' so it is subtracted into the project "Profit" balance exactly
	 * like a supplier invoice, but without ever touching accountancy.
	 *
	 * @param  array        $parameters Hook parameters (contains 'listofreferent')
	 * @param  CommonObject $object     The project
	 * @param  string       $action     Current action
	 * @return int                      0 = merge into resArray, >0 = replace
	 */
	public function completeListOfReferent($parameters, &$object, &$action)
	{
		global $langs, $user;

		if (!isModEnabled('projectanalytic')) {
			return 0;
		}

		$langs->load('projectanalytic@projectanalytic');

		$id = $object->id;

		$this->results = array(
			'projectanalyticexpense' => array(
				'name' => 'ProjectAnalyticExpense',
				'title' => 'ListProjectAnalyticExpenseAssociatedProject',
				'class' => 'ProjectAnalyticExpense',
				'margin' => 'minus',
				'table' => 'projectanalytic_expense',
				'datefieldname' => 'datep',
				'urlnew' => dol_buildpath('/projectanalytic/card.php', 1).'?action=create&projectid='.((int) $id).'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
				'lang' => 'projectanalytic@projectanalytic',
				'buttonnew' => 'AddProjectAnalyticExpense',
				'testnew' => $user->hasRight('projectanalytic', 'projectexpense', 'write'),
				'test' => $user->hasRight('projectanalytic', 'projectexpense', 'read'),
			),
		);

		return 0;
	}

	/**
	 * Replace the generic "Detail" block for our referent key with our own
	 * simple listing (label, date, amount HT/TTC, optional link to the
	 * global supplier invoice this cost was extracted from).
	 *
	 * @param  array        $parameters Hook parameters (key, value, dates, datee)
	 * @param  CommonObject $object     The project
	 * @param  string       $action     Current action
	 * @return int                      0 = let core print its own block, 1 = we printed it ourselves
	 */
	public function printOverviewDetail($parameters, &$object, &$action)
	{
		global $langs, $user;

		if ($parameters['key'] != 'projectanalyticexpense') {
			return 0;
		}
		if (!isModEnabled('projectanalytic') || !$user->hasRight('projectanalytic', 'projectexpense', 'read')) {
			return 0;
		}

		$langs->load('projectanalytic@projectanalytic');

		$projectanalyticexpense = new ProjectAnalyticExpense($this->db);
		$lines = $projectanalyticexpense->fetchAll('ASC', 'datep', 0, 0, array('fk_projet' => $object->id));
		if (!is_array($lines)) {
			$lines = array();
		}

		$dates = $parameters['dates'];
		$datee = $parameters['datee'];
		if ($dates || $datee) {
			foreach ($lines as $key => $line) {
				if ($dates && $line->datep < $dates) {
					unset($lines[$key]);
				} elseif ($datee && $line->datep > $datee) {
					unset($lines[$key]);
				}
			}
		}

		$addform = '';
		if ($user->hasRight('projectanalytic', 'projectexpense', 'write')) {
			$urlnew = dol_buildpath('/projectanalytic/card.php', 1).'?action=create&projectid='.((int) $object->id).'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$object->id);
			$addform .= '<div class="inline-block valignmiddle">';
			$addform .= '<a class="buttonxxx marginleftonly" href="'.$urlnew.'" title="'.dol_escape_htmltag($langs->trans('AddProjectAnalyticExpense')).'"><span class="fa fa-plus-circle valignmiddle paddingleft"></span></a>';
			$addform .= '</div>';
		}

		$out = load_fiche_titre($langs->trans('ListProjectAnalyticExpenseAssociatedProject'), $addform, '');

		$out .= '<div class="div-table-responsive">';
		$out .= '<table class="noborder centpercent">';
		$out .= '<tr class="liste_titre">';
		$out .= '<td>'.$langs->trans('Label').'</td>';
		$out .= '<td class="center">'.$langs->trans('Date').'</td>';
		$out .= '<td>'.$langs->trans('ProjectAnalyticExpenseSourceInvoice').'</td>';
		$out .= '<td class="right">'.$langs->trans('AmountHT').'</td>';
		$out .= '<td class="right">'.$langs->trans('AmountTTC').'</td>';
		$out .= '<td></td>';
		$out .= '</tr>';

		if (count($lines) == 0) {
			$out .= '<tr class="oddeven"><td colspan="6" class="opacitymedium">'.$langs->trans('None').'</td></tr>';
		} else {
			foreach ($lines as $line) {
				$out .= '<tr class="oddeven">';
				$out .= '<td>'.$line->getNomUrl(1).' '.dol_escape_htmltag($line->label).'</td>';
				$out .= '<td class="center">'.dol_print_date($line->datep, 'day').'</td>';
				$out .= '<td>';
				if (!empty($line->fk_facture_fourn_source)) {
					require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';
					$invoice = new FactureFournisseur($this->db);
					if ($invoice->fetch($line->fk_facture_fourn_source) > 0) {
						$out .= $invoice->getNomUrl(1);
					}
				}
				$out .= '</td>';
				$out .= '<td class="right">'.price($line->total_ht).'</td>';
				$out .= '<td class="right">'.price($line->total_ttc).'</td>';
				$out .= '<td class="right">';
				if ($user->hasRight('projectanalytic', 'projectexpense', 'write')) {
					$out .= '<a href="'.dol_buildpath('/projectanalytic/card.php', 1).'?id='.$line->id.'&action=edit&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$object->id).'">'.img_edit().'</a>';
				}
				$out .= '</td>';
				$out .= '</tr>';
			}
		}

		$out .= '</table>';
		$out .= '</div>';

		$this->resprints = $out;

		return 1;
	}
}
