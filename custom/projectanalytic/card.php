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
 * \file    custom/projectanalytic/card.php
 * \ingroup projectanalytic
 * \brief   Page to create/edit/view/delete a project analytic expense.
 *
 * This amount only feeds the "Profit" balance of the project Overview tab
 * (see class/actions_projectanalytic.class.php hooks). It is never read by
 * the Accountancy module, so it can never duplicate a cost already booked
 * on a regular, global supplier invoice.
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
dol_include_once('/projectanalytic/class/projectanalyticexpenseline.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

// Load translation files required by the page
$langs->loadLangs(array("projectanalytic@projectanalytic", "projects", "bills"));

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$projectid = GETPOST('projectid', 'int');
$lineid = GETPOST('lineid', 'int');

// Initialize technical objects
$object = new ProjectAnalyticExpense($db);
$extrafields = new ExtraFields($db); // Required by core/actions_addupdatedelete.inc.php even though this object has no extrafield

if (empty($action) && empty($id)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

$permissiontoread = $user->hasRight('projectanalytic', 'projectexpense', 'read');
$permissiontoadd = $user->hasRight('projectanalytic', 'projectexpense', 'write');
$permissiontodelete = $user->hasRight('projectanalytic', 'projectexpense', 'delete');

// Security check
if (!isModEnabled('projectanalytic')) {
	accessforbidden();
}
if (!$permissiontoread) {
	accessforbidden();
}


/*
 * Actions
 */

$backurlforlist = dol_buildpath('/projectanalytic/list.php', 1);

/*
 * Actions on detail lines (consumable, supplier, supplier invoice, quantity, unit price, VAT)
 * Totals are always computed by the system and the expense totals are the sum of its lines.
 */
if ($object->id > 0 && in_array($action, array('addline', 'updateline', 'confirm_deleteline')) && !$cancel) {
	if (!$permissiontoadd) {
		accessforbidden('NotEnoughPermissions', 0, 1);
	}

	$line = new ProjectAnalyticExpenseLine($db);
	$line->fk_expense = $object->id;
	$error = 0;

	if ($action == 'addline' || $action == 'updateline') {
		$line->id = $lineid;
		$line->description = GETPOST('line_description', 'alphanohtml');
		$line->fk_soc = GETPOST('line_fk_soc', 'int');
		$line->fk_facture_fourn = GETPOST('line_fk_facture_fourn', 'int');
		$line->qty = price2num(GETPOST('line_qty', 'alpha'));
		$line->unit_price_ht = price2num(GETPOST('line_pu', 'alpha'));
		$line->tva_tx = price2num(GETPOST('line_tva', 'alpha'));
		if ($line->qty === '' || $line->qty === null) {
			$line->qty = 1;
		}
		if ($line->unit_price_ht === '' || $line->unit_price_ht === null) {
			$line->unit_price_ht = 0;
		}
		if ($line->tva_tx === '' || $line->tva_tx === null) {
			$line->tva_tx = 0;
		}
		$res = ($action == 'addline') ? $line->create() : $line->update();
		if ($res < 0) {
			setEventMessages($line->error, null, 'errors');
			$error++;
		}
	} elseif (GETPOST('confirm', 'alpha') == 'yes') {
		$line->id = $lineid;
		if ($line->delete() < 0) {
			setEventMessages($line->error, null, 'errors');
			$error++;
		}
	}

	if (!$error) {
		$object->updateTotalsFromLines();
		header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id);
		exit;
	}
	$action = ($action == 'confirm_deleteline') ? 'view' : ($lineid ? 'editline' : 'view');
}

// After the creation of an expense, always open its card to enter the detail lines
if ($action == 'add' && !$cancel) {
	$backtopage = '';
}


if (empty($backtopage) || ($cancel && empty($id))) {
	if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
		$backtopage = $backurlforlist;
	} else {
		$backtopage = dol_buildpath('/projectanalytic/card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
	}
}

$triggermodname = '';

// Handles actions: cancel, add, update, confirm_delete
include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';


/*
 * View
 */

$form = new Form($db);

$title = $langs->trans("ProjectAnalyticExpense");
llxHeader('', $title);

// Part to create
if ($action == 'create') {
	if (empty($permissiontoadd)) {
		accessforbidden('NotEnoughPermissions', 0, 1);
	}

	// Pre-fill project from the "projectid" parameter coming from the project Overview tab
	if (!GETPOSTISSET('fk_projet') && $projectid > 0) {
		$_POST['fk_projet'] = $projectid;
	}

	print load_fiche_titre($langs->trans("NewProjectAnalyticExpense"), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}

	print dol_get_fiche_head(array(), '');

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

	print '</table>'."\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';
}

// Part to edit record
if ($object->id > 0 && $action == 'edit') {
	if (empty($permissiontoadd)) {
		accessforbidden('NotEnoughPermissions', 0, 1);
	}

	print load_fiche_titre($langs->trans("ProjectAnalyticExpense"), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';

	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	print dol_get_fiche_head(array(), 'card', $langs->trans("ProjectAnalyticExpense"), -1, $object->picto);

	$formconfirm = '';
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('Delete'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}
	if ($action == 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.((int) $lineid), $langs->trans('Delete'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}
	print $formconfirm;

	$linkback = '<a href="'.dol_buildpath('/projectanalytic/list.php', 1).'?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

	print '<div class="refidno">';
	print $linkback;
	print '</div>';
	print '<div class="clearboth"></div>';

	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">'."\n";

	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';

	print dol_get_fiche_end();



	// Detail lines
	$object->fetchLines();

	// Supplier invoices available to be linked (validated ones, newest first)
	$invoiceoptions = array();
	$sqli = "SELECT f.rowid, f.ref, f.ref_supplier, f.datef, s.nom as supplier";
	$sqli .= " FROM ".MAIN_DB_PREFIX."facture_fourn as f LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON s.rowid = f.fk_soc";
	$sqli .= " WHERE f.entity IN (".getEntity('supplier_invoice').") AND f.fk_statut > 0 ORDER BY f.datef DESC, f.rowid DESC";
	$sqli .= $db->plimit(1500, 0);
	$resi = $db->query($sqli);
	if ($resi) {
		while ($oi = $db->fetch_object($resi)) {
			$invoiceoptions[$oi->rowid] = $oi->ref.' - '.dol_trunc($oi->supplier, 25).($oi->ref_supplier ? ' ('.dol_trunc($oi->ref_supplier, 20).')' : '');
		}
	}
	foreach ($object->lines as $l) {
		if ($l->fk_facture_fourn > 0 && !isset($invoiceoptions[$l->fk_facture_fourn])) {
			$invoiceoptions[$l->fk_facture_fourn] = '#'.$l->fk_facture_fourn;
		}
	}

	require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
	require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';
	$tmpsoc = new Societe($db);
	$tmpinv = new FactureFournisseur($db);

	print load_fiche_titre($langs->trans('ProjectAnalyticLines'), '', '');

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'" name="addline">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';

	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans('ProjectAnalyticLineDescription').'</td>';
	print '<td>'.$langs->trans('Supplier').'</td>';
	print '<td>'.$langs->trans('SupplierInvoice').'</td>';
	print '<td class="right">'.$langs->trans('Qty').'</td>';
	print '<td class="right">'.$langs->trans('PriceUHT').'</td>';
	print '<td class="right">'.$langs->trans('VATRate').'</td>';
	print '<td class="right">'.$langs->trans('TotalHT').'</td>';
	print '<td class="right">'.$langs->trans('TotalVAT').'</td>';
	print '<td class="right">'.$langs->trans('TotalTTC').'</td>';
	print '<td></td>';
	print '</tr>';

	$sumht = 0;
	$sumtva = 0;
	$sumttc = 0;
	foreach ($object->lines as $l) {
		$sumht += $l->total_ht;
		$sumtva += $l->total_tva;
		$sumttc += $l->total_ttc;

		if ($action == 'editline' && $lineid == $l->id && $permissiontoadd) {
			print '<tr class="oddeven">';
			print '<td><input type="hidden" name="lineid" value="'.$l->id.'"><input type="text" name="line_description" class="minwidth200 maxwidth300" value="'.dol_escape_htmltag($l->description).'"></td>';
			print '<td>'.$form->select_company($l->fk_soc, 'line_fk_soc', 's.fournisseur = 1', 1, 0, 0, array(), 0, 'minwidth150 maxwidth200').'</td>';
			print '<td>'.$form->selectarray('line_fk_facture_fourn', $invoiceoptions, $l->fk_facture_fourn, 1, 0, 0, '', 0, 0, 0, '', 'minwidth150 maxwidth250', 1).'</td>';
			print '<td class="right"><input type="text" name="line_qty" class="width50 right" value="'.dol_escape_htmltag(price2num($l->qty, 'MS')).'"></td>';
			print '<td class="right"><input type="text" name="line_pu" class="width75 right" value="'.dol_escape_htmltag(price2num($l->unit_price_ht, 'MU')).'"></td>';
			print '<td class="right"><input type="text" name="line_tva" class="width50 right" value="'.dol_escape_htmltag(price2num($l->tva_tx, 'MS')).'"></td>';
			print '<td colspan="3" class="right opacitymedium">'.$langs->trans('ProjectAnalyticLineAutoTotals').'</td>';
			print '<td class="center nowraponall"><button type="submit" class="button buttongen" name="action" value="updateline">'.$langs->trans('Save').'</button> <button type="submit" class="button button-cancel" name="cancel" value="1">'.$langs->trans('Cancel').'</button></td>';
			print '</tr>';
		} else {
			print '<tr class="oddeven">';
			print '<td>'.dol_escape_htmltag($l->description).'</td>';
			print '<td>';
			if ($l->fk_soc > 0 && $tmpsoc->fetch($l->fk_soc) > 0) {
				print $tmpsoc->getNomUrl(1, 'supplier');
			}
			print '</td>';
			print '<td>';
			if ($l->fk_facture_fourn > 0 && $tmpinv->fetch($l->fk_facture_fourn) > 0) {
				print $tmpinv->getNomUrl(1);
			}
			print '</td>';
			print '<td class="right">'.price2num($l->qty, 'MS').'</td>';
			print '<td class="right">'.price($l->unit_price_ht).'</td>';
			print '<td class="right">'.price2num($l->tva_tx, 'MS').' %</td>';
			print '<td class="right">'.price($l->total_ht).'</td>';
			print '<td class="right">'.price($l->total_tva).'</td>';
			print '<td class="right">'.price($l->total_ttc).'</td>';
			print '<td class="right nowraponall">';
			if ($permissiontoadd) {
				print '<a class="editfielda" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=editline&lineid='.$l->id.'&token='.newToken().'">'.img_edit().'</a> ';
				print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=deleteline&lineid='.$l->id.'&token='.newToken().'">'.img_delete().'</a>';
			}
			print '</td>';
			print '</tr>';
		}
	}

	if (empty($object->lines)) {
		print '<tr class="oddeven"><td colspan="10" class="opacitymedium">'.$langs->trans('None').'</td></tr>';
	}

	// Add line form
	if ($permissiontoadd && $action != 'editline') {
		print '<tr class="liste_titre nodrag nodrop">';
		print '<td><input type="text" name="line_description" class="minwidth200 maxwidth300" placeholder="'.dol_escape_htmltag($langs->trans('ProjectAnalyticLineDescription')).'" value="'.dol_escape_htmltag(GETPOST('line_description', 'alphanohtml')).'"></td>';
		print '<td>'.$form->select_company(GETPOST('line_fk_soc', 'int'), 'line_fk_soc', 's.fournisseur = 1', 1, 0, 0, array(), 0, 'minwidth150 maxwidth200').'</td>';
		print '<td>'.$form->selectarray('line_fk_facture_fourn', $invoiceoptions, GETPOST('line_fk_facture_fourn', 'int'), 1, 0, 0, '', 0, 0, 0, '', 'minwidth150 maxwidth250', 1).'</td>';
		print '<td class="right"><input type="text" id="line_qty" name="line_qty" class="width50 right" value="1"></td>';
		print '<td class="right"><input type="text" id="line_pu" name="line_pu" class="width75 right" value=""></td>';
		print '<td class="right"><input type="text" id="line_tva" name="line_tva" class="width50 right" value="0"></td>';
		print '<td class="right opacitymedium" colspan="3" id="line_preview">'.$langs->trans('ProjectAnalyticLineAutoTotals').'</td>';
		print '<td class="center"><button type="submit" class="button buttongen" name="action" value="addline">'.$langs->trans('Add').'</button></td>';
		print '</tr>';
	}

	// Totals of the expense = sum of the lines
	print '<tr class="liste_total">';
	print '<td colspan="6" class="right">'.$langs->trans('Total').'</td>';
	print '<td class="right">'.price($sumht).'</td>';
	print '<td class="right">'.price($sumtva).'</td>';
	print '<td class="right">'.price($sumttc).'</td>';
	print '<td></td>';
	print '</tr>';

	print '</table>';
	print '</div>';
	print '</form>';

	// Live preview of the line totals while typing (the stored totals are computed server side)
	print '<script>
	jQuery(document).ready(function () {
		function num(v) { v = String(v).replace(/\s/g, "").replace(",", "."); var n = parseFloat(v); return isNaN(n) ? 0 : n; }
		function refresh() {
			var ht = num(jQuery("#line_qty").val()) * num(jQuery("#line_pu").val());
			var ttc = ht * (1 + num(jQuery("#line_tva").val()) / 100);
			jQuery("#line_preview").text("HT " + ht.toFixed(2) + "  /  TTC " + ttc.toFixed(2));
		}
		jQuery("#line_qty, #line_pu, #line_tva").on("keyup change", refresh);
	});
	</script>';

	// Buttons for actions
	print '<div class="tabsAction">'."\n";
	if ($permissiontoadd) {
		print dolGetButtonAction('', $langs->trans('Modify'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);
	}
	if ($permissiontodelete) {
		print dolGetButtonAction('', $langs->trans("Delete"), 'delete', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=delete&token='.newToken(), '', $permissiontodelete);
	}
	print '</div>'."\n";
}

// End of page
llxFooter();
$db->close();
