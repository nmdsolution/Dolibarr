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
 * \file    contratplus/admin/configwizard.php
 * \ingroup contratplus
 * \brief   ContratPlus Wizard setup page.
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

$wizard = array(
    "wizardindex"      => $conf->global->CONTRATPLUS_WIZARD_INDEX,
    "wizardcontrat"    => $conf->global->CONTRATPLUS_WIZARD_CONTRAT,
    "wizardservice"      => $conf->global->CONTRATPLUS_WIZARD_SERVICE,
);

if ($action == 'confirmedit') {
    // Set all demo to a value
    if (!empty(GETPOST('submit'))) {
        $wizardindex = empty(GETPOST('wizardindex')) ? $conf->global->CONTRATPLUS_WIZARD_INDEX : GETPOST('wizardindex');
        $wizardcontrat = empty(GETPOST('wizardcontrat')) ? $conf->global->CONTRATPLUS_WIZARD_CONTRAT : GETPOST('wizardcontrat');
        $wizardservice = empty(GETPOST('wizardservice')) ? $conf->global->CONTRATPLUS_WIZARD_SERVICE : GETPOST('wizardservice');

        $res = dolibarr_set_const($db, 'CONTRATPLUS_WIZARD_INDEX', $wizardindex);
        if (! $res > 0)
            $error++;

        $res = dolibarr_set_const($db, 'CONTRATPLUS_WIZARD_CONTRAT', $wizardcontrat);
        if (! $res > 0)
            $error++;

        $res = dolibarr_set_const($db, 'CONTRATPLUS_WIZARD_SERVICE', $wizardservice);
        if (! $res > 0)
            $error++;


        if ($error > 0)
            setEventMessages($langs->trans("Error"), '', 'errors');
        else
            setEventMessages($langs->trans("SetupSaved"), '', 'mesgs');
    }
}

// Activate or deactivate the link with inter module
if ($action == "setwizardactive") {
    $value = GETPOST("status", "int");

    // Set const value
    dolibarr_set_const($db, 'CONTRATPLUS_WIZARD_ACTIVE', $value);
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

dol_fiche_head($head, 'wizardsetup', "Contrat Plus", 0, 'contratplus@contratplus');

print '<h1>'.$langs->trans('SetupWizard').'</h1>';

print '<form name="ContratPlusSetup">';

if ($action == 'edit')
    print '<input type="hidden" name="action" value="confirmedit">';

if ($action != 'edit') {
    print '<table class="noborder" width="100%"><tbody>';
    print '<tr class="liste_titre">';
    print '<td><h4 align="left" width="30%">'.$langs->trans('Parameter').'</h4></td>';
    print '<td align="center" width="70%"><h4>'.$langs->trans('Value').'</h4></td>';
    print '</tr>';
    print '<tr>';
    print '<td>'.$langs->trans('ActivateWizardMode').'</td>';
    if ($conf->global->CONTRATPLUS_WIZARD_ACTIVE == 1) {
        print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setwizardactive&status=0">';
        print img_picto($langs->trans("Activated"), 'switch_on');
        print '</a></td>';
    } else {
        print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setwizardactive&status=1">';
        print img_picto($langs->trans("Disabled"), 'switch_off');
        print '</a></td>';
    }

    print '</tr>';
    print '</table>';
}

print '<table class="noborder" width="100%"><tbody>';
print '<tr class="liste_titre">';
print '<td><h4 align="left" width="30%">'.$langs->trans('Parameter').'</h4></td>';
print '<td align="center" width="70%"><h4>'.$langs->trans('Value').'</h4></td>';
print '</tr>';

foreach($wizard as $key => $wiz) {
    print '<tr class="oddeven">';
    print '<td>'.$langs->trans($key).'</td>';
    if ($action == 'edit') {
        print '<td width="70%">';
        $doleditor = new DolEditor($key, $wiz, '', 142, 'dolibarr_notes', 'In', false, true, true, ROWS_4, '90%');
        $doleditor->Create();
        print '</td>';
    }
    else
        print '<td align="left">'.$wiz.'</span></td>';
    print '</tr>';
}
print '</tbody></table><br />';
print '<div style="width: 100%" align="center">';
if ($action == 'edit') {
    print '<div class="center">';
    print '<input class="button" type="submit" name="submit" value="'.$langs->trans('Save').'"> &nbsp;';
    print '<input class="button" type="submit" name="cancel" value="'.$langs->trans('Cancel').'"></div>';
}
else
    print '<a href="'.$_SERVER['PHP_SELF'].'?action=edit" class="button">'.$langs->trans('Modify').'</a>';

print '</div>';

print '</form>';

// Page end
dol_fiche_end();

llxFooter();
$db->close();
