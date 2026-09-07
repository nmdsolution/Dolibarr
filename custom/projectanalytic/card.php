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
