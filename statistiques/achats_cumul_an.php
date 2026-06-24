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


llxHeader("",$langs->trans("PurchasePerMonth"));




print '<div class="fichecenter"><div class="fichethirdleft">';
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facture_fourn_det.total_ht ) AS totalht';

		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facture_fourn_det.total_ht ) AS totalht';
		print load_fiche_titre($langs->trans("PurchasePerMonth"),'','title_accountancy.png');

if ($user->rights->statistiques->Achats->SAAchats)
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


	if ($_POST["clientid"] > 0)	
	{
		$clientid=$_POST["clientid"];
		$varsearch_motcle_url3='and ('.MAIN_DB_PREFIX.'facture_fourn.fk_soc = '.$clientid.')';

	}
	else if ($_GET["soc"] > 0)	
	{
		$clientid=$_GET["soc"];
		$varsearch_motcle_url3='and ('.MAIN_DB_PREFIX.'facture_fourn.fk_soc = '.$clientid.')';

	}
		else
	{
		$varsearch_motcle_url3='';
	}

$array_mois = array("Janvier", "Février", "Mars", "Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre");

echo '<div class="div-table-responsive-no-min">
	<table class="noborder" max-width="600px"><tr class="liste_titre"><td>Mois</td><td style="text-align:right;">'.(date("Y")-3).'</td><td style="text-align:right;">'.(date("Y")-2).'</td><td style="text-align:right;">'.(date("Y")-1).'</td><td style="text-align:right;">'.date("Y").'</td></tr>';
