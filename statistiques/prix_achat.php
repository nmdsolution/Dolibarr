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


llxHeader("",$langs->trans("Prix d'achat"));

print load_fiche_titre($langs->trans("Prix d'achat"),'','title_accountancy.png');


print '<div class="fichecenter"><div class="fichethirdleft">';


echo '<section style="position:relative; display:inline-block;height:600px;width:1000px;background-color:#fff;">';



$resql=$db->query('SELECT label from '.MAIN_DB_PREFIX.'product where rowid='.GETPOST('familleid').'');

if ($resql)
{
	$num = $db->num_rows($resql);
		
			$obj = $db->fetch_object($resql);							
								echo $obj->label;			
	
}



if ($_POST["familleid"] > 0)
{
$varsearch_motcle_url='('.MAIN_DB_PREFIX.'facture_fourn_det.fk_product="'.GETPOST('familleid').'")';

					$max=1;
					$nbenr=1;

					
$resql=$db->query('SELECT '.MAIN_DB_PREFIX.'facture_fourn_det.pu_ht,'.MAIN_DB_PREFIX.'facture_fourn.datef from '.MAIN_DB_PREFIX.'facture_fourn_det inner join '.MAIN_DB_PREFIX.'facture_fourn on '.MAIN_DB_PREFIX.'facture_fourn.rowid='.MAIN_DB_PREFIX.'facture_fourn_det.fk_facture_fourn where'.$varsearch_motcle_url.'');

if ($resql)
{
	$num = $db->num_rows($resql);
	$i = 0;
	if ($num)
	{
		while ($i < $num)
		{
			$obj = $db->fetch_object($resql);
			if ($obj)
			{
				// You can use here results
					if ($obj->pu_ht>$max)
							{
								$max=$obj->pu_ht;	
							}
					$nbenr++;
				
			}
			$i++;
		}
	}
}
					$premiere_grad=round(($max/5),0);
					$coef=50/$premiere_grad;
					
					$largeur=(800/$nbenr);
					$largeur=round($largeur,2);

$resql=$db->query('SELECT '.MAIN_DB_PREFIX.'facture_fourn.fk_soc as fksoc, DAY('.MAIN_DB_PREFIX.'facture_fourn.datef) as jour, monthname('.MAIN_DB_PREFIX.'facture_fourn.datef) as mois, YEAR('.MAIN_DB_PREFIX.'facture_fourn.datef) as annee, '.MAIN_DB_PREFIX.'facture_fourn_det.pu_ht,'.MAIN_DB_PREFIX.'facture_fourn.datef from '.MAIN_DB_PREFIX.'facture_fourn_det inner join '.MAIN_DB_PREFIX.'facture_fourn on '.MAIN_DB_PREFIX.'facture_fourn.rowid='.MAIN_DB_PREFIX.'facture_fourn_det.fk_facture_fourn where'.$varsearch_motcle_url.'');

					echo '<div>';
					echo '<div style="diplay:inline-block;position:absolute; background-color:#222; width:3px; height:350px; left:70px;top:50px; margin:0px;"></div>';
					echo '<div style="diplay:inline-block;position:absolute; background-color:#fff; width:827px; height:350px;left:73px; top:50px;bottom:0px; margin:0px;">';
					
					$enr=0;

if ($resql)
{
	$num = $db->num_rows($resql);
	$i = 0;
	if ($num)
	{
		while ($i < $num)
		{
			$obj = $db->fetch_object($resql);
			if ($obj)
			{
					$resqlsoc=$db->query('select nom, rowid as fourn from '.MAIN_DB_PREFIX.'societe where rowid='.$obj->fksoc.'');
					if ($resqlsoc)
					{
						$objsoc=$db->fetch_object($resqlsoc);
						 if ($objsoc)
						 {
						echo'<div style="display: inline-block; position: absolute; left: '.(25+($largeur*$enr)).'px; width:'.($largeur-5).'px; text-align:center;top:0px; height:15px; border-radius:3px;z-index:1;">'.$objsoc->nom.'</div>';

						 }
					
					}
					//création d'une couleur aléatoire à partir de première lettre et dernière lettre du nom du fournisseur et dernier chiffre du code fournisseur
					// convertir nom du fournisseur en chaine hexadecimale
					$couleur=substr($objsoc->nom, 0, 1).substr($objsoc->nom, -1, 1);
				
					$couleur=substr(bin2hex($objsoc->nom),1,3).substr(bin2hex($objsoc->nom),-1,2);
					//echo $couleur.'<br>';
					//prendre les 6ers caratères pour créer couleur aléatoire
					
					
					//echo $couleur.'<br>';
					$longueur=($obj->fksoc)-round((strlen($objsoc->nom)),0);
					//echo $longueur.'<br>';
				
					$couleur=substr($longueur,-1).substr($obj->fksoc,-1).(substr($couleur, 0, 6));
					//echo $couleur.'<br>';
					echo'<div style="display: inline-block; position: absolute; left: '.(25+($largeur*$enr)).'px; width:'.($largeur-5).'px; bottom:0px; height:'.($obj->pu_ht*$coef).'px; border-radius:3px;background-color:#'.$couleur.';z-index:1;"></div>';
								
						
echo '<div style="display: inline-block; position: absolute; text-align:right; transform: rotate(315deg); left: '.(5+($largeur*$enr)).'px; width:'.($largeur+35).'px; bottom:-80px;  height:55px;text-align:center;color:#666;font-size:10px;">'.$obj->jour.' '.$langs->trans($obj->mois).' '.$obj->annee.'</div>';
					echo '<div style="display: inline-block; position: absolute; left: '.(25+($largeur*$enr)).'px; width:'.($largeur-5).'px; bottom:+0px; height:20px;background:rgba(175, 175, 175, 0.4);text-align:center;color:#333;z-index:3">'.round($obj->pu_ht,2).'</div>';			

							
							$enr++;
							
							
							
							
							
							
							
							
							
			
							
							
							
							
							
							
			}
			$i++;
		}

	}
}
							echo '</div>';
					echo '<div style="diplay:block;position:absolute; background-color:#222; width:830px; height:3px;left:70px; top:400px;bottom:0px; margin:0px;"></div>';
															//quadrillage et echelle des ordonnées
					for ($i = 1; $i <= 6; $i++) {
						echo '<div style="diplay:block;position:absolute; background-color:#888; width:830px; height:1px;left:70px; top:'.(((7-$i)*50)+50).'px;bottom:0px; margin:0px;"></div>';
						echo '<div style="diplay:block;position:absolute; width:50px; height:30px;left:30px; top:'.(((7-$i)*50)+43).'px;bottom:0px; margin:0px; z-index:0;color:#888;">'.$premiere_grad*$i.'</div>';
					}
					echo '</div></section>';
					

		
}			
					
					
		
					
					//if (empty($socid))
