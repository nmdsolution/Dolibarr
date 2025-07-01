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

llxHeader("",$langs->trans("Répartition du chiffre d'affaires par produits / services"));
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facturedet.total_ht) AS totalht';
	if ($_POST["donneesid"] == "ca")	
	{
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facturedet.total_ht) AS totalht';
print load_fiche_titre($langs->trans("SalesProductServiceCategoryDistribution"),'','title_accountancy.png');
	}
	elseif ($_POST["donneesid"] == "marge")	
	{
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facturedet.total_ht - ( '.MAIN_DB_PREFIX.'facturedet.buy_price_ht)*'.MAIN_DB_PREFIX.'facturedet.qty) AS totalht';
print load_fiche_titre($langs->trans("MarginProductServiceCategoryDistribution"),'','title_accountancy.png');
	}
	else
	{
		print load_fiche_titre($langs->trans("SalesProductServiceCategoryDistribution"),'','title_accountancy.png');
	}






print '<div class="fichecenter"><div class="fichethirdleft">';


echo '<section style="position:relative; display:inline-block;height:600px;width:1000px;background-color:#fff;">';


if (GETPOST('anneedeb')>1)
{

	$debut=GETPOST('anneedeb');
}
else
{
	$debut=DATE("Y");

}
if (isset ($_GET["socid"]))
{
	$client=$_GET["socid"];
}
else
{
	$client=$_POST["clientid"];
}



	echo $langs->trans("Année").' '.$debut;

