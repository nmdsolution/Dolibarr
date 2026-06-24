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

llxHeader("",$langs->trans("MeilleuresVilles"));

print load_fiche_titre($langs->trans("MeilleuresVilles"),'','title_accountancy.png');


print '<div class="fichecenter"><div class="fichethirdleft">';

if ($user->rights->statistiques->Classements->SAClassements)
{
echo '<section style="position:relative; display:inline-block;height:900px;width:1000px;background-color:#fff;">';
if (GETPOST('anneedeb')>1)
{

	$debut=GETPOST('anneedeb');
}
else
{
	$debut=DATE("Y");

}
	echo $langs->trans("Year").' '.$debut;

			$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facturedet.total_ht ) AS totalht';
	if ($_POST["donneesid"] == "ca")	
	{
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facturedet.total_ht ) AS totalht';

	}
	if ($_POST["donneesid"] == "marge")	
	{
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facturedet.total_ht - ( '.MAIN_DB_PREFIX.'facturedet.buy_price_ht)*'.MAIN_DB_PREFIX.'facturedet.qty ) AS totalht';

	}


					$max=1;
					$nbenr=1;
		


		
$resql=$db->query('SELECT '.MAIN_DB_PREFIX.'societe.town as ville,'.$varsearch_motcle_url4.'  FROM '.MAIN_DB_PREFIX.'facture INNER JOIN '.MAIN_DB_PREFIX.'facturedet ON '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid inner join '.MAIN_DB_PREFIX.'societe on '.MAIN_DB_PREFIX.'societe.rowid='.MAIN_DB_PREFIX.'facture.fk_soc where (YEAR('.MAIN_DB_PREFIX.'facture.datef)>="'.$debut.'") group by ville order by totalht desc limit 0,20;');
				
				

				

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
					if ($obj->totalht>$max)
							{
								$max=$obj->totalht;	
							}
					$totalca=$totalca+$obj->totalht;
					$nbenr++;
				
			}
			$i++;
		}
	}
}
				
include ("calcul_echelle_horizontal.php");

					$coef=50/$premiere_grad;
					
					$largeur=(750/$nbenr);
					$largeur=round($largeur,2);
					

					
$resql=$db->query('SELECT '.MAIN_DB_PREFIX.'societe.town as ville,'.$varsearch_motcle_url4.'  FROM '.MAIN_DB_PREFIX.'facture INNER JOIN '.MAIN_DB_PREFIX.'facturedet ON '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid inner join '.MAIN_DB_PREFIX.'societe on '.MAIN_DB_PREFIX.'societe.rowid='.MAIN_DB_PREFIX.'facture.fk_soc where (YEAR('.MAIN_DB_PREFIX.'facture.datef)>="'.$debut.'") group by ville order by totalht desc limit 0,20;');

					echo '<div>';
					echo '<div style="diplay:inline-block;position:absolute; background-color:#222; width:3px; height:750px; left:120px;top:50px; margin:0px;"></div>';
					echo '<div style="diplay:inline-block;position:absolute; background-color:#fff; width:827px; height:750px;left:123px; top:50px;bottom:0px; margin:0px;">';
					echo '<div style="diplay:block;position:absolute; background-color:#222; width:830px; height:3px;left:0px; top:747px;bottom:0px; margin:0px;"></div>';
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
					echo'<div class="barreN" style="display: inline-block; position: absolute; top: '.(25+($largeur*$enr)).'px; height:'.($largeur-12).'px; left:0px; width:'.round(($obj->totalht),2)*$coef.'px; border-radius:3px;background-color:#1E7FCB;z-index:1;"><div class="bulle">'.number_format($obj->totalht, 2, ',', ' ').' '.$conf->global->MAIN_MONNAIE.'</div></div>';
											
							echo '<div style="display: inline-block; position: absolute;top: '.(27+($largeur*$enr)).'px; height:'.($largeur-5).'px; left:-125px;  height:20px;text-align:center;color:#666;font-size:10px;">'.$obj->ville.'</div>';
							echo '<div class="valeurhistogramme" style="display: inline-block; position: absolute; top: '.(30+($largeur*$enr)).'px; height:'.($largeur-5).'px; left:+20px; width:70px;text-align:center;z-index:2">'.number_format(($obj->totalht), 0, ',', ' ').'</div>';
							$enr++;
			}
			$i++;
		}
	}
}
							echo '</div>';

															//quadrillage et echelle des ordonnées
			for ($i = 1; $i <= 16; $i++) {
						echo '<div style="diplay:block;position:absolute; background-color:#888; height:750px; width:1px;bottom:100px; left:'.((($i)*50)+120).'px; margin:0px;"></div>';
						echo '<div style="diplay:block;position:absolute; height:50px; width:50px; left:'.((($i)*50)+95).'px;bottom:40px; font-size:10px;margin:0px;text-align:center; z-index:0;color:#888;">'.number_format(($premiere_grad*$i), 0, ',', ' ').'</div>';
					}
					echo '</div></section>';
					
					//if (empty($socid))
//{
	// Show filter box
	print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="mode" value="'.$mode.'">';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre"><td class="liste_titre" colspan="2">'.$langs->trans("Filter").'</td></tr>';


	// Year
	print '<tr><td>'.$langs->trans("Since").'</td><td>';

	

	print '<input type="hidden" name="anneedeb">';?>
								<select class="field" name="anneedeb" id="ville">
								
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
			// type de données
	print '<tr><td>'.$langs->trans("Unit").'</td><td>';
	if ($_POST["donneesid"]=="marge")
	{
		print '<input type="radio" name="donneesid" value="ca" id="ca"/> <label for="ca">'.$langs->trans("Sales").'</label><br />
			<input type="radio" name="donneesid" value="marge" id="marge" checked/> <label for="marge">'.$langs->trans("Margin").'</label></select></td></tr>';
	}
else	
	{
		print '<input type="radio" name="donneesid" value="ca" id="ca" checked/> <label for="ca">'.$langs->trans("Sales").'</label><br />
			<input type="radio" name="donneesid" value="marge" id="marge" /> <label for="marge">'.$langs->trans("Margin").'</label></select></td></tr>';
	}


	print '<tr><td align="center" colspan="2"><input type="submit" name="submit" class="button" value="'.$langs->trans("Refresh").'"></td></tr>';
	print '</table>';
	print '</form>';
	print '<br><br>';
					
	
$resql=$db->query('SELECT * from '.MAIN_DB_PREFIX.'facture where annee in (select total_ht from '.MAIN_DB_PREFIX.'facturedet);');

echo '<table>';
if ($resql)
{
	$num = $db->num_rows($resql);
	if ($num)
	{
		while ($i < $num)
		{
			$obj = $db->fetch_object($resql);
			if ($obj)
			{
					echo '<tr><td>'.$obj->fk_soc.'</td>';
					echo '<td>'.$obj->annee.'</td></tr>';

			}
			$i++;
		}
	}
}
echo '</table>';
	}
else
{
	echo $langs->trans("Accès non autorisé.");
}					
					


llxFooter();

$db->close();
