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
echo '<section style="position:relative; display:inline-block;height:600px;width:1000px;background-color:#fff;">';

	






		$echelle='SELECT day(datem) as jour, week(datem) as semaine, monthname(datem) as moisnom, month(datem) as mois, year(datem) as annee FROM '.MAIN_DB_PREFIX.'stock_mouvement GROUP BY annee, mois, moisnom, semaine, jour order by annee desc, mois desc, jour desc, moisnom desc';

	if ($_POST["donneesid"] == "mois")	
	{
		$echelle='SELECT monthname(datem) as moisnom, month(datem) as mois, year(datem) as annee FROM '.MAIN_DB_PREFIX.'stock_mouvement GROUP BY annee, mois, moisnom order by annee desc, mois desc, moisnom desc';


	}
	if ($_POST["donneesid"] == "jour")	
	{
		$echelle='SELECT day(datem) as jour, week(datem) as semaine, monthname(datem) as moisnom, month(datem) as mois, year(datem) as annee FROM '.MAIN_DB_PREFIX.'stock_mouvement GROUP BY annee, mois, moisnom, semaine, jour order by annee desc, mois desc, jour desc, moisnom desc';


	}








	
		
if ($_POST["donneesid"] == "mois")	
{

	echo 'Par mois';
	$echelle='SELECT monthname(datem) as moisnom, month(datem) as mois, year(datem) as annee FROM '.MAIN_DB_PREFIX.'stock_mouvement GROUP BY annee, mois, moisnom order by annee desc, mois desc, moisnom desc';
}


if ($_POST["donneesid"] == "jour")	

{

	echo 'Par jour';
	$echelle='SELECT day(datem) as jour, week(datem) as semaine, monthname(datem) as moisnom, month(datem) as mois, year(datem) as annee FROM '.MAIN_DB_PREFIX.'stock_mouvement GROUP BY annee, mois, moisnom, semaine, jour order by annee desc, mois desc, jour desc, moisnom desc';
}

//trouver la valeur maxi
$resqlst=$db->query('SELECT '.MAIN_DB_PREFIX.'product.label, (
			'.MAIN_DB_PREFIX.'product.pmp * '.MAIN_DB_PREFIX.'product_stock.reel
			) AS total, '.MAIN_DB_PREFIX.'product.pmp, '.MAIN_DB_PREFIX.'product_stock.reel
			FROM '.MAIN_DB_PREFIX.'product_stock
			INNER JOIN '.MAIN_DB_PREFIX.'product ON '.MAIN_DB_PREFIX.'product.rowid = '.MAIN_DB_PREFIX.'product_stock.fk_product');

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
				// You can use here results
							$stock_actuel=$stock_actuel+$obj->total;
							
							if ($stock_actuel>400)
							{
								$premiere_grad=round(($stock_actuel/4),-2);				
								$maxcoef=50/$premiere_grad;
							}
			}
			$i++;
		}
	}
}
			
$resqlst2=$db->query($echelle);
										
									

