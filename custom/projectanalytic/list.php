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
 * \file    custom/projectanalytic/list.php
 * \ingroup projectanalytic
 * \brief   List of project analytic expenses, optionally filtered by project.
 */

// Load Dolibarr environment
$res = 0;
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

dol_include_once('/projectanalytic/class/projectanalyticexpense.class.php');
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

// Load translation files required by the page
$langs->loadLangs(array("projectanalytic@projectanalytic", "projects"));

$action = GETPOST('action', 'aZ09');
$search_projectid = GETPOST('search_projectid', 'int');

$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$page = GETPOSTISSET('page') ? GETPOST('page', 'int') : 0;
if ($page < 0) {
	$page = 0;
}
$offset = $limit * $page;

$permissiontoread = $user->hasRight('projectanalytic', 'projectexpense', 'read');
$permissiontoadd = $user->hasRight('projectanalytic', 'projectexpense', 'write');
$permissiontodelete = $user->hasRight('projectanalytic', 'projectexpense', 'delete');

if (!isModEnabled('projectanalytic')) {
	accessforbidden();
}
if (!$permissiontoread) {
	accessforbidden();
}

$object = new ProjectAnalyticExpense($db);

$filter = array();
if ($search_projectid > 0) {
	$filter['fk_projet'] = $search_projectid;
}

$totalht = 0;
$totalttc = 0;
$lines = $object->fetchAll('DESC', 'datep', $limit, $offset, $filter);
if (!is_array($lines)) {
	$lines = array();
	setEventMessages($object->error, $object->errors, 'errors');
}


/*
 * View
 */

$form = new Form($db);

$title = $langs->trans("ProjectAnalyticExpenses");
llxHeader('', $title);

print load_fiche_titre($title, '', 'projectanalytic@projectanalytic');

print '<form method="GET" action="'.$_SERVER["PHP_SELF"].'">';
print '<div class="div-table-responsive">';
print '<table class="noborder centpercent">';

print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Project").'</td>';
print '<td>'.$langs->trans("Label").'</td>';
print '<td class="center">'.$langs->trans("Date").'</td>';
print '<td>'.$langs->trans("ProjectAnalyticExpenseSourceInvoice").'</td>';
print '<td class="right">'.$langs->trans("AmountHT").'</td>';
print '<td class="right">'.$langs->trans("AmountTTC").'</td>';
print '<td></td>';
print '</tr>';

print '<tr class="liste_titre">';
print '<td>';
$formproject = null;
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
$formproject = new FormProjets($db);
print $formproject->select_projects(-1, $search_projectid, 'search_projectid', 0, 0, 1, 1, 0, 0, 0, '', 0, 0, 'maxwidth300');
print '</td>';
print '<td></td><td></td><td></td><td></td><td></td>';
print '<td class="right"><input type="submit" class="button small" value="'.$langs->trans("Refresh").'"></td>';
print '</tr>';

if (count($lines) == 0) {
	print '<tr class="oddeven"><td colspan="7" class="opacitymedium">'.$langs->trans("None").'</td></tr>';
} else {
	foreach ($lines as $expenseline) {
		$totalht += $expenseline->total_ht;
		$totalttc += $expenseline->total_ttc;

		print '<tr class="oddeven">';
		print '<td>';
		$proj = new Project($db);
		if ($proj->fetch($expenseline->fk_projet) > 0) {
			print $proj->getNomUrl(1);
		}
		print '</td>';
		print '<td>'.$expenseline->getNomUrl(1).' '.dol_escape_htmltag($expenseline->label).'</td>';
		print '<td class="center">'.dol_print_date($expenseline->datep, 'day').'</td>';
		print '<td>';
		if (!empty($expenseline->fk_facture_fourn_source)) {
			require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';
			$invoice = new FactureFournisseur($db);
			if ($invoice->fetch($expenseline->fk_facture_fourn_source) > 0) {
				print $invoice->getNomUrl(1);
			}
		}
		print '</td>';
		print '<td class="right">'.price($expenseline->total_ht).'</td>';
		print '<td class="right">'.price($expenseline->total_ttc).'</td>';
		print '<td class="right">';
		if ($permissiontoadd) {
			print '<a href="'.dol_buildpath('/projectanalytic/card.php', 1).'?id='.$expenseline->id.'&action=edit">'.img_edit().'</a> ';
		}
		if ($permissiontodelete) {
			print '<a href="'.dol_buildpath('/projectanalytic/card.php', 1).'?id='.$expenseline->id.'&action=delete&token='.newToken().'">'.img_delete().'</a>';
		}
		print '</td>';
		print '</tr>';
	}

	print '<tr class="liste_total">';
	print '<td colspan="4" class="right">'.$langs->trans("Total").'</td>';
	print '<td class="right">'.price($totalht).'</td>';
	print '<td class="right">'.price($totalttc).'</td>';
	print '<td></td>';
	print '</tr>';
}

print '</table>';
print '</div>';
print '</form>';

if ($permissiontoadd) {
	print '<div class="tabsAction">';
	$urlnew = dol_buildpath('/projectanalytic/card.php', 1).'?action=create'.($search_projectid > 0 ? '&projectid='.$search_projectid : '');
	print dolGetButtonAction('', $langs->trans('AddProjectAnalyticExpense'), 'default', $urlnew, '', $permissiontoadd);
	print '</div>';
}

// End of page
llxFooter();
$db->close();
