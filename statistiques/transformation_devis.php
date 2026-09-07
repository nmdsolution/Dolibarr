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


llxHeader("",$langs->trans("Devis signés"));


print '<div class="fichecenter"><div class="fichethirdleft">';

	$varsearch_motcle_url4='count('.MAIN_DB_PREFIX.'propal.ref) as compteur';
	


	if ($_POST["donneesid"] == "nombre")	
	{
		$varsearch_motcle_url4='count('.MAIN_DB_PREFIX.'propal.ref) as compteur';
		print load_fiche_titre($langs->trans("QuotationNumberByMonth"),'','title_accountancy.png');
	}
	else
	{
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'propal.total_ht ) AS compteur';
		print load_fiche_titre($langs->trans("AmountQuotationbyMonth"),'','title_accountancy.png');
		$unite=$conf->global->MAIN_MONNAIE;
	}		
	

	
	
	

if ($user->rights->statistiques->Devis->SADevis)
{
echo '<section style="position:relative; display:inline-block;height:600px;width:1000px;background-color:#fff;">';

if (GETPOST('anneedeb')>1)
{
	$debut=GETPOST('anneedeb');
}
else
{
	$debut=DATE("Y");
}


	echo $langs->trans("Year").' '.$debut.' - ';
	echo $langs->trans("ComparedTo").' '.($debut-1);
					$max=1;
					$nbenr=1;


					$max=1;
					$nbenr=1;

$resql=$db->query('SELECT '.$varsearch_motcle_url4.', month( '.MAIN_DB_PREFIX.'propal.datep ) AS mois, YEAR( '.MAIN_DB_PREFIX.'propal.datep ) AS annee
				FROM '.MAIN_DB_PREFIX.'propal
				where (YEAR('.MAIN_DB_PREFIX.'propal.datep)<="'.$debut.'")
				GROUP BY annee, mois ORDER by annee DESC, mois DESC limit 0,24;');

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
					if ($obj->compteur>$max)
							{
								$max=$obj->compteur;								
							}
					$nbenr++;
				
			}
			$i++;
		}
	}
}


include ("calcul_echelle.php");		

					$coef=45/$premiere_grad;
	

		

					
					$largeur=(800/12);
					$largeur=round($largeur,2);
					