$resqlnom=$db->query('SELECT nom 
FROM '.MAIN_DB_PREFIX.'societe
where rowid='.$client.'');












				
				

				

if ($resqlnom)
{
			$objnom = $db->fetch_object($resqlnom);
			if ($objnom)
			{
								echo " - ".$objnom->nom;								
			}
			
}
	
	

$resql=$db->query('SELECT '.$varsearch_motcle_url4.' 
FROM '.MAIN_DB_PREFIX.'facture 
INNER JOIN '.MAIN_DB_PREFIX.'facturedet ON '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid 
INNER JOIN '.MAIN_DB_PREFIX.'product ON '.MAIN_DB_PREFIX.'product.rowid='.MAIN_DB_PREFIX.'facturedet.fk_product 
where(YEAR('.MAIN_DB_PREFIX.'facture.datef)="'.$debut.'") and ('.MAIN_DB_PREFIX.'facture.fk_soc="'.$client.'");');

				
				

				

if ($resql)
{
			$obj = $db->fetch_object($resql);
			if ($obj)
			{
								$total=$obj->totalht;								
			}
			
}






?>

<canvas id="canvas1" width="600" height="500">
	Requiert un navigateur récent: Internet Explorer 9, Chrome, Firefox, Safari.
</canvas>

<script type="text/javascript">
function invertColor(hex, bw) {
    if (hex.indexOf('#') === 0) {
        hex = hex.slice(1);
    }
    // convert 3-digit hex to 6-digits.
    if (hex.length === 3) {
        hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
    }
    if (hex.length !== 6) {
        throw new Error('Invalid HEX color.');
    }
    var r = parseInt(hex.slice(0, 2), 16),
        g = parseInt(hex.slice(2, 4), 16),
        b = parseInt(hex.slice(4, 6), 16);
    if (bw) {
        // http://stackoverflow.com/a/3943023/112731
        return (r * 0.299 + g * 0.587 + b * 0.114) > 186
            ? '#000000'
            : '#DDDDDD';
    }
    // invert color components
    r = (255 - r).toString(16);
    g = (255 - g).toString(16);
    b = (255 - b).toString(16);
    // pad each with zeros and return
    return "#" + padZero(r) + padZero(g) + padZero(b);
}
function padZero(str, len) {
    len = len || 2;
    var zeros = new Array(len).join('0');
    return (zeros + str).slice(-len);
}
function draw(fin,debut,couleur,num,categorie)
{

  var canvas = document.getElementById("canvas1"); 
  var ctx = canvas.getContext("2d");



	var radius = 200;
	var centerx = 200;
	var centery = 200;
	var offset = 3.14 / 2;
	var startAngle=debut * Math.PI
	var endAngle = fin * Math.PI


  	ctx.beginPath();
	ctx.moveTo(centerx,centery);
	ctx.fillStyle=couleur;   
	ctx.lineWidth="2";   

	ctx.arc(centerx, centery, radius, startAngle, endAngle, true);
	ctx.lineTo(centerx, centery);		
   
  
			
					ctx.fill();

  var arcsector=endAngle-startAngle;
if (arcsector<-0.6)
{
	var langle=startAngle + arcsector / 2 + Math.PI + offset+1.5;
	var lradius=radius*3/4;
	var dx= centerx + lradius * Math.cos(langle);
	var dy= centery + lradius * Math.sin(langle);
    ctx.font = "10pt Calibri,Geneva,Arial";
    ctx.fillStyle = invertColor(couleur,true);
	var pourcentage=-arcsector/ Math.PI/2*100;
	var libelle=categorie + ' - ' + Math.round(pourcentage) + '%';
    ctx.fillText(libelle, dx-(libelle.length*2.6), dy);
}
else
{
	var pourcentage=-arcsector/ Math.PI/2*100;
	if (pourcentage>0)
	{
  	ctx.rect(420,15+(20*num),15,15);
			
					ctx.fill();

    ctx.font = "10pt Calibri,Geneva,Arial";
    ctx.fillStyle = "black";

    ctx.fillText(categorie + ' - ' + Math.round(pourcentage) + '%', 450, 28+(20*num));
	}
}
				
				
}





					




<?php
$couleural =["#0000ff","#BF3030","#1FA055","#FF7F00","#8C008c","#C8ADF1","#FEE347","#606060","#dddddd","#BBD2E1","#FF69B4","#77b5fe","#8C4510","#56739a","#B666D2","#008284"];

$resql=$db->query('SELECT '.MAIN_DB_PREFIX.'categorie.rowid as rowid,'.MAIN_DB_PREFIX.'categorie.color as color,'.MAIN_DB_PREFIX.'categorie.label as label FROM '.MAIN_DB_PREFIX.'categorie where '.MAIN_DB_PREFIX.'categorie.fk_parent =0  and type =0 group by label,rowid');
				

if ($resql)
{
	
			$debutangle=0;
	$num = $db->num_rows($resql);
	$i = 0;
	$j=0;
	if ($num)
	{
		while ($i < $num)
		{

			$obj = $db->fetch_object($resql);
			
			if ($obj)
			{
				
				$resql2=$db->query('SELECT '.$varsearch_motcle_url4.' FROM '.MAIN_DB_PREFIX.'facturedet INNER JOIN '.MAIN_DB_PREFIX.'facture ON '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid INNER JOIN '.MAIN_DB_PREFIX.'product ON '.MAIN_DB_PREFIX.'product.rowid='.MAIN_DB_PREFIX.'facturedet.fk_product INNER JOIN '.MAIN_DB_PREFIX.'categorie_product ON '.MAIN_DB_PREFIX.'categorie_product.fk_product = '.MAIN_DB_PREFIX.'product.rowid INNER JOIN '.MAIN_DB_PREFIX.'categorie on '.MAIN_DB_PREFIX.'categorie.rowid = '.MAIN_DB_PREFIX.'categorie_product.fk_categorie where ('.MAIN_DB_PREFIX.'facture.fk_soc="'.$client.'") and (YEAR('.MAIN_DB_PREFIX.'facture.datef)="'.$debut.'") and ('.MAIN_DB_PREFIX.'categorie.rowid='.$obj->rowid.' or '.MAIN_DB_PREFIX.'categorie.fk_parent='.$obj->rowid.');');



				$obj2 = $db->fetch_object($resql2);
				
		
				
					$angle=$obj2->totalht/$total*2;
					$finangle=$debutangle+$angle;

					if (strlen($obj->color)>0)
					{
						$couleur="#".$obj->color;
					}
					else
					{
						$couleur=$couleural[$i];
					}				
					$cat =$obj->label;
					?>
					window.onload=draw(<?php echo $debutangle;?>,<?php echo $finangle;?>,"<?php echo $couleur;?>",<?php echo $i;?>,"<?php echo $cat;?>"); 
					<?php
					$debutangle=$finangle;

				
			}
			$i++;
		}
	}
}


?>










</script> 
</section>
<section>
<?php
echo '<div class="div-table-responsive-no-min">
	<table class="noborder" max-width="600px"><tr class="liste_titre"><td>'.$langs->trans("Category").'</td><td style="text-align:right;">'.(date("Y")-3).'</td><td style="text-align:right;">'.(date("Y")-2).'</td><td style="text-align:right;">'.(date("Y")-1).'</td><td style="text-align:right;">'.date("Y").'</td></tr>';


{


	
	$resql2=$db->query('SELECT '.MAIN_DB_PREFIX.'categorie.rowid as rowid,'.MAIN_DB_PREFIX.'categorie.color as color,'.MAIN_DB_PREFIX.'categorie.label as label FROM '.MAIN_DB_PREFIX.'categorie where '.MAIN_DB_PREFIX.'categorie.fk_parent =0  and type =0 group by label,rowid');

$i=0;
if ($resql2)
{
		echo '<tr>';

		while ($i < $num)
		{

			$obj2 = $db->fetch_object($resql2);
			
			if ($obj2)
			{
				$cat =$obj2->label;
				
				echo '<td>'.$cat.'</td>';
				
				
				for ($l=2016; $l<=2019;$l++)
				{
				
				$resql3=$db->query('SELECT '.$varsearch_motcle_url4.' FROM '.MAIN_DB_PREFIX.'facturedet INNER JOIN '.MAIN_DB_PREFIX.'facture ON '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid INNER JOIN '.MAIN_DB_PREFIX.'product ON '.MAIN_DB_PREFIX.'product.rowid='.MAIN_DB_PREFIX.'facturedet.fk_product INNER JOIN '.MAIN_DB_PREFIX.'categorie_product ON '.MAIN_DB_PREFIX.'categorie_product.fk_product = '.MAIN_DB_PREFIX.'product.rowid INNER JOIN '.MAIN_DB_PREFIX.'categorie on '.MAIN_DB_PREFIX.'categorie.rowid = '.MAIN_DB_PREFIX.'categorie_product.fk_categorie where ('.MAIN_DB_PREFIX.'facture.fk_soc="'.$client.'") and (YEAR('.MAIN_DB_PREFIX.'facture.datef)="'.$l.'") and ('.MAIN_DB_PREFIX.'categorie.rowid='.$obj2->rowid.' or '.MAIN_DB_PREFIX.'categorie.fk_parent='.$obj2->rowid.');');

				
				
				$obj3 = $db->fetch_object($resql3);
				
				
							
					
					echo '<td style="text-align:right;">'.round($obj3->totalht,0).'</td>';
					
					
				}
					

								

		
					

				
			}
			$i++;
			echo '</tr>';
		}
		
	
}



	echo '<tr><td><br>Total<b></td>';
	$annee=((date("Y"))-3);
	while ($annee<=(date("Y")))
	{	
		
				$reqcumul=$db->query('SELECT '.$varsearch_motcle_url4.' 
										from '.MAIN_DB_PREFIX.'facture 
										INNER JOIN '.MAIN_DB_PREFIX.'facturedet ON '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid
										where (YEAR('.MAIN_DB_PREFIX.'facture.datef)="'.$annee.'") and '.MAIN_DB_PREFIX.'facture.fk_soc="'.$client.'";');								
				if ($reqcumul)
				{
							$objcumul = $db->fetch_object($reqcumul);
								
									echo '<td style="text-align:right;"><b>';
							
								
									echo round($objcumul->totalht,0);
								
								echo '</b></td>';
									
				}
				$annee++;
	}
	echo '</tr>';
	}
















	echo '</table>';
print '</div>';?>

</section>
<?php

					//if (empty($socid))
//{
	// Show filter box
	print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="mode" value="'.$mode.'">';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre"><td class="liste_titre" colspan="2">'.$langs->trans("Filter").'</td></tr>';


	// Year
	print '<tr><td>'.$langs->trans("Année analysée").'</td><td>';

	

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
	// type de données
	print '<tr><td>'.$langs->trans("Unit").'</td><td>';
	if ($_POST["donneesid"]=="ca")	
	{
		print '<input type="radio" name="donneesid" value="ca" id="ca" checked/> <label for="ca">'.$langs->trans("Sales").'</label><br />
			<input type="radio" name="donneesid" value="marge" id="marge" /> <label for="marge">'.$langs->trans("Margin").'</label></select></td></tr>';
	}
else
	{
		print '<input type="radio" name="donneesid" value="ca" id="ca"/> <label for="ca">'.$langs->trans("Sales").'</label><br />
			<input type="radio" name="donneesid" value="marge" id="marge" checked/> <label for="marge">'.$langs->trans("Margin").'</label></select></td></tr>';
	}	
	
	// Client
	print '<tr><td>'.$langs->trans("Customer").'</td><td>';
								print '<select class="texte" name="clientid">';
								print '<OPTION value="0">'.$langs->trans("All").'</OPTION>';
								$resql2=$db->query("select distinct nom, rowid, town from '.MAIN_DB_PREFIX.'societe where client=1 order by nom asc");
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
												
												if ($client == $obj->rowid)	
												{
													
													echo '<OPTION selected value="'.$obj->rowid.'">'.$obj->nom.'</OPTION>';
												}
												else
												{
													echo '<OPTION value="'.$obj->rowid.'">'.$obj->nom.'</OPTION>';	
												}
												
												

											}
											$j++;
										}
									}
								}								

														
												 
										
								print '</select>';
	print '</td></tr>';
	

	print '<tr><td align="center" colspan="2"><input type="submit" name="submit" class="button" value="'.$langs->trans("Refresh").'"></td></tr>';
	print '</table>';
	print '</form>';
	print '<br><br>';
	?>	


			


<?php


llxFooter();

$db->close();
