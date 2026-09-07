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



llxHeader("",$langs->trans("SalesPerMonth"));

print '<div class="fichecenter"><div class="fichethirdleft">';
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facturedet.total_ht ) AS total_ht';
	if ($_POST["donneesid"] == "ca")	
	{
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facturedet.total_ht ) AS total_ht';
		print load_fiche_titre($langs->trans("SalesPerMonth"),'','title_accountancy.png');

	}
	elseif ($_POST["donneesid"] == "marge")	
	{
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facturedet.total_ht - ( '.MAIN_DB_PREFIX.'facturedet.buy_price_ht)*'.MAIN_DB_PREFIX.'facturedet.qty *SIGN('.MAIN_DB_PREFIX.'facturedet.total_ht)) AS total_ht';
		print load_fiche_titre($langs->trans("MarginPerMonth"),'','title_accountancy.png');
	}
	else
	{
				print load_fiche_titre($langs->trans("SalesPerMonth"),'','title_accountancy.png');
	}



if ($user->rights->statistiques->Ventes->SAVentes)
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



	echo $langs->trans("Year").' '.$debut.' ';
	echo $langs->trans("ComparedTo").' '.($debut-1).' ';
	echo $langs->trans("And").' '.($debut-2);
					$max=1;
					$nbenr=1;
					
					


$resql=$db->query('SELECT '.$varsearch_motcle_url4.', month( '.MAIN_DB_PREFIX.'facture.datef ) AS mois, YEAR( '.MAIN_DB_PREFIX.'facture.datef ) AS annee
				FROM '.MAIN_DB_PREFIX.'facture
				INNER JOIN '.MAIN_DB_PREFIX.'facturedet ON '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid
				where (YEAR('.MAIN_DB_PREFIX.'facture.datef)<="'.$debut.'") '.$typ2.'
				GROUP BY annee, mois ORDER by annee DESC, mois DESC limit 0,12;');

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
					if ($obj->total_ht>$max)
							{
								$max=$obj->total_ht;	
							}
					$nbenr++;
				
			}
			$i++;
		}
	}
}
include ("calcul_echelle.php");		
					$coef=50/$premiere_grad;
					
					$largeur=(800/12);
					$largeur=round($largeur,2);
					

$resql=$db->query('SELECT '.$varsearch_motcle_url4.', month( '.MAIN_DB_PREFIX.'facture.datef ) AS mois,monthname( '.MAIN_DB_PREFIX.'facture.datef ) AS moisnom, YEAR( '.MAIN_DB_PREFIX.'facture.datef ) AS annee
				FROM '.MAIN_DB_PREFIX.'facture
				INNER JOIN '.MAIN_DB_PREFIX.'facturedet ON '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid
				where (YEAR('.MAIN_DB_PREFIX.'facture.datef)<="'.$debut.'")
				GROUP BY annee, mois,moisnom ORDER by annee DESC, mois DESC limit 0,36;');

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
			{
					echo'<div class="barreN" style="display: inline-block; position: absolute; left: '.(765-($largeur*$enr)).'px; width:'.($largeur-25).'px; bottom:0px; height:'.($obj->total_ht*$coef).'px; border-radius:3px;background-color:#1E7FCB;z-index:4;"><div class="bulle">'.number_format($obj->total_ht, 2, ',', ' ').' '.$conf->global->MAIN_MONNAIE.'</div></div>';
								
									
							
echo '<div style="display: inline-block; position: absolute; text-align:right; transform: rotate(315deg); left: '.(745-($largeur*$enr)).'px; width:'.($largeur+35).'px; bottom:-80px;  height:55px;text-align:center;color:#666;font-size:10px;">'.$obj->jour.' '.$langs->trans($obj->moisnom).' '.$obj->annee.'</div>';
							echo '<div class="valeurhistogramme" style="display: inline-block; position: absolute; left: '.(765-($largeur*$enr)).'px; width:'.($largeur-25).'px; bottom:+0px; height:20px;text-align:center;z-index:5">'.number_format($obj->total_ht, 0, ',', ' ').'</div>';
							$enr++;
			}
			$i++;
		}
		while ($i < 24)
		{
			$obj = $db->fetch_object($resql);
			if ($obj)
			{
					echo'<div style="display: inline-block; opacity:0.5;position: absolute; left: '.(775-($largeur*($enr-12))).'px; width:'.($largeur-25).'px; bottom:0px; height:'.($obj->total_ht*$coef).'px; border-radius:3px;background-color:#1E7FCB;z-index:2;"></div>';
								
									
							
							$enr++;
			}
			$i++;
		}
		while ($i < 36)
		{

			$obj = $db->fetch_object($resql);
			if ($obj)
			{
					echo'<div style="display: inline-block; opacity:0.3;position: absolute; left: '.(785-($largeur*($enr-24))).'px; width:'.($largeur-25).'px; bottom:0px; height:'.($obj->total_ht*$coef).'px; border-radius:3px;background-color:#1E7FCB;z-index:1;"></div>';
								
									
							
							$enr++;
			}

	

			$i++;
		}

	}
}
							echo '</div>';
					echo '<div style="diplay:block;position:absolute; background-color:#222; width:830px; height:3px;left:70px; top:400px;bottom:0px; margin:0px;"></div>';
															//quadrillage et echelle des ordonnĂ©es
					for ($i = 1; $i <= 6; $i++) {
						echo '<div style="diplay:block;position:absolute; background-color:#888; width:830px; height:1px;left:70px; top:'.(((7-$i)*50)+50).'px;bottom:0px; margin:0px;"></div>';
						echo '<div style="diplay:block;position:absolute; width:50px; height:30px;left:15px; top:'.(((7-$i)*50)+43).'px;bottom:0px; text-align:right;margin:0px; z-index:0;color:#888;">'.number_format(($premiere_grad*$i), 0, ',', ' ').'</div>';
					}
					echo '</div>
					<a href="ventes_cumul_an.php">'.$langs->trans("CumulAnnuel").'</A>
					</section>';
					
					//if (empty($socid))