$maxi=1;

		if ($resqlst2)
		{
			$num = $db->num_rows($resqlst2);
			$j = 0;
			if ($num)
			{
				while ($j < $num)
				{
					$obj2 = $db->fetch_object($resqlst2);
					if ($obj2)
					{
						$var_stock_periode=0;
						$date_comp=$obj2->annee.'-'.$obj2->mois.'-'.$obj2->jour;
						if ($_POST["donneesid"]=="mois")
						{
							$date_comp=$obj2->annee.'-'.$obj2->mois.'-01';
						}
						else
						if ($_POST["donneesid"]=="jour")
						{
						}
						else
						{
							$date_comp=$obj2->annee.'-'.$obj2->mois.'-'.$obj2->jour;
						}
						
						
						
						

				
							$resqlst3=$db->query('SELECT '.MAIN_DB_PREFIX.'product.pmp as pmp, '.MAIN_DB_PREFIX.'stock_mouvement.value as qte 
FROM '.MAIN_DB_PREFIX.'stock_mouvement
inner join '.MAIN_DB_PREFIX.'product on '.MAIN_DB_PREFIX.'product.rowid='.MAIN_DB_PREFIX.'stock_mouvement.fk_product where '.MAIN_DB_PREFIX.'stock_mouvement.datem >= "'.$date_comp.'";');



							if ($resqlst3)
							{
								$num3 = $db->num_rows($resqlst3);
								$k = 0;
								
								if ($num3)
								{
									$total_var_periode=0;
									
									while ($k < $num3)
									{
										$obj3 = $db->fetch_object($resqlst3);
										if ($obj3)
										{
											$total_var_periode=$total_var_periode-(($obj3->pmp)*($obj3->qte));		
										}
										$k++;
										
									}
									
									$stock_instant=$stock_actuel+$total_var_periode;
									//echo '<h2>'.$stock_instant.'</h2>';
								}
											if ($stock_instant>$maxi)
											{
												$maxi=$stock_instant;
											}
							}







							
							
							$resqlst3=$db->query('SELECT '.MAIN_DB_PREFIX.'product.pmp as pmp, '.MAIN_DB_PREFIX.'stock_mouvement.value as qte 
FROM '.MAIN_DB_PREFIX.'stock_mouvement
inner join '.MAIN_DB_PREFIX.'product on '.MAIN_DB_PREFIX.'product.rowid='.MAIN_DB_PREFIX.'stock_mouvement.fk_product where '.MAIN_DB_PREFIX.'stock_mouvement.datem >= "'.$date_comp.'";');



							if ($resqlst3)
							{
								$num3 = $db->num_rows($resqlst3);
								$k = 0;
								
								if ($num3)
								{
									$total_var_periode=0;
									while ($k < $num3)
									{
										
										$obj3 = $db->fetch_object($resqlst3);
										if ($obj3)
										{
											$total_var_periode=$total_var_periode-(($obj3->pmp)*($obj3->qte));		
										}
										$k++;
										
									}
									$stock_instant=$stock_actuel+$total_var_periode;
											
								}
							}										
					}
					$j++;
				}

			}
		}



	
if ($premiere_grad<1)
{ 
$premiere_grad=round((($maxi)/5),-2);	
}								
								$maxcoef=50/$premiere_grad;
					echo '<div style="diplay:inline-block;position:absolute; background-color:#222; width:3px; height:350px; left:70px;top:50px; margin:0px;"></div>';

$resqlst2=$db->query($echelle.' limit 0,18');
										
$maxi=1;


		if ($resqlst2)
		{
			$num = $db->num_rows($resqlst2);
			$j = 0;
			if ($num)
			{
				while ($j < $num)
				{
					$obj2 = $db->fetch_object($resqlst2);
					if ($obj2)
					{
						$var_stock_periode=0;
$date_comp=$obj2->annee.'-'.$obj2->mois.'-'.$obj2->jour;						
							if ($_POST["donneesid"]=="mois")
						{
							$date_comp=$obj2->annee.'-'.$obj2->mois.'-01';
						}
						else
							if ($_POST["donneesid"]=="jour")
						{
						}
						else
						{
							$date_comp=$obj2->annee.'-'.$obj2->mois.'-'.$obj2->jour;
						}
					

						
						
							$resqlst3=$db->query('SELECT '.MAIN_DB_PREFIX.'product.pmp as pmp, '.MAIN_DB_PREFIX.'stock_mouvement.value as qte 
FROM '.MAIN_DB_PREFIX.'stock_mouvement
inner join '.MAIN_DB_PREFIX.'product on '.MAIN_DB_PREFIX.'product.rowid='.MAIN_DB_PREFIX.'stock_mouvement.fk_product where '.MAIN_DB_PREFIX.'stock_mouvement.datem >= "'.$date_comp.'";');



							if ($resqlst3)
							{
								$num3 = $db->num_rows($resqlst3);
								$k = 0;
								
								if ($num3)
								{
									$total_var_periode=0;
									
									while ($k < $num3)
									{
										
										$obj3 = $db->fetch_object($resqlst3);
										if ($obj3)
										{
											
											//echo $obj2->datem.':'.$obj3->pmp.' * '.$obj3->qte.'<br>';
											$total_var_periode=$total_var_periode-(($obj3->pmp)*($obj3->qte));		
										}
										$k++;
										
									}
									
									$stock_instant=$stock_actuel+$total_var_periode;
									//echo '<h2>'.$stock_instant.'</h2>';
								}
											if ($stock_instant>$maxi)
											{
												$maxi=$stock_instant;
											}
								

									
							
							}


							$largeur=(800/20);




							
							
							$resqlst3=$db->query('SELECT '.MAIN_DB_PREFIX.'product.pmp as pmp, '.MAIN_DB_PREFIX.'stock_mouvement.value as qte 
FROM '.MAIN_DB_PREFIX.'stock_mouvement
inner join '.MAIN_DB_PREFIX.'product on '.MAIN_DB_PREFIX.'product.rowid='.MAIN_DB_PREFIX.'stock_mouvement.fk_product where '.MAIN_DB_PREFIX.'stock_mouvement.datem >= "'.$date_comp.'";');



							if ($resqlst3)
							{
								$num3 = $db->num_rows($resqlst3);
								$k = 0;
								
								if ($num3)
								{
									$total_var_periode=0;
									while ($k < $num3)
									{							
										$obj3 = $db->fetch_object($resqlst3);
										if ($obj3)
										{								
											$total_var_periode=$total_var_periode-(($obj3->pmp)*($obj3->qte));		
										}
										$k++;									
									}
														
									$stock_instant=$stock_actuel+$total_var_periode;
											echo '<div class="barreN" style="display: inline-block; position: absolute; left: '.(808-($largeur*$j)).'px; width:'.($largeur-5).'px; bottom:+200px; height:'.($stock_instant*$maxcoef).'px; border-radius:3px;background-color:#1E7FCB;z-index:1;"><div class="bulle">'.number_format($stock_instant, 2, ',', ' ').' '.$conf->global->MAIN_MONNAIE.'</div></div>';
											echo '<div style="display: inline-block; position: absolute;  transform: rotate(315deg); left: '.(829-($largeur*$j)).'px; width:'.($largeur+35).'px; bottom:120px;  height:50px;text-align:center;color:#666;font-size:10px;">'.$obj2->jour.'/'.$obj2->mois.'/'.$obj2->annee.'</div>';
											echo '<div class="valeurhistogramme" style="display: inline-block; position: absolute; transform: rotate(270deg);left: '.(810-($largeur*$j)).'px; width:'.($largeur-5).'px; bottom:+220px; height:20px;text-align:center;z-index:3">'.number_format($stock_instant, 0, ',', ' ').'</div>';
								}
							}										
					}
					$j++;
				}
				


//affichage de la dernière barre: le stock actuel
echo '<div class="barreN" style="display: inline-block; position: absolute; left: '.(848).'px; width:'.($largeur-5).'px; bottom:+200px; height:'.($stock_actuel*$maxcoef).'px; border-radius:3px;background-color:#1E7FCB;z-index:1;"><div class="bulle">'.number_format($stock_actuel, 2, ',', ' ').' '.$conf->global->MAIN_MONNAIE.'</div></div>';
echo '<div class="valeurhistogramme" style="display: inline-block; position: absolute; transform: rotate(270deg);left: '.(850).'px; width:'.($largeur-5).'px; bottom:+220px; height:20px;text-align:center;z-index:3">'.number_format($stock_actuel, 0, ',', ' ').'</div>';

								
			}
		}
						
					echo '<div style="diplay:block;position:absolute; background-color:#222; width:830px; height:3px;left:70px; top:400px;bottom:0px; margin:0px;"></div>';
															//quadrillage et echelle des ordonnées
					for ($l = 1; $l <= 6; $l++) {
						echo '<div style="diplay:block;position:absolute; background-color:#888; width:830px; height:1px;left:70px; top:'.(((7-$l)*50)+50).'px;bottom:0px; margin:0px;"></div>';
						echo '<div style="diplay:block;position:absolute; width:50px; text-align:right;height:30px;left:15px; top:'.(((7-$l)*50)+43).'px;bottom:0px; margin:0px; z-index:0;color:#888;">'.number_format(($premiere_grad*$l), 0, ',', ' ').'</div>';
					}
	
			echo '</div></section>';
					//if (empty($socid))
//{
	// Show filter box
	print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="mode" value="'.$mode.'">';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre"><td class="liste_titre" colspan="2">'.$langs->trans("Filter").'</td></tr>';



	
	
		print '</td></tr>';
			// type de données
	print '<tr><td>'.$langs->trans("Scale").'</td><td>';
	if ($_POST["donneesid"]=="mois")	
	{
		print '<input type="radio" name="donneesid" value="jour" id="jour"/> <label for="jour">'.$langs->trans("Day").'</label><br />
			<input type="radio" name="donneesid" value="mois" id="mois" checked/> <label for="mois">'.$langs->trans("Month").'</label></select></td></tr>';
	}
	else
	{
		print '<input type="radio" name="donneesid" value="jour" id="jour" checked/> <label for="jour">'.$langs->trans("Day").'</label><br />
			<input type="radio" name="donneesid" value="mois" id="mois" /> <label for="mois">'.$langs->trans("Month").'</label></select></td></tr>';
	}
	
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
