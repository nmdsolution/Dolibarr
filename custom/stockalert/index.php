<?php
/**
 * \file       custom/stockalert/index.php
 * \ingroup    stockalert
 * \brief      Stock alert summary screen with PDF export button
 */

// Load Dolibarr environment
$res = 0;
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
dol_include_once('/stockalert/class/stockalert.class.php');

$langs->loadLangs(array("products", "stocks", "stockalert@stockalert"));

if (!$user->hasRight('stockalert', 'report', 'read')) {
	accessforbidden();
}

$action = GETPOST('action', 'aZ09');

$alert = new StockAlert($db);
$alert->fetchAll();

if ($action == 'pdf') {
	dol_include_once('/stockalert/class/pdf_stockalert.class.php');
	$pdf = new PdfStockAlert($db);
	$pdf->write($alert->rows, $langs);
	exit;
}

$kpi = $alert->getKpis();

llxHeader('', $langs->trans("StockAlertReport"), '');
print load_fiche_titre($langs->trans("StockAlertTitle"), '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=pdf" target="_blank">'.img_picto('', 'pdf', 'class="pictofixedwidth"').$langs->trans("StockAlertGeneratePdf").'</a>', 'stock');

print '<div class="fichecenter"><div class="underbanner clearboth"></div>';
print '<table class="border centpercent tableforfield">';
print '<tr><td class="titlefield">'.$langs->trans("StockAlertKpiAlert").'</td><td><strong>'.$kpi['nb_alert'].'</strong></td></tr>';
print '<tr><td>'.$langs->trans("StockAlertKpiReliquats").'</td><td><strong>'.$kpi['nb_reliquats'].'</strong></td></tr>';
print '<tr><td>'.$langs->trans("StockAlertKpiLate").'</td><td><strong>'.$kpi['nb_late'].'</strong></td></tr>';
print '</table></div><br>';

$parts = array(
	'StockAlertPart1' => array_filter($alert->rows, function ($r) {
		return $r->is_alert;
	}),
	'StockAlertPart2' => array_filter($alert->rows, function ($r) {
		return $r->reliquat > 0;
	}),
);

foreach ($parts as $title => $rows) {
	print '<div class="div-table-responsive">';
	print '<table class="tagtable liste centpercent">';
	print '<tr class="liste_titre"><td colspan="7">'.$langs->trans($title).'</td></tr>';
	print '<tr class="liste_titre">';
	print '<th>'.$langs->trans("Ref").'</th><th>'.$langs->trans("Label").'</th>';
	print '<th class="right">'.$langs->trans("StockAlertPhysical").'</th><th class="right">'.$langs->trans("StockAlertDesired").'</th>';
	print '<th class="right">'.$langs->trans("StockAlertReliquat").'</th><th class="center">'.$langs->trans("StockAlertDueDate").'</th>';
	print '<th class="right">'.$langs->trans("StockAlertSuggested").'</th></tr>';
	if (empty($rows)) {
		print '<tr class="oddeven"><td colspan="7" class="opacitymedium center">'.$langs->trans("StockAlertNothing").'</td></tr>';
	}
	foreach ($rows as $r) {
		print '<tr class="oddeven">';
		print '<td><a href="'.DOL_URL_ROOT.'/product/card.php?id='.$r->id.'">'.dol_escape_htmltag($r->ref).'</a></td>';
		print '<td>'.dol_escape_htmltag($r->label).'</td>';
		print '<td class="right">'.price2num($r->stock, 'MS').'</td>';
		print '<td class="right">'.($r->souhaitee === null ? '' : price2num($r->souhaitee, 'MS')).'</td>';
		print '<td class="right">'.($r->reliquat > 0 ? price2num($r->reliquat, 'MS') : '').'</td>';
		print '<td class="center">'.($r->next_date ? dol_print_date($r->next_date, 'day') : '').($r->is_late ? ' '.img_warning($langs->trans("StockAlertLate")).' <span class="warning">'.$langs->trans("StockAlertLate").'</span>' : '').'</td>';
		print '<td class="right"><strong>'.price2num($r->to_order, 'MS').'</strong></td>';
		print '</tr>';
	}
	print '</table></div><br>';
}

llxFooter();
$db->close();
