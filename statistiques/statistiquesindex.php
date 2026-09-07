<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-FranÃ§ois Ferry	<jfefe@aternatik.fr>
 *


/**
 *	\file       htdocs/statistiques/template/statistiquesindex.php
 *	\ingroup    statistiques
 *	\brief      Home page of statistiques top menu
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include dirname(substr($tmp, 0, ($i+1)))."/main.inc.php";
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include "../main.inc.php";
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

// Load translation files required by the page
$langs->loadLangs(array("statistiques@statistiques"));

$action=GETPOST('action', 'alpha');


// Securite acces client

if (isset($user->societe_id) && $user->societe_id > 0)
{
	$action = '';
	$socid = $user->societe_id;
}

$max=5;
$now=dol_now();


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("",$langs->trans("Statistics"));

print load_fiche_titre($langs->trans("Statistics"),'','statistiques.png@statistiques');


$NBMAX=3;
$max=3;

if ($user->rights->statistiques->Ventes->SAVentes)
{
print '<div style="display:block; width:653px; margin-top:10px; text-align:left; vertical-align:bottom; border-radius:10px; background:#5472ae;"><img style="display:inline-block; width:35px; height:35px;background:#fff;margin:10px;border:1px solid #fff; border-radius:25px;" src="img/statistiques.png"><A style="display:inline-block; color:#ddd;padding-top:20px; vertical-align:top;font-size:20px;" HREF="ventes.php">'. $langs->trans("Sells").'</a></div>';
		
	print '<a href="ventes_produits_services.php"><div style="display:inline-block; width:153px; height:115px; border:0px #fff solid; margin:5px; text-align:center; vertical-align:bottom; border-radius:10px; background:#cccccc;"><P style="text-align:center; vertical-align:bottom; height:45px; font-weight:bold;">'. $langs->trans("SellsPerProductsServices").'</p><img src="img/statistiques.png"></a></div>';
	print '<a href="repartition_par_type_produits.php"><div style="display:inline-block; width:153px; height:115px; border:0px #fff solid; margin:5px; text-align:center; vertical-align:bottom; border-radius:10px; background:#cccccc;"><P style="text-align:center; vertical-align:bottom; height:45px; font-weight:bold;">'. $langs->trans("ProductServiceCategoryDistribution").'</p><img src="img/circ.png"></a></div>';
	print '<a href="repartition_par_familles_clients.php"><div style="display:inline-block; width:153px; height:115px; border:0px #fff solid; margin:5px; text-align:center; vertical-align:bottom; border-radius:10px; background:#cccccc;"><P style="text-align:center; vertical-align:bottom; height:45px; font-weight:bold;">'. $langs->trans("CustomerCategoryDistribution").'</p><img src="img/circ.png"></a></div>';
	print '<a href="ventes_par_familles.php"><div style="display:inline-block; width:153px; height:115px; border:0px #fff solid; margin:5px; text-align:center; vertical-align:bottom; border-radius:10px; background:#cccccc;"><P style="text-align:center; vertical-align:bottom; height:45px; font-weight:bold;">'. $langs->trans("ProductServiceCategoryEvolution").'</p><img src="img/line.png"></a></div>';
	print '<a href="ventes_multicriteres.php"><div style="display:inline-block; width:153px; height:115px; border:0px #fff solid; margin:5px; text-align:center; vertical-align:bottom; border-radius:10px; background:#cccccc;"><P style="text-align:center; vertical-align:bottom; height:45px; font-weight:bold;">'. $langs->trans("SellsMulticriteria").'</p><img src="img/statistiques.png"></a></div>';
	print '<a href="ventes_par_familles_clients.php"><div style="display:inline-block; width:153px; height:115px; border:0px #fff solid; margin:5px; text-align:center; vertical-align:bottom; border-radius:10px; background:#cccccc;"><P style="text-align:center; vertical-align:bottom; height:45px; font-weight:bold;">'. $langs->trans("CustomerCategoryEvolution").'</p><img src="img/line.png"></a></div>';
}

if ($user->rights->statistiques->Stock->SAStock)
{	
print '<div style="display:block; width:653px; margin-top:10px;text-align:left; vertical-align:bottom; border-radius:10px; background:#5472ae;"><img style="display:inline-block; width:35px; height:35px;background:#fff;margin:10px;border:1px solid #fff; border-radius:25px;" src="img/statistiques.png"><A style="display:inline-block; color:#ddd;padding-top:20px; vertical-align:top;font-size:20px;" HREF="stock.php">'. $langs->trans("Stock").'</a></div>';
}

if ($user->rights->statistiques->Achats->SAAchats)
{
print '<div style="display:block; width:653px; margin-top:10px;text-align:left; vertical-align:bottom; border-radius:10px; background:#5472ae;"><img style="display:inline-block; width:35px; height:35px;background:#fff;margin:10px;border:1px solid #fff; border-radius:25px;" src="img/statistiques.png"><A style="display:inline-block; color:#ddd;padding-top:20px; vertical-align:top;font-size:20px;" HREF="achats.php">'. $langs->trans("Purchases").'</a></div>';

print '<a href="repartition_achats_par_fournisseurs.php"><div style="display:inline-block; width:153px; height:115px; border:0px #fff solid; margin:5px; text-align:center; vertical-align:bottom; border-radius:10px; background:#cccccc;"><P style="text-align:center; vertical-align:bottom; height:45px; font-weight:bold;">'. $langs->trans("SupplierDistribution").'</p><img src="img/circ.png"></a></div>';
print '<a href="repartition_origine.php"><div style="display:inline-block; width:153px; height:115px; border:0px #fff solid; margin:5px; text-align:center; vertical-align:bottom; border-radius:10px; background:#cccccc;"><P style="text-align:center; vertical-align:bottom; height:45px; font-weight:bold;">'. $langs->trans("CountryOfOriginDistribution").'</p><img src="img/circ.png"></a></div>';
print '<a href="achats_par_fournisseurs.php"><div style="display:inline-block; width:153px; height:115px; border:0px #fff solid; margin:5px; text-align:center; vertical-align:bottom; border-radius:10px; background:#cccccc;"><P style="text-align:center; vertical-align:bottom; height:45px; font-weight:bold;">'. $langs->trans("SuppliersComparison").'</p><img src="img/line.png"></a></div>';
}

if ($user->rights->statistiques->Clients->SAClients)
{
print '<div style="display:block; width:653px; margin-top:10px;text-align:left; vertical-align:bottom; border-radius:10px; background:#5472ae;"><img style="display:inline-block; width:35px; height:35px;background:#fff;margin:10px;border:1px solid #fff; border-radius:25px;" src="img/statistiques.png"><A style="display:inline-block; color:#ddd;padding-top:20px; vertical-align:top;font-size:20px;" HREF="clients_crees_par_mois.php">'. $langs->trans("Customers").'</a></div>';


print '<a href="clients_categorie.php"><div style="display:inline-block; width:153px; height:115px; border:0px #fff solid; margin:5px; text-align:center; vertical-align:bottom; border-radius:10px; background:#cccccc;"><P style="text-align:center; vertical-align:bottom; height:45px; font-weight:bold;">'.$langs->trans("ClientsCréésParMois").'</p><img src="img/statistiques.png"></a></div>';

print '<a href="delai_paiement_client.php"><div style="display:inline-block; width:153px; height:115px; border:0px #fff solid; margin:5px; text-align:center; vertical-align:bottom; border-radius:10px; background:#cccccc;"><P style="text-align:center; vertical-align:bottom; height:45px; font-weight:bold;">'. $langs->trans("PaymentTime").'</p><img src="img/statistiques.png"></a></div>';
}
if ($user->rights->statistiques->Classements->SAClassements)
{
print '<div style="display:block; width:653px; margin-top:10px;text-align:left; vertical-align:bottom; border-radius:10px; background:#5472ae;"><img style="display:inline-block; width:35px; height:35px;background:#fff;margin:10px;border:1px solid #fff; border-radius:25px;" src="img/statistiques.png"><A style="display:inline-block; color:#ddd;padding-top:20px; vertical-align:top;font-size:20px;">Ranking</a></div>';

print '<a href="top_clients.php"><div style="display:inline-block; width:153px; height:115px; border:0px #fff solid; margin:5px; text-align:center; vertical-align:bottom; border-radius:10px; background:#cccccc;"><P style="text-align:center; vertical-align:bottom; height:45px; font-weight:bold;">'.$langs->trans("TopCustomers").'</p><img src="img/barresh.png"></a></div>';

print '<a href="top_villes.php"><div style="display:inline-block; width:153px; height:115px; border:0px #fff solid; margin:5px; text-align:center; vertical-align:bottom; border-radius:10px; background:#cccccc;"><P style="text-align:center; vertical-align:bottom; height:45px; font-weight:bold;">'.$langs->trans("TopTowns").'</p></a><img src="img/barresh.png"></div>';
print '<a href="top_produits.php"><div style="display:inline-block; width:153px; height:115px; border:0px #fff solid; margin:5px; text-align:center; vertical-align:bottom; border-radius:10px; background:#cccccc;"><P style="text-align:center; vertical-align:bottom; height:45px; font-weight:bold;">'.$langs->trans("TopProducts").'</p><img src="img/barresh.png"></a></div>';
print '<a href="top_departements.php"><div style="display:inline-block; width:153px; height:115px; border:0px #fff solid; margin:5px; text-align:center; vertical-align:bottom; border-radius:10px; background:#cccccc;"><P style="text-align:center; vertical-align:bottom; height:45px; font-weight:bold;">'.$langs->trans("TopDepartment").'</p><img src="img/barresh.png"></a></div>';
}
if ($user->rights->statistiques->Devis->SADevis)
{
	
print '<div style="display:block; width:653px; margin-top:10px;text-align:left; vertical-align:bottom; border-radius:10px; background:#5472ae;"><img style="display:inline-block; width:35px; height:35px;background:#fff;margin:10px;border:1px solid #fff; border-radius:25px;" src="img/statistiques.png"><A style="display:inline-block; color:#ddd;padding-top:20px; vertical-align:top;font-size:20px;" HREF="transformation_devis.php">'.$langs->trans("QuotesValidated").'</a></div>';
}


/* BEGIN MODULEBUILDER LASTMODIFIED MYOBJECT
// Last modified myobject
if (! empty($conf->statistiques->enabled) && $user->rights->statistiques->read)
{
	$sql = "SELECT s.rowid, s.nom as name, s.client, s.datec, s.tms, s.canvas";
    $sql.= ", s.code_client";
	$sql.= " FROM ".MAIN_DB_PREFIX."societe as s";
	if (! $user->rights->societe->client->voir && ! $socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql.= " WHERE s.client IN (1, 2, 3)";
	$sql.= " AND s.entity IN (".getEntity($companystatic->element).")";
	if (! $user->rights->societe->client->voir && ! $socid) $sql.= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	if ($socid)	$sql.= " AND s.rowid = $socid";
	$sql .= " ORDER BY s.tms DESC";
	$sql .= $db->plimit($max, 0);

	$resql = $db->query($sql);
	if ($resql)
	{
		$num = $db->num_rows($resql);
		$i = 0;

		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<th colspan="2">';
		if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) print $langs->trans("BoxTitleLastCustomersOrProspects",$max);
        else if (! empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) print $langs->trans("BoxTitleLastModifiedProspects",$max);
		else print $langs->trans("BoxTitleLastModifiedCustomers",$max);
		print '</th>';
		print '<th align="right">'.$langs->trans("DateModificationShort").'</th>';
		print '</tr>';
		if ($num)
		{
			while ($i < $num)
			{
				$objp = $db->fetch_object($resql);
				$companystatic->id=$objp->rowid;
				$companystatic->name=$objp->name;
				$companystatic->client=$objp->client;
                $companystatic->code_client = $objp->code_client;
                $companystatic->code_fournisseur = $objp->code_fournisseur;
                $companystatic->canvas=$objp->canvas;
				print '<tr class="oddeven">';
				print '<td class="nowrap">'.$companystatic->getNomUrl(1,'customer',48).'</td>';
				print '<td align="right" nowrap>';
				print $companystatic->getLibCustProspStatut();
				print "</td>";
				print '<td align="right" nowrap>'.dol_print_date($db->jdate($objp->tms),'day')."</td>";
				print '</tr>';
				$i++;


			}

			$db->free($resql);
		}
		else
		{
			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("None").'</td></tr>';
		}
		print "</table><br>";
	}
}
*/

print '</div></div></div>';

// End of page
llxFooter();
$db->close();