//{
	// Show filter box
	print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="mode" value="'.$mode.'">';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre"><td class="liste_titre" colspan="2">'.$langs->trans("Filter").'</td></tr>';



								
								
		print '<tr><td>'.$langs->trans("Produit / service").'</td><td>';
								print '<select class="texte" name="familleid">';
								print '<OPTION value="0">Sélectionnez un produit / service</OPTION>';
								$resql2=$db->query("select '.MAIN_DB_PREFIX.'product.rowid as rowid, '.MAIN_DB_PREFIX.'product.ref as ref,'.MAIN_DB_PREFIX.'product.label as label, count('.MAIN_DB_PREFIX.'facture_fourn_det.pu_ht) as nombre
from '.MAIN_DB_PREFIX.'product 
right join '.MAIN_DB_PREFIX.'facture_fourn_det on '.MAIN_DB_PREFIX.'facture_fourn_det.fk_product = '.MAIN_DB_PREFIX.'product.rowid
where fk_product_type=0
group by '.MAIN_DB_PREFIX.'product.ref,rowid order by label");
								if ($resql2)
								{
									$num = $db->num_rows($resql2);
									$j = 0;
									if ($num)
									{
										while ($j < $num)
										{
											$obj = $db->fetch_object($resq2);
											if ($obj)
											{
												if ($obj->nombre>=2)
												{
												// You can use here results
												print '<OPTION value="'.$obj->rowid.'">'.$obj->label.' - '.$obj->ref.'</OPTION>';
												}
											}
											$j++;
										}
									}
								}
								

			
											
												 
										
								print '</select>';
	print '</td></tr>';
								
	print '</td></tr>';
	print '<tr><td align="center" colspan="2"><input type="submit" name="submit" class="button" value="'.$langs->trans("Refresh").'"></td></tr>';
	print '</table>';
	print '</form>';
	print '<br><br>';
	


llxFooter();

$db->close();
