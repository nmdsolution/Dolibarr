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
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

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

llxHeader("",$langs->trans("TopProduit"));

print '<div class="fichecenter"><div class="fichethirdleft">';
if ($user->rights->statistiques->Classements->SAClassements)
{
print load_fiche_titre($langs->trans("TopProduits"),'','title_accountancy.png');


if (GETPOST('anneedeb')>1)
{

	$debut=GETPOST('anneedeb');

}
else
{
	$debut=DATE("Y");

}


	echo $langs->trans("Year").' '.$debut.'<br>';







					

    $lastlineisempty=false;

	print '<div class="div-table-responsive-no-min">';
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre">';
    print '<td>'.$langs->trans("Ref").'</td>';
    print '<td>'.$langs->trans("Product").'</td>';
    print '<td>'.$langs->trans("Quantity").'</td>';
    print '<td>'.$langs->trans("Invoices").'</td>';
    print '</tr>';
					

$resql4=$db->query('SELECT '.MAIN_DB_PREFIX.'product.rowid as id,'.MAIN_DB_PREFIX.'product.ref as ref, '.MAIN_DB_PREFIX.'product.label as label, SUM('.MAIN_DB_PREFIX.'facturedet.qty) as nombre, count('.MAIN_DB_PREFIX.'facture.rowid) as nbfact FROM `'.MAIN_DB_PREFIX.'product`
inner join '.MAIN_DB_PREFIX.'facturedet on '.MAIN_DB_PREFIX.'facturedet.fk_product='.MAIN_DB_PREFIX.'product.rowid
inner join '.MAIN_DB_PREFIX.'facture on '.MAIN_DB_PREFIX.'facturedet.fk_facture='.MAIN_DB_PREFIX.'facture.rowid
where '.MAIN_DB_PREFIX.'product.fk_product_type=0 and (YEAR('.MAIN_DB_PREFIX.'facture.datec)="'.$debut.'")
group by label
order by nombre DESC limit 30;');								
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
													
					echo '<tr><td><a href="'.DOL_URL_ROOT.'/product/card.php?id='.$obj4->id.'">'.$obj4->ref.'</td><td>'.$obj4->label.'</td><td style="text-align:right;">'.round(($obj4->nombre),0).'</td><td style="text-align:right;">'.round(($obj4->nbfact),0).'</td></tr>';
					
					
					
					
					
					
					
					
					
					
					
					
					
				
					
					
					
					
					
					

				}
				$m++;
			}
		}
	}echo '</table>';


	// Show filter box
	print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="mode" value="'.$mode.'">';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre"><td class="liste_titre" colspan="2">'.$langs->trans("Filter").'</td></tr>';


	// Year
	print '<tr><td>'.$langs->trans("YearAnalysed").'</td><td>';

	

	print '<input type="hidden" name="anneedeb">';?>
								<select class="field" name="anneedeb" id="annedeb">
								
									<?php
										$i=0;
										while ($i < 10)
										{		
											if ((GETPOST('anneedeb')==(Date('Y')-$i)))
											{
												echo '<OPTION selected id="'.(Date('Y')-$i).'">'.(Date('Y')-$i).'</OPTION>'; 
											}
											else
											{
												echo '<OPTION id="'.(Date('Y')-$i).'">'.(Date('Y')-$i).'</OPTION>'; 
											}	
										$i++;
										}?>
								</select>

								
	
								
								<?php

	
	print '</td></tr>';
	print '<tr><td align="center" colspan="2"><input type="submit" name="submit" class="button" value="'.$langs->trans("Refresh").'"></td></tr>';
	print '</table>';
	print '</form>';
	print '<br><br>';
				
	}
else
{
	echo $langs->trans("Accès non autorisé.");
}					
					



llxFooter();

$db->close();
