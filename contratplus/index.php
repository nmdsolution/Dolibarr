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
dol_include_once('contratplus/class/contractStats.class.php');
dol_include_once('contratplus/class/serviceStats.class.php');
dol_include_once('contratplus/class/customcontrat.class.php');

global $db, $langs, $user, $conf;

$now = dol_now();
$form = new Form($db);

$langs->load('contratplus@contratplus');


// Security check
$id=GETPOST('id', 'int');
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'contrat', $id);

/*
 * Action
 */




/*
 * View
 */

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

dol_fiche_head($head, 'stats', $langs->trans("CPStats"), 0, '', 1);

// Graph section
$stat = new ContractStats($db);
$servicestat = new ServiceStats($db);

//
// Customer Pie : get all customer contracts in function of status
//
$data = $stat->getAllContractByStatus('customer');
$filenamenb = $conf->contratplus->dir_output . "/stats/customercontractbystatut.png";
$fileurlnb = DOL_URL_ROOT . '/viewimage.php?modulepart=contractstats&amp;file=customercontractbystatut.png';
$customercontractpie = loadPie($langs->trans('CustomerContractNbByStatut'), $data, $filenamenb, $fileurlnb, $stat->error);

//
// Customer Pie : get all customer services in function of status
//
$data = $servicestat->getAllServiceByStatusPlus('customer');
$filenamenb = $conf->contratplus->dir_output . "/stats/customerservicebystatut.png";
$fileurlnb = DOL_URL_ROOT . '/viewimage.php?modulepart=servicestats&amp;file=customerservicebystatut.png';
$customerservicepie = loadPie($langs->trans('CustomerServiceNbByStatut'), $data, $filenamenb, $fileurlnb, $servicestat->error);


//
// Supplier Pie : get all supplier contracts in function of status
//
$data = $stat->getAllContractByStatus('supplier');
$filenamenb = $conf->contratplus->dir_output . "/stats/suppliercontractbystatut.png";
$fileurlnb = DOL_URL_ROOT . '/viewimage.php?modulepart=contractstats&amp;file=suppliercontractbystatut.png';
$suppliercontractpie = loadPie($langs->trans('FournContractNbByStatut'), $data, $filenamenb, $fileurlnb, $stat->error);

//
// Supplier Pie : get all supplier services in function of status
//
$data = $servicestat->getAllServiceByStatusPlus('supplier');
$filenamenb = $conf->contratplus->dir_output . "/stats/supplierservicebystatut.png";
$fileurlnb = DOL_URL_ROOT . '/viewimage.php?modulepart=servicestats&amp;file=supplierservicebystatut.png';
$supplierservicepie = loadPie($langs->trans('SupplierServiceNbByStatut'), $data, $filenamenb, $fileurlnb, $servicestat->error);


//
// Set all list to display
//
$customerdraftcontractlisting = draftContractListing($conf->liste_limit, 'customer');
$customercreatedcontractlisting = lastCreatedContractListing($conf->liste_limit, 'customer');
$supplierdraftcontractlisting = draftContractListing($conf->liste_limit, 'supplier');
$suppliercreatedcontractlisting = lastCreatedContractListing($conf->liste_limit, 'supplier');

//
// Display stats
//
// <div class="containercenter">
$stringtoshow = '<div class="fichecenter">';
$stringtoshow .= '<h2><i class="fa fa-user" aria-hidden="true"></i> '.$langs->trans('Customer').'</h2>';
$stringtoshow .= '<table class="noborder" width="100%"><tr valign="top"><td align="center">';
$stringtoshow .= '<div class="fichehalfleft"><div class="containercenter">';
$stringtoshow .= $customercontractpie;
$stringtoshow .= '</div></div><div class="fichehalfright"><div class="containercenter">';
$stringtoshow .= $customerservicepie;
$stringtoshow .= '</div></div>';
$stringtoshow .= '</div>';
$stringtoshow .= '</td></tr>';
$stringtoshow .= '</table>';

$stringtoshow .= '<table class="noborder" width="100%"><tr valign="top"><td align="center">';
$stringtoshow .= '<div class="fichehalfleft">';
$stringtoshow .= $customerdraftcontractlisting;
$stringtoshow .= '</div>';
$stringtoshow .= '<div class="fichehalfright">';
$stringtoshow .= $customercreatedcontractlisting;
$stringtoshow .= '</div>';
$stringtoshow .= '</td></tr>';
$stringtoshow .= '</table>';

if ($conf->global->CONTRATPLUS_SUPPLIER_CONTRACT) {
    $stringtoshow .= '<h2><i class="fa fa-truck" aria-hidden="true"></i> '.$langs->trans('Supplier').'</h2>';
    $stringtoshow .= '<table class="noborder" width="100%"><tr valign="top"><td align="center">';
    $stringtoshow .= '<div class="fichehalfleft"><div class="containercenter">';
    $stringtoshow .= $suppliercontractpie;
    $stringtoshow .= '</div></div>';
    $stringtoshow .= '<div class="fichehalfright"><div class="containercenter">';
    $stringtoshow .= $supplierservicepie;
    $stringtoshow .= '</div></div>';
    $stringtoshow .= '</td></tr>';
    $stringtoshow .= '</table>';

    $stringtoshow .= '<table class="noborder" width="100%"><tr valign="top"><td align="center">';
    $stringtoshow .= '<div class="fichehalfleft">';
    $stringtoshow .= $supplierdraftcontractlisting;
    $stringtoshow .= '</div>';
    $stringtoshow .= '<div class="fichehalfright">';
    $stringtoshow .= $suppliercreatedcontractlisting;
    $stringtoshow .= '</div>';
    $stringtoshow .= '</td></tr>';
    $stringtoshow .= '</table>';
}

print $stringtoshow;

llxFooter();
$db->close();
