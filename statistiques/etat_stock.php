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


llxHeader("",$langs->trans("Stock"));

print load_fiche_titre($langs->trans("Stock"),'','title_accountancy.png');

print '<div class="fichecenter"><div class="fichethirdleft">';




if ($user->rights->statistiques->Stock->SAStock)
{	
$mydate=dol_mktime(12,0,0,$_POST['mykeymonth'], $_POST['mykeyday'],$_POST['mykeyyear']);


	
print '<div class="liste_titre liste_titre_bydiv centpercent">';
	print '<div class="div-table-responsive">';
								print '<table class="tagtable liste'.($moreforfilter ? " listwithfilterbefore" : "").'">'."\n";
							print '<tr class="liste_titre">';
							print '<td>'.$langs->trans("Ref").'</td>';
							print '<td>'.$langs->trans("Designation").'</td>';
							print '<td>'.$langs->trans("Quantité").'</td>';
							print '<td>'.$langs->trans("PMP").'</td>';
							print '<td>'.$langs->trans("Total").'</td>';
							print '</tr>';
	

//trouver la valeur maxi
$resqlst=$db->query('SELECT '.MAIN_DB_PREFIX.'product.pmp as pmp, '.MAIN_DB_PREFIX.'product.rowid as rowid,'.MAIN_DB_PREFIX.'product.ref as ref,'.MAIN_DB_PREFIX.'product.label as label 
			FROM '.MAIN_DB_PREFIX.'product');

if ($resqlst)
{
	$num = $db->num_rows($resqlst);
	$i = 0;
	if ($num)
	{
		while ($i < $num)
		{
			$obj = $db->fetch_object($resqlst);
			if ($obj)
			{
				
			
											
									
				
				
					$resqlst2=$db->query('SELECT sum('.MAIN_DB_PREFIX.'stock_mouvement.value) as qte 
					FROM '.MAIN_DB_PREFIX.'stock_mouvement
					 where '.MAIN_DB_PREFIX.'stock_mouvement.fk_product='.$obj->rowid.' and '.MAIN_DB_PREFIX.'stock_mouvement.datem <= "'.strftime('%Y-%m-%d', $mydate).'";');



					if ($resqlst2)
					{
						$num2 = $db->num_rows($resqlst2);
						$j = 0;
						if ($num2)
						{
							while ($j < $num2)
							{
								$obj2 = $db->fetch_object($resqlst2);
								if ($obj2)
								{	
									if ($obj2->qte!=0)
									{
										print '<tr class="oddeven">';
										echo '<td class="center nowraponall">'.$obj->ref.'</td>';
										print '<td class="tdoverflowmax200">'.dol_trunc($obj->label, 80).'</td>';	
										echo '<td class="right nowrap">'.$obj2->qte.'</td>';
										echo '<td class="right nowrap">'.number_format($obj->pmp, 2, ',', ' ').'</td>';
										echo '<td class="right nowrap">'.number_format(($obj->pmp*$obj2->qte), 2, ',', ' ').'</td>';
										print '</tr>';
									}
								}
								$j++;
							}
						}
					}
				
											
				
				
	
				
				
				
				

			}
			$i++;
		}
	}
}
			
print '</table></div></div>';



					//if (empty($socid))
//{
	// Show filter box
	print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="mode" value="'.$mode.'">';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre"><td class="liste_titre" colspan="2">'.$langs->trans("Filter").'</td></tr>';



	
		print '</td></tr>';
	print '<tr><td>';
		$form->select_date('','mykey',0,0,0,"myform");
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
