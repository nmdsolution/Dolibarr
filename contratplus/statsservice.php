<?php
/* Copyright (C) 2017-2018	Eric GROULT			    <eric@code42.fr>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *       \file       htdocs/contrat/list.php
 *       \ingroup    contrat
 *       \brief      Page liste des contrats
 */

$res = 0;
if (! $res && file_exists("../main.inc.php")) $res=@include_once "../main.inc.php";         // to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php")) $res=@include_once "../../main.inc.php";       // to work if your module directory is into a subdir of root htdocs directory
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT."/contrat/class/contrat.class.php";
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT."/cron/class/cronjob.class.php";
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';

dol_include_once('contratplus/lib/contratplus.lib.php');
dol_include_once('contratplus/lib/contratplusstats.lib.php');
dol_include_once('contratplus/class/serviceStats.class.php');

global $db, $langs, $user, $conf;

$now = dol_now();
$form = new Form($db);

$langs->load('contratplus@contratplus');

// Security check
$id=GETPOST('id', 'int');
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'contrat', $id);

$userid=GETPOST('userid', 'int');
$socid=GETPOST('socid', 'int');
// Security check
if ($user->societe_id > 0)
{
    $action = '';
    $socid = $user->societe_id;
}
$nowyear=strftime("%Y", dol_now());
$year = GETPOST('year')>0?GETPOST('year'):$nowyear;
$startyear=$year-1;
$endyear=$year;

$stat = new ServiceStats($db);

if (!empty($userid) && $userid!=-1) $stat->userid=$userid;
if (!empty($socid)  && $socid!=-1) $stat->socid=$socid;
if (!empty($year)) $stat->year=$year;

$stat->year=0;
$data_all_year = $stat->getAllByYear('customer', $userid, $socid);
if (!empty($year)) $stat->year=$year;
$arrayyears=array();
foreach($data_all_year as $val) {
    $arrayyears[$val['year']]=$val['year'];
}
if (! count($arrayyears)) $arrayyears[$nowyear]=$nowyear;

/*
 * Action
 */




/*
 * View
 */

//
// First graph : get all customer service in function of status
//
$data = $stat->getAllServiceByStatusPlus('customer', $year, $userid, $socid);
$filenamenb = $conf->contratplus->dir_output . "/stats/servicecustomercontractbystatut.png";
$fileurlnb = DOL_URL_ROOT . '/viewimage.php?modulepart=servicestats&amp;file=servicecontractbystatut.png';
$firstpie = loadPie($langs->trans('ServiceNbByStatutForYear'), $data, $filenamenb, $fileurlnb, $stat->error);

//
// Secong graph : get all services by month
//
$data = $stat->getNbByMonthWithPrevYear($endyear, $startyear, 'customer', $userid, $socid);
$filenamenb = $conf->contratplus->dir_output . "/stats/servicenbprevyear-".$year.".png";
$fileurlnb = DOL_URL_ROOT . '/viewimage.php?modulepart=servicestats&amp;file=servicenbprevyear-'.$year.'.png';
$secondpie = loadDepth($langs->trans('ServiceNbServiceByMonth'), $data, $filenamenb, $fileurlnb, $startyear, $endyear);

//
// Third graph : get ca of all services by month
//
$data = $stat->getCAByMonthWithPrevYear($endyear, $startyear, 'customer', $userid, $socid);
$filenamenb = $conf->contratplus->dir_output . "/stats/servicecaprevyear-".$year.".png";
$fileurlnb = DOL_URL_ROOT . '/viewimage.php?modulepart=servicestats&amp;file=servicecaprevyear-'.$year.'.png';
$thirdpie = loadDepth($langs->trans('ServiceCAServiceByMonth'), $data, $filenamenb, $fileurlnb, $startyear, $endyear);

//declare llx_header here to see form confirm
llxHeader();

print load_fiche_titre($langs->trans("ContratP"), '', 'object_contract.png');
   // print_barre_liste($langs->trans("ContratPlusindex"), $page, $_SERVER["PHP_SELF"], '', $sortfield, $sortorder, $massactionbutton, $num, $totalnboflines, 'object_contract.png', 0, '', '', $limit);