$resql=$db->query('SELECT month( '.MAIN_DB_PREFIX.'propal.datep ) AS mois,monthname( '.MAIN_DB_PREFIX.'propal.datep ) AS moisnom, YEAR( '.MAIN_DB_PREFIX.'propal.datep ) AS annee
				FROM '.MAIN_DB_PREFIX.'propal
				where (YEAR('.MAIN_DB_PREFIX.'propal.datep)<="'.$debut.'")
				GROUP by annee, mois ORDER by annee DESC, mois DESC limit 0,24;');

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
		while ($i < 12)
		{
			$obj = $db->fetch_object($resql);
			if ($obj)
				
				$resql1=$db->query('SELECT '.$varsearch_motcle_url4.'
				FROM '.MAIN_DB_PREFIX.'propal
				where (YEAR('.MAIN_DB_PREFIX.'propal.datep)="'.($obj->annee).'") and (MONTH('.MAIN_DB_PREFIX.'propal.datep)="'.($obj->mois).'") and ('.MAIN_DB_PREFIX.'propal.fk_statut in ("2","4"));');
			

				if ($resql1)
				{
					$num1 = $db->num_rows($resql1);
					
					if ($num1)
					{
						$obj1 = $db->fetch_object($resql1);
							if ($obj1)
							{
								$color="#1E7FCB";
								$left=(765-($largeur*$enr));
								$hauteurval1=($obj1->compteur*$coef);
								echo'<div class="barreN" style="display: inline-block; position: absolute; left: '.$left.'px; width:'.($largeur-25).'px; bottom:0px; height:'.($obj1->compteur*$coef).'px; border-radius:0px 0px 3px 3px;background-color:'.$color.';z-index:2;"><div class="bulle">'.number_format($obj1->compteur, 2, ',', ' ').' '.$unite.'</div></div>';

								$compteurrefus=round($obj1->compteur,0);
								echo '<div style="display: inline-block; position: absolute; text-align:right; transform: rotate(315deg); left: '.(745-($largeur*$enr)).'px; width:'.($largeur+35).'px; bottom:-80px;  height:55px;text-align:center;color:#666;font-size:10px;">'.$obj->jour.' '.$langs->trans($obj->moisnom).' '.$obj->annee.'</div>';	
							
							
																			if (($obj1->compteur*$coef)>20)
					{
					echo '<div class="valeurhistogramme" style="display: inline-block; position: absolute; left: '.(765-($largeur*$enr)).'px; width:'.($largeur-25).'px; bottom:0px; height:20px;text-align:center;z-index:3">'.number_format($obj1->compteur, 0, ',', ' ').'</div>';
					}
							
							}
					}
				}
				
				$resql1=$db->query('SELECT '.$varsearch_motcle_url4.'
				FROM '.MAIN_DB_PREFIX.'propal
				where (YEAR('.MAIN_DB_PREFIX.'propal.datep)="'.($obj->annee).'") and (MONTH('.MAIN_DB_PREFIX.'propal.datep)="'.($obj->mois).'") and ('.MAIN_DB_PREFIX.'propal.fk_statut in ("1","3"));');
			

				if ($resql1)
				{
					$num1 = $db->num_rows($resql1);
					
					if ($num1)
					{
						$obj1 = $db->fetch_object($resql1);
							if ($obj1)
							{
								$color="#e50000";
								$left=(765-($largeur*$enr));
								echo'<div class="barreN" style="display: inline-block; position: absolute; left: '.$left.'px; width:'.($largeur-25).'px; bottom:'.$hauteurval1.'px; height:'.($obj1->compteur*$coef).'px; border-radius:3px 3px 0px 0px;background-color:'.$color.';z-index:2;"><div class="bulle">'.number_format($obj1->compteur, 2, ',', ' ').' '.$unite.'</div></div>';
								$compteurtotal=$compteurrefus+round($obj1->compteur,0);
								
								
													if (($obj1->compteur*$coef)>20)
					{
					echo '<div class="valeurhistogramme" style="display: inline-block; position: absolute; left: '.(765-($largeur*$enr)).'px; width:'.($largeur-25).'px; bottom:'.($hauteurval1+($obj1->compteur*$coef)-25).'px; height:20px;text-align:center;z-index:3">'.number_format($obj1->compteur, 0, ',', ' ').'</div>';
					}
								
								
		
												
							}
					}
				}
				$enr++;
			$i++;
		}
		
		while ($i < 24)
		{
			$obj = $db->fetch_object($resql);
			if ($obj)
				
				$resql1=$db->query('SELECT '.$varsearch_motcle_url4.'
				FROM '.MAIN_DB_PREFIX.'propal
				where (YEAR('.MAIN_DB_PREFIX.'propal.datep)="'.($obj->annee).'") and (MONTH('.MAIN_DB_PREFIX.'propal.datep)="'.($obj->mois).'") and ('.MAIN_DB_PREFIX.'propal.fk_statut in("2","4"));');
			

				if ($resql1)
				{
					$num1 = $db->num_rows($resql1);
					
					if ($num1)
					{
						$obj1 = $db->fetch_object($resql1);
							if ($obj1)
							{
								$color="#1E7FCB";
								$left=(765-($largeur*($enr-12))+10);
								$hauteurval1=($obj1->compteur*$coef);
								echo'<div style="display: inline-block; opacity:0.5; position: absolute; left: '.$left.'px; width:'.($largeur-25).'px; bottom:0px; height:'.($obj1->compteur*$coef).'px; border-radius:0px 0px 3px 3px;background-color:'.$color.';z-index:1;"></div>';
					
							}
					}
				}
				
				$resql1=$db->query(''.$varsearch_motcle_url4.'
				FROM '.MAIN_DB_PREFIX.'propal
				where (YEAR('.MAIN_DB_PREFIX.'propal.datep)="'.($obj->annee).'") and (MONTH('.MAIN_DB_PREFIX.'propal.datep)="'.($obj->mois).'") and ('.MAIN_DB_PREFIX.'propal.fk_statut in ("1","3"));');
	

				if ($resql1)
				{
					$num1 = $db->num_rows($resql1);
					
					if ($num1)
					{
						$obj1 = $db->fetch_object($resql1);
							if ($obj1)
							{
								$color="#e50000";
								$left=(765-($largeur*($enr-12))+10);
								echo'<div style="display: inline-block; opacity:0.5; position: absolute; left: '.$left.'px; width:'.($largeur-25).'px; bottom:'.$hauteurval1.'px; height:'.($obj1->compteur*$coef).'px; border-radius:3px 3px 0px 0px;background-color:'.$color.';z-index:1;"></div>';
													}
					}
				}
				$enr++;
			$i++;
		}


	}
}
							echo '</div>';
					echo '<div style="diplay:block;position:absolute; background-color:#222; width:830px; height:3px;left:70px; top:400px;bottom:0px; margin:0px;"></div>';
															//quadrillage et echelle des ordonnées
					for ($i = 1; $i <= 6; $i++) {
						echo '<div style="diplay:block;position:absolute; background-color:#888; width:830px; height:1px;left:70px; top:'.(((7-$i)*50)+50).'px;bottom:0px; margin:0px;"></div>';
						echo '<div style="diplay:block;position:absolute; width:50px; height:30px;left:10px; top:'.(((7-$i)*50)+43).'px;bottom:0px; text-align:right; margin:0px; z-index:0;color:#888;">'.number_format($premiere_grad*$i, 0, ',', ' ').'</div>';
					}
					
					echo '<div class="valeurhistogramme" style="diplay:block;position:absolute; text-align:center; padding:3px;width:150px; height:16px;left:30px;bottom:60px; margin:0px; z-index:0;background-color:#1E7FCB;">'.$langs->trans("Signed").' / '.$langs->trans("Billed").'</div>';
					echo '<div class="valeurhistogramme" style="diplay:block;position:absolute; text-align:center; padding:3px;width:150px; height:16px;left:30px;bottom:30px; margin:0px; z-index:0;background-color:#e50000;">'.$langs->trans("Refused").' / '.$langs->trans("Open").'</div>';
					
					echo '</div></section>';
					
					//if (empty($socid))
//{
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
	
				// type de donnĂ©es
	print '<tr><td>'.$langs->trans("Unit").'</td><td>';

	if ($_POST["donneesid"]=="ca")
	{
		print '<input type="radio" name="donneesid" value="ca" id="ca" checked/> <label for="ca">'.$langs->trans("Amount").'</label><br />
		<input type="radio" name="donneesid" value="nombre" id="nombre" /> <label for="marge">'.$langs->trans("Number").'</label>
		
			</select></td></tr>';
	}
	else	
	{
		print '<input type="radio" name="donneesid" value="ca" id="ca"/> <label for="ca">'.$langs->trans("Amount").'</label><br />
			<input type="radio" name="donneesid" value="nombre" id="nombre" checked/> <label for="marge">'.$langs->trans("Number").'</label></select></td></tr>';
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
