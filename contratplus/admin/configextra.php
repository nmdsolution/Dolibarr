<?php
/*
 * Copyright (C) 2004-2017 Laurent Destailleur      <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019 David Moyon              <david@code42.fr>
 * Copyright (C) 2018-2019 Adam Gendre              <adam@code42.fr>
 * Copyright (C) 2019-2020 Fabien Fernandes Alves   <fabien@code42.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    contratplus/admin/configextra.php
 * \ingroup contratplus
 * \brief   ContratPlus extrafields setup page.
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) { $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--;
}
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) { $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
}
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) { $res=@include dirname(substr($tmp, 0, ($i+1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (! $res && file_exists("../../main.inc.php")) { $res=@include "../../main.inc.php";
}
if (! $res && file_exists("../../../main.inc.php")) { $res=@include "../../../main.inc.php";
}
if (! $res) { die("Include of main fails");
}

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
dol_include_once('/contratplus/core/function.php');

// Classes
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

global $langs, $user, $db, $conf;

// Translations
$langs->loadLangs(array("admin", "contratplus@contratplus"));

// Access control
if (! $user->rights->contratplus->action) { accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$error = 0;
$msg = '';

$option = array('options' => array('1' => '12',
    '2' => '24',
    '3' => '36',
    '4' => '48',
    '5' => '60',
    '6' => 'autres'));

if (GETPOST('action') == 'setpropaldetduree') {
    $status = GETPOST('status');
    $res = dolibarr_set_const($db, 'CONTRATPLUS_PROPAL_EXTRA_DUREE', $status, '', 0, '', $conf->entity);

    if (!$res > 0) {
        setEventMessages($langs->trans("Error"), '', 'errors');
    } else {
        $extrafield = new Extrafields($db);
        $res_update = $extrafield->update('serv_duree', 'ContratplusEngage', 'select', '', 'propaldet', 0, 0, 2, $option, 1, '', $status);
        if (!$res_update > 0) setEventMessages($langs->trans("Error"), '', 'errors');
        else setEventMessages($langs->trans("SetupSaved"), '', 'mesgs');
	}
}

if (GETPOST('action') == 'setfactureduree') {
    $status = GETPOST('status');
    $res = dolibarr_set_const($db, 'CONTRATPLUS_FACTURE_EXTRA_DUREE', $status, '', 0, '', $conf->entity);

    if (!$res > 0) {
        setEventMessages($langs->trans("Error"), '', 'errors');
    } else {
        $extrafield = new Extrafields($db);
        $res_update = $extrafield->update('serv_duree', 'ContratplusEngage', 'select', '', 'facturedet', 0, 0, 2, $option, 1, '', $status);
        if (!$res_update > 0) setEventMessages($langs->trans("Error"), '', 'errors');
        else setEventMessages($langs->trans("SetupSaved"), '', 'mesgs');
    }
}
if (GETPOST('action') == 'setfacturedatestart') {
    $status = GETPOST('status');
    $res = dolibarr_set_const($db, 'CONTRATPLUS_FACTURE_EXTRA_DATE_START', $status, '', 0, '', $conf->entity);

    if (!$res > 0) {
        setEventMessages($langs->trans("Error"), '', 'errors');
    } else {
		$extrafield = new Extrafields($db);
		$res_update = $extrafield->update('serv_date_start', 'ContratplusDateStart', 'date', '', 'facturedet', 0, 0, 1, '', 1, '', $status);
		if (!$res_update > 0) setEventMessages($langs->trans("Error"), '', 'errors');
		else setEventMessages($langs->trans("SetupSaved"), '', 'mesgs');
	}
}
if (GETPOST('action') == 'setfacturedateend') {
    $status = GETPOST('status');
    $res = dolibarr_set_const($db, 'CONTRATPLUS_FACTURE_EXTRA_DATE_END', $status, '', 0, '', $conf->entity);

    if (!$res > 0) {
        setEventMessages($langs->trans("Error"), '', 'errors');
    } else {
		$extrafield = new Extrafields($db);
		$res_update = $extrafield->update('serv_date_end', 'ContratplusDateEnd', 'date', '', 'facturedet', 0, 0, 3, '', 1, '', $status);
		if (!$res_update > 0) setEventMessages($langs->trans("Error"), '', 'errors');
		else setEventMessages($langs->trans("SetupSaved"), '', 'mesgs');
	}
}

/*
 * View
 */