if (!empty($conf->global->CONTRATPLUS_WIZARD_INDEX) && $conf->global->CONTRATPLUS_WIZARD_ACTIVE == 1) {
    print '<div class="cp-demo-div">';
    print $conf->global->CONTRATPLUS_WIZARD_INDEX;
    print '</div>';
    print '<div class="clearboth"></div>';
}

$head = contractStatsPrepareHead();

dol_fiche_head($head, 'servicestats', $langs->trans("CPStats"), 0, '', 1);

print '<div class="fichecenter">';
print '<table class="noborder" width="100%"><tr valign="top"><td align="center">';
print '<div class="fichehalfleft">';

print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<table class="border" width="100%">';
print '<tr class="liste_titre"><td class="liste_titre" colspan="2">'.$langs->trans("Filter").'</td></tr>';
// Company
print '<tr><td>'.$langs->trans("ThirdParty").'</td><td>';
$filter='';
print $form->select_thirdparty_list($socid, 'socid', $filter, 1);
print '</td></tr>';
// User
print '<tr><td>'.$langs->trans("Commercial").'</td><td>';
print $form->select_dolusers($userid, 'userid', 1, getInactiveUsers(), 0);
print '</td></tr>';
// Year
print '<tr><td>'.$langs->trans("Year").'</td><td>';
if (! in_array($year, $arrayyears)) $arrayyears[$year]=$year;
if (! in_array($nowyear, $arrayyears)) $arrayyears[$nowyear]=$nowyear;
arsort($arrayyears);
print $form->selectarray('year', $arrayyears, $year, 0);
print '</td></tr>';
print '<tr><td align="center" colspan="2"><input type="submit" name="submit" class="button" value="'.$langs->trans("Refresh").'"></td></tr>';
print '</table>';
print '</form>';

print '<table class="border" width="100%">';
print '<tr class="liste_titre" style="height:24px">';
print '<td align="center">'.$langs->trans("Year").'</td>';
print '<td align="center">'.$langs->trans("NbService").'</td>';
print '<td align="center">'.$langs->trans("CAService").'</td>';
print '</tr>';
print '<br><br>';

$oldyear=0;
foreach ($data_all_year as $val)
{
    $year = $val['year'];
    while ($year && $oldyear > $year+1)
    {	// If we have empty year
        $oldyear--;
        print '<tr style="height:24px">';
        print '<td align="center"><a href="'.$_SERVER["PHP_SELF"].'?year='.$oldyear.($socid>0?'&socid='.$socid:'').($userid>0?'&userid='.$userid:'').'">'.$oldyear.'</a></td>';
        print '<td align="center">0</td>';
        print '<td align="center">0</td>';
        print '</tr>';
    }
    print '<tr style="height:24px">';
    if ($year)
        print '<td align="center"><a href="'.$_SERVER["PHP_SELF"].'?year='.$year.($socid>0?'&socid='.$socid:'').($userid>0?'&userid='.$userid:'').'">'.$year.'</a></td>';
    else {
        $url = dol_buildpath('/contratplus/services.php?search_no_date=1&search_cli_four=0', 1);
        print '<td align="center"><i class="fa fa-exclamation-triangle" aria-hidden="true" style="color: red"></i> <a href="'.$url.'">'.$langs->trans('ServiceWithoutDate').'</a></td>';
    }
    print '<td align="center">'.$val['nb'].'</td>';
    print '<td align="center">'.price2num($val['total'], 'MU').' €</td>';
    print '</tr>';

    $oldyear=$year;
}

print '</table>';

print '</div><div class="fichehalfright">';

//
// Show pie on the right
//
print $firstpie;

print '</div>';
print '</table></div>';

//
// Second row
//
print '<div class="fichecenter">';
print '<table class="noborder" width="100%"><tr valign="top"><td align="center">';
print '<div class="fichehalfleft">';
print $secondpie;
print '</div>';
print '<div class="fichehalfright">';
print $thirdpie;
print '</div>';
print '</td></tr></table>';
print '</div>';

llxFooter();
$db->close();
