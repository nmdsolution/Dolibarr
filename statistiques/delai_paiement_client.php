 <?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-Fran?§ois Ferry	<jfefe@aternatik.fr>
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
 $now=dol_now();
 



 
 
 
 
 
 

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("",$langs->trans("PaymentTime"));

print '<div class="fichecenter"><div class="fichethirdleft">';
if ($user->rights->statistiques->Clients->SAClients)
{
print load_fiche_titre($langs->trans("PaymentTime"),'','title_accountancy.png');


if (GETPOST('anneedeb')>1)
{

	$debut=GETPOST('anneedeb');

}
else
{
	$debut=DATE("Y");

}


	//echo $langs->trans("AnnĂ©e").' '.$debut.'<br>';







					

    $lastlineisempty=false;

	print '<div class="div-table-responsive-no-min">';
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre">';
    //print '<td>'.$langs->trans("Module").'</td>';
    print '<td>'.$langs->trans("Customer").'</td>';
    print '<td>'.$langs->trans("PaymentTime").'</td>';
    print '<td>'.$langs->trans("Invoices").'</td>';
    print '</tr>';
					

$resql4=$db->query('SELECT '.MAIN_DB_PREFIX.'societe.nom as nom, AVG(DATEDIFF('.MAIN_DB_PREFIX.'paiement.datep,'.MAIN_DB_PREFIX.'facture.datef)) as delai, COUNT(DATEDIFF('.MAIN_DB_PREFIX.'paiement.datep,'.MAIN_DB_PREFIX.'facture.datef)) as nombre FROM `'.MAIN_DB_PREFIX.'paiement_facture`
inner join '.MAIN_DB_PREFIX.'paiement on '.MAIN_DB_PREFIX.'paiement_facture.fk_paiement='.MAIN_DB_PREFIX.'paiement.rowid
inner join '.MAIN_DB_PREFIX.'facture on '.MAIN_DB_PREFIX.'paiement_facture.fk_facture='.MAIN_DB_PREFIX.'facture.rowid
inner join '.MAIN_DB_PREFIX.'societe on '.MAIN_DB_PREFIX.'societe.rowid='.MAIN_DB_PREFIX.'facture.fk_soc
where '.MAIN_DB_PREFIX.'societe.client=1 or '.MAIN_DB_PREFIX.'societe.client=3
group by '.MAIN_DB_PREFIX.'societe.nom
order by delai DESC limit 30;');								
if ($resql4)
	{
		$num4 = $db->num_rows($resql4);
		$m = 0;
		if ($num4)
		{
			
			while ($m < $num4)
			{
				$obj4 = $db->fetch_object($resql4);
				if ($obj4)
				{
													
					echo '<tr><td>'.$obj4->nom.'</td><td style="text-align:right;">'.round(($obj4->delai),0).'</td><td style="text-align:right;">'.round(($obj4->nombre),0).'</td></tr>';
					
					
					
					
					
					
					
					
					
					
					
					
					
				
					
					
					
					
					
					

				}
				$m++;
			}
		}
	}echo '</table>';



				
	}
else
{
	echo $langs->trans("Accès non autorisé.");
}					
					



llxFooter();

$db->close();