$morejs = array("/contratplus/js/contratPlus.js");
llxHeader('', $langs->trans("ContratPlusSetupExtra"), '', '', '', '', $morejs, '', 0, 0);

$head = generateHeader();

dol_fiche_head($head, 'extrasetup', "Contrat Plus", 0, 'contratplus@contratplus');

print '<h1>'.$langs->trans('SetupExtraPropal').'</h1>';

print '<form name="ContratPlusSetupExtra">';

if ($action == 'edit')
    print '<input type="hidden" name="action" value="confirmedit">';

if ($action != 'edit') {
    print '<table class="noborder" width="100%"><tbody>';
    print '<tr class="liste_titre">';
    print '<td><h4 align="left" width="30%">' . $langs->trans('Parameter') . '</h4></td>';
    print '<td align="center" width="70%"><h4>' . $langs->trans('Value') . '</h4></td>';
    print '</tr>';
    print '<tr>';
    print '<td>' . $langs->trans('ContratPlusDureePropalActive') . '</td>';
    if ($conf->global->CONTRATPLUS_PROPAL_EXTRA_DUREE == 1) {
        print '<td align="center"><a href="' . $_SERVER['PHP_SELF'] . '?action=setpropaldetduree&status=0">';
        print img_picto($langs->trans("Activated"), 'switch_on');
        print '</a></td>';
    } else {
        print '<td align="center"><a href="' . $_SERVER['PHP_SELF'] . '?action=setpropaldetduree&status=1">';
        print img_picto($langs->trans("Disabled"), 'switch_off');
        print '</a></td>';
    }

    print '</tr>';
    print '</table>';
}

print '<h1>'.$langs->trans('SetupExtraFacture').'</h1>';

if ($action == 'edit')
    print '<input type="hidden" name="action" value="confirmedit">';

if ($action != 'edit') {
	print '<table class="noborder" width="100%"><tbody>';
	print '<tr class="liste_titre">';
	print '<td><h4 align="left" width="30%">'.$langs->trans('Parameter').'</h4></td>';
	print '<td align="center" width="70%"><h4>'.$langs->trans('Value').'</h4></td>';
	print '</tr>';

    print '<tr>';
    print '<td>'.$langs->trans('ContratPlusDateStartFactActive').'</td>';
    if ($conf->global->CONTRATPLUS_FACTURE_EXTRA_DATE_START == 1) {
        print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setfacturedatestart&status=0">';
        print img_picto($langs->trans("Activated"), 'switch_on');
        print '</a></td>';
    } else {
        print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setfacturedatestart&status=1">';
        print img_picto($langs->trans("Disabled"), 'switch_off');
        print '</a></td>';
    }

    print '</tr>';

	print '<tr>';
	print '<td>'.$langs->trans('ContratPlusDureeFactActive').'</td>';
	if ($conf->global->CONTRATPLUS_FACTURE_EXTRA_DUREE == 1) {
		print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setfactureduree&status=0">';
		print img_picto($langs->trans("Activated"), 'switch_on');
		print '</a></td>';
	} else {
		print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setfactureduree&status=1">';
		print img_picto($langs->trans("Disabled"), 'switch_off');
		print '</a></td>';
	}

	print '</tr>';



	print '<tr>';
	print '<td>'.$langs->trans('ContratPlusDateEndFactActive').'</td>';
	if ($conf->global->CONTRATPLUS_FACTURE_EXTRA_DATE_END == 1) {
		print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setfacturedateend&status=0">';
		print img_picto($langs->trans("Activated"), 'switch_on');
		print '</a></td>';
	} else {
		print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setfacturedateend&status=1">';
		print img_picto($langs->trans("Disabled"), 'switch_off');
		print '</a></td>';
	}

	print '</tr>';



	print '</table>';

    print '</form>';
}








// Page end
dol_fiche_end();

llxFooter();
$db->close();