$mois=0;
while ($mois<12)
{
$moisnom=$array_mois[$mois];
	echo '<tr><td>'.$moisnom.'</td>';
	$annee=((date("Y"))-3);
	while ($annee<=(date("Y")))
	{	
				$reqcumul=$db->query('SELECT '.$varsearch_motcle_url4.' 
										from '.MAIN_DB_PREFIX.'facture_fourn 
										INNER JOIN '.MAIN_DB_PREFIX.'facture_fourn_det ON '.MAIN_DB_PREFIX.'facture_fourn_det.fk_facture_fourn = '.MAIN_DB_PREFIX.'facture_fourn.rowid
										where (YEAR('.MAIN_DB_PREFIX.'facture_fourn.datef)="'.$annee.'") '.$varsearch_motcle_url3.' and (month('.MAIN_DB_PREFIX.'facture_fourn.datef)<='.($mois+1).');');								
				if ($reqcumul)
				{
							$objcumul = $db->fetch_object($reqcumul);
								{
									echo '<td style="text-align:right;">';
																		if (($mois>=(date("m")) and ($annee==(date("Y")))))
									{}
								else
								{
									echo round($objcumul->totalht,0);
								}
								echo '</td>';
								}		
				}
				$annee++;
	}
	echo '</tr>';
	$mois++;
	}
	echo '</table>';
print '</div>';




				$resqmax=$db->query('SELECT '.$varsearch_motcle_url4.', YEAR('.MAIN_DB_PREFIX.'facture_fourn.datef) as annee 
									from '.MAIN_DB_PREFIX.'facture_fourn 
									INNER JOIN '.MAIN_DB_PREFIX.'facture_fourn_det ON '.MAIN_DB_PREFIX.'facture_fourn_det.fk_facture_fourn = '.MAIN_DB_PREFIX.'facture_fourn.rowid
									where (YEAR('.MAIN_DB_PREFIX.'facture_fourn.datef)<="'.$debut.'") '.$varsearch_motcle_url3.' group by annee;');
					
													
				if ($resqmax)
				{
					$nummax = $db->num_rows($resqlmax);
					$b = 0;						
						while ($b < $nummax)
						{
							$objmax = $db->fetch_object($resqmax);
								if ($objmax->totalht>$max)
								{
									$max=$objmax->totalht;
								}		
							$b++;
						}
				}


include ("calcul_echelle.php");

				
					//$premiere_grad=300;
					
					$coef=50/$premiere_grad;
					$largeur=67;

?>


<canvas id="canvas1" width="1000" height="500" style="display:inline-block;">
	Requiert un navigateur récent: Internet Explorer 9, Chrome, Firefox, Safari.
</canvas>

<script type="text/javascript">
function draw(x1,y1,x2,y2,couleur)
{

  var canvas = document.getElementById("canvas1"); 
  var ctx = canvas.getContext("2d");
  
  	ctx.lineWidth="2"; 
	ctx.fill();
	ctx.font="14px Arial";


	

	 ctx.moveTo(x1,y1);
	 ctx.lineTo(x2,y2);
	 ctx.stroke(); 
 
  	ctx.beginPath();
	ctx.lineCap = 'round';
	ctx.moveTo(x1,y1);
	ctx.strokeStyle=couleur;  
	ctx.lineWidth="2";   
	ctx.lineTo(x2,y2);
	

}

function drawpoint(x2,y2,couleur)
{
  var canvas = document.getElementById("canvas1"); 
  var ctx = canvas.getContext("2d");


	ctx.fillStyle = couleur;
	ctx.fillRect(x2,y2,5,5);
}

function drawx(mois,moisnom,largeur)
{
	var canvas = document.getElementById("canvas1"); 
	var ctx = canvas.getContext("2d");
	ctx.font = "08pt Verdana";
	ctx.fillStyle = "black";
	ctx.textAlign="center";
	ctx.fillText(moisnom,738-largeur*mois,480);
}


function legende(cat,y,couleur)
{
	var canvas = document.getElementById("canvas1"); 
	var ctx = canvas.getContext("2d");
	ctx.lineWidth="2"; 
	
	ctx.fill();
	ctx.font="14px Arial";
	ctx.textAlign="left";
	ctx.fillStyle = "#333";
	ctx.fillText(cat, 850,y);
	ctx.fillStyle = couleur;
	ctx.fillRect(830,(y-11),16,12);
}

function draw3(valeurgrad,ygrad,largeur)
{
	var canvas = document.getElementById("canvas1"); 
	var ctx1 = canvas.getContext("2d");
	ctx1.font = "10pt Verdana";
	ctx1.textAlign = "left";

	ctx1.beginPath();

for (i = 1; i < 9; i++) 
{ 
	const frNumberFormat = new Intl.NumberFormat('FR');
   ctx1.textAlign = "right";
   ctx1.fillText(frNumberFormat.format(valeurgrad*i),815,455-i*50);
} 
for (i = 0; i < 9; i++) 
{ 
// lignes horizontales (grille)
	ctx1.moveTo(0,450-i*50);
	ctx1.lineTo(750,450-i*50);
} 

for (i = 0; i < 10; i++) 
{ 
	//graduations axe horizontal
	ctx1.fillStyle="#cccccc";
   	ctx1.fillText("|",71+i*largeur,450);
} 

	//lignes verticales
	ctx1.strokeStyle="#eeeeee"; 
	ctx1.moveTo(0,0);
	ctx1.lineTo(0,450);
	ctx1.moveTo(50,450);
	ctx1.lineTo(750,450);
	ctx1.moveTo(750,0);
	ctx1.lineTo(750,450);
	ctx1.moveTo(750,0);
	

	ctx1.stroke(); 
	
	ctx1.fillStyle = "#cccccc";
	ctx1.fillRect(820,0,180,500);	
	ctx1.fillStyle = "#efefef";
	ctx1.fillRect(822,2,176,496);	
}

//tracade des graduations de l'abscisse
window.onload=draw3(<?php echo $premiere_grad;?>,430,67);	

<?php

$couleur =["#7FDFFD", "#5FBFED", "#3F9FED","#1E7FCB"];				
$mois=12;
$i=0;
while ($mois>-1)
{
	$annee=((date("Y"))-3);
	$j=0;
	while ($annee<=(date("Y")))
	{	
				$reqcumul=$db->query('SELECT '.$varsearch_motcle_url4.' 
									from '.MAIN_DB_PREFIX.'facture_fourn 
									INNER JOIN '.MAIN_DB_PREFIX.'facture_fourn_det ON '.MAIN_DB_PREFIX.'facture_fourn_det.fk_facture_fourn = '.MAIN_DB_PREFIX.'facture_fourn.rowid 
									where (YEAR('.MAIN_DB_PREFIX.'facture_fourn.datef)="'.$annee.'") and (month('.MAIN_DB_PREFIX.'facture_fourn.datef)<='.($mois).') '.$varsearch_motcle_url3.';');								
				if ($reqcumul)
				{
							$objcumul = $db->fetch_object($reqcumul);
								{		
									if (is_null($objcumul->totalht)) 
										{
											$mt_fact=0;
										}
										else
										{
											$mt_fact=$objcumul->totalht;
										}						
										if ($mois>11)
										{
											$valx1=855-$i*$largeur;
										}
										else
										{
											$num_coul=$j;
										
											
											$valx1=805-($i)*$largeur;
											$valy1=450-($montant_last[$j]*$coef);
											$valx2=$valx1-$largeur;
											$valy2=450-(($mt_fact)*$coef);
											
											if (($valy2==$valy1) and ($annee==(date("Y"))))
											{
												//ne pas tracer la courbe si valeur identique par rapport au mois précédent et que l'année est en cours (mois non passé)
											}
											else
											{
												?>window.onload=draw(<?php echo $valx1;?>,<?php echo $valy1;?>,<?php echo $valx2;?>,<?php echo $valy2;?>,"<?php echo $couleur[$j];?>"); <?php
											}
												
												
											
										}
										$montant_last[$j]=$mt_fact;
																		$num_coul_last=$num_coul;
										
									$valx2=735-$i*$largeur;
									$mt_fact=$objcumul->totalht;
									$valy2=448-(($mt_fact)*$coef);
									if (($mois>(date("m")) and ($annee==(date("Y")))))
									{}
								else
									{										
									?>window.onload=drawpoint(<?php echo $valx2;?>,<?php echo $valy2;?>,"<?php echo $couleur[$j];?>");<?	
									}									
								}		
				}
				$annee++;
				$j++;
	}
$array_mois2 = array("Décembre", "Novembre", "Octobre", "Septembre","Août","Juillet","Juin","Mai","Avril","Mars","Février","Janvier");

	$moisnom=$array_mois2[$mois];
	
	?>window.onload=drawx(<?php echo $mois;?>,"<?php echo $moisnom;?>",<?php echo $largeur;?>);<?
	
	$mois--;
	$i++;
	
}
//légende
	$annee=((date("Y"))-3);
	$j=0;
	while ($j < 4)
	{
			?>window.onload=legende("<?php echo $annee?>",<?php echo ($j*20)+20;?>,"<?php echo $couleur[$j];?>");<?php
		$j++;
		$annee++;
	}
							



				
			


?>



</script>



</section>

<?php


	echo $langs->trans("Year").' '.$debut;


					//if (empty($socid))
//{
	// Show filter box
	print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="mode" value="'.$mode.'">';
	print '<table class="noborder" width="100%">';
	
	
	
	
	
	
	
	
	
	
	

	
	
	
	
	
	
	print '<tr class="liste_titre"><td class="liste_titre" colspan="2">'.$langs->trans("Filter").'</td></tr>';


			// Client
	print '<tr><td>'.$langs->trans("Supplier").'</td><td>';
								print '<select class="texte" name="clientid">';
								print '<OPTION value="0">'.$langs->trans("All").'</OPTION>';
								$resql2=$db->query('select distinct nom, rowid, town from '.MAIN_DB_PREFIX.'societe where fournisseur=1 order by nom asc');
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
												// You can use here results
												
													if ($clientid == $obj->rowid)	
												{
													
													print '<OPTION selected value="'.$obj->rowid.'">'.$obj->nom.' - '.$obj->town.'</OPTION>';
												}
												else
												{
													print '<OPTION value="'.$obj->rowid.'">'.$obj->nom.' - '.$obj->town.'</OPTION>';
												}
											}
											$j++;
										}
									}
								}								

														
												 
										
								print '</select>';
	print '</td></tr>';


	
	print '<tr><td align="center" colspan="2"><input type="submit" name="submit" class="button" value="'.$langs->trans("Search").'"></td></tr>';
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
