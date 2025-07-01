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
 * \file    contratplus/admin/configpdf.php
 * \ingroup contratplus
 * \brief   ContratPlus Pdf setup page.
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



// Activate or deactivate the link with inter module
if ($action == "setContratPlusDateEnd") {
    $value = GETPOST("status", "int");

    // Set const value
    dolibarr_set_const($db, 'CONTRATPLUS_DATEEND_ACTIVE', $value);
}

if ($action == "setContratPlusDateStartContract") {
    $value = GETPOST("status", "int");

    // Set const value
    dolibarr_set_const($db, 'CONTRATPLUS_DATESTART_CONTRACT', $value);
}

if ($action == "setContratPlusDateEndContract") {
    $value = GETPOST("status", "int");

    // Set const value
    dolibarr_set_const($db, 'CONTRATPLUS_DATEEND_CONTRACT', $value);
}

if ($action == "setContratPlusDureeContract") {
    $value = GETPOST("status", "int");

    // Set const value
    dolibarr_set_const($db, 'CONTRATPLUS_DUREE_CONTRACT', $value);
}

if ($action == "setAll") {
    $value = GETPOST("status", "int");

    // Set const value
    dolibarr_set_const($db, 'CONTRATPLUS_PDF_ALL_CONTRACT', $value);
    dolibarr_set_const($db, 'CONTRATPLUS_DUREE_CONTRACT', $value);
    dolibarr_set_const($db, 'CONTRATPLUS_DATEEND_CONTRACT', $value);
    dolibarr_set_const($db, 'CONTRATPLUS_DATESTART_CONTRACT', $value);
}

if ($conf->global->CONTRATPLUS_DATESTART_CONTRACT == 1 && $conf->global->CONTRATPLUS_DUREE_CONTRACT == 1 && $conf->global->CONTRATPLUS_DATEEND_CONTRACT == 1)
{
    dolibarr_set_const($db, 'CONTRATPLUS_PDF_ALL_CONTRACT', 1);
} else {
    dolibarr_set_const($db, 'CONTRATPLUS_PDF_ALL_CONTRACT', 0);
}



if (!empty($action) && $action != "edit") {
    exit(header("Location:".$_SERVER['PHP_SELF']));
}

/*
 * View
 */

$morejs = array("/contratplus/js/contratPlus.js");
llxHeader('', $langs->trans("ContratPlusSetup"), '', '', '', '', $morejs, '', 0, 0);

$head = generateHeader();

dol_fiche_head($head, 'pdfsetup', "Contrat Plus", 0, 'contratplus@contratplus');



print '<form name="ContratPlusSetup">';

print '<h1>'.$langs->trans('SetupPdf').'</h1>';

if ($action == 'edit')
    print '<input type="hidden" name="action" value="confirmedit">';

if ($action != 'edit') {
    print '<table class="noborder" width="100%"><tbody>';
    print '<tr class="liste_titre">';
    print '<td><h4 align="left" width="30%">'.$langs->trans('Parameter').'</h4></td>';
    print '<td align="center" width="70%"><h4>'.$langs->trans('Value').'</h4></td>';
    print '</tr>';
    print '<tr>';
    print '<td>'.$langs->trans('ContratPlusDateEndActive').'</td>';
    if ($conf->global->CONTRATPLUS_DATEEND_ACTIVE == 1) {
        print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setContratPlusDateEnd&status=0">';
        print img_picto($langs->trans("Activated"), 'switch_on');
        print '</a></td>';
    } else {
        print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setContratPlusDateEnd&status=1">';
        print img_picto($langs->trans("Disabled"), 'switch_off');
        print '</a></td>';
    }

    print '</tr>';
    print '</table>';

    print '<h1>'.$langs->trans('SetupPdfContract').'</h1>';

    print '<table class="noborder" width="100%"><tbody>';
    print '<tr class="liste_titre">';
    print '<td><h4 align="left" width="30%">'.$langs->trans('Parameter').'</h4></td>';
    print '<td align="center" width="70%"><h4>'.$langs->trans('Value').'</h4></td>';
    print '</tr>';
    print '<tr>';
    print '<td><strong>'.$langs->trans('ContratPlusAllPdf').'</strong></td>';
    if ($conf->global->CONTRATPLUS_PDF_ALL_CONTRACT == 1) {
        print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setAll&status=0">';
        print img_picto($langs->trans("Activated"), 'switch_on');
        print '</a></td>';
    } else {
        print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setAll&status=1">';
        print img_picto($langs->trans("Disabled"), 'switch_off');
        print '</a></td>';
    }

    print '<tr>';
    print '<td>'.$langs->trans('ContratPlusDateStartPDF').'</td>';
    if ($conf->global->CONTRATPLUS_DATESTART_CONTRACT == 1) {
        print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setContratPlusDateStartContract&status=0">';
        print img_picto($langs->trans("Activated"), 'switch_on');
        print '</a></td>';
    } else {
        print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setContratPlusDateStartContract&status=1">';
        print img_picto($langs->trans("Disabled"), 'switch_off');
        print '</a></td>';
    }

    print '</tr>';
    print '<tr>';
    print '<td>'.$langs->trans('ContratPlusDureePDF').'</td>';
    if ($conf->global->CONTRATPLUS_DUREE_CONTRACT == 1) {
        print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setContratPlusDureeContract&status=0">';
        print img_picto($langs->trans("Activated"), 'switch_on');
        print '</a></td>';
    } else {
        print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setContratPlusDureeContract&status=1">';
        print img_picto($langs->trans("Disabled"), 'switch_off');
        print '</a></td>';
    }

    print '</tr>';
    print '<tr>';
    print '<td>'.$langs->trans('ContratPlusDateEndActive').'</td>';
    if ($conf->global->CONTRATPLUS_DATEEND_CONTRACT == 1) {
        print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setContratPlusDateEndContract&status=0">';
        print img_picto($langs->trans("Activated"), 'switch_on');
        print '</a></td>';
    } else {
        print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setContratPlusDateEndContract&status=1">';
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