//{
	// Show filter box
	print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="mode" value="'.$mode.'">';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre"><td class="liste_titre" colspan="2">'.$langs->trans("Filter").'</td></tr>';


	// Year
	print '<tr><td>'.$langs->trans("ReferentYear").'</td><td>';

	

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
	
	print '<tr><td align="center" colspan="2"><input type="submit" name="submit" class="button" value="'.$langs->trans("Search").'"></td></tr>';
	print '</table>';
	print '</form>';
	print '<br><br>';


/* BEGIN MODULEBUILDER DRAFT MYOBJECT
// Draft MyObject
if (! empty($conf->statistiques->enabled) && $user->rights->statistiques->read)
{
	$langs->load("orders");

	$sql = "SELECT c.rowid, c.ref, c.ref_client, c.total_ht, c.tva as total_tva, c.total_ttc, s.rowid as socid, s.nom as name, s.client, s.canvas";
    $sql.= ", s.code_client";
	$sql.= " FROM ".MAIN_DB_PREFIX."commande as c";
	$sql.= ", ".MAIN_DB_PREFIX."societe as s";
	if (! $user->rights->societe->client->voir && ! $socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql.= " WHERE c.fk_soc = s.rowid";
	$sql.= " AND c.fk_statut = 0";
	$sql.= " AND c.entity IN (".getEntity('commande').")";
	if (! $user->rights->societe->client->voir && ! $socid) $sql.= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	if ($socid)	$sql.= " AND c.fk_soc = ".$socid;

	$resql = $db->query($sql);
	if ($resql)
	{
		$total = 0;
		$num = $db->num_rows($resql);

		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<th colspan="3">'.$langs->trans("DraftOrders").($num?' <span class="badge">'.$num.'</span>':'').'</th></tr>';

		$var = true;
		if ($num > 0)
		{
			$i = 0;
			while ($i < $num)
			{

				$obj = $db->fetch_object($resql);
				print '<tr class="oddeven"><td class="nowrap">';
                $orderstatic->id=$obj->rowid;
                $orderstatic->ref=$obj->ref;
                $orderstatic->ref_client=$obj->ref_client;
                $orderstatic->total_ht = $obj->total_ht;
                $orderstatic->total_tva = $obj->total_tva;
                $orderstatic->total_ttc = $obj->total_ttc;
                print $orderstatic->getNomUrl(1);
                print '</td>';
				print '<td class="nowrap">';
				$companystatic->id=$obj->socid;
				$companystatic->name=$obj->name;
				$companystatic->client=$obj->client;
                $companystatic->code_client = $obj->code_client;
                $companystatic->code_fournisseur = $obj->code_fournisseur;
                $companystatic->canvas=$obj->canvas;
				print $companystatic->getNomUrl(1,'customer',16);
				print '</td>';
				print '<td align="right" class="nowrap">'.price($obj->total_ttc).'</td></tr>';
				$i++;
				$total += $obj->total_ttc;
			}
			if ($total>0)
			{

				print '<tr class="liste_total"><td>'.$langs->trans("Total").'</td><td colspan="2" align="right">'.price($total)."</td></tr>";
			}
		}
		else
		{

			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("NoOrder").'</td></tr>';
		}
		print "</table><br>";

		$db->free($resql);
	}
	else
	{
		dol_print_error($db);
	}
}
END MODULEBUILDER DRAFT MYOBJECT */


print '</div></div></div>';
}
else
{
	echo $langs->trans("Accès non autorisé.");
}
// End of page
llxFooter();
$db->close();
