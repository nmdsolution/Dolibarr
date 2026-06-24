<?php


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

require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

$action = GETPOST('action','aZ09');

$langs->load("companies");

// Security check
$id = GETPOST('id')?GETPOST('id','int'):GETPOST('socid','int');
if ($user->societe_id) $id=$user->societe_id;
$result = restrictedArea($user, 'societe', $id, '&societe');

$object = new Societe($db);
if ($id > 0) $object->fetch($id);

$permissionnote=$user->rights->societe->creer;	// Used by the include of actions_setnotes.inc.php

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('thirdpartynote','globalcard'));





/*
 *	View
 */
 
 $title=$langs->trans("ThirdParty").' - '.$langs->trans("Graphics");
if (! empty($conf->global->MAIN_HTML_TITLE) && preg_match('/thirdpartynameonly/',$conf->global->MAIN_HTML_TITLE) && $object->name) $title=$object->name.' - '.$langs->trans("Graphics");
$help_url='EN:Module_Third_Parties|FR:Module_Tiers|ES:Empresas';
llxHeader('',$title,$help_url);


    /*
     * Affichage onglets
     */
    if (! empty($conf->notification->enabled)) $langs->load("mails");

    $head = societe_prepare_head($object);

    dol_fiche_head($head, 'client2', $langs->trans("ThirdParty"), -1, 'company');

    $linkback = '<a href="'.DOL_URL_ROOT.'/societe/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

    dol_banner_tab($object, 'socid', $linkback, ($user->societe_id?0:1), 'rowid', 'nom'); //bannière coordonnées tiers
 

$form = new Form($db);






//llxHeader("",$langs->trans("Répartition du chiffre d'affaires par produits / services"));
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facturedet.total_ht) AS totalht';		
		$varsearch_motcle_url41='sum('.MAIN_DB_PREFIX.'facture_fourn_det.total_ht) AS totalht';
		$varsearch_motcle_url5='sum('.MAIN_DB_PREFIX.'propal.total_ht ) AS compteur';
	if ($_POST["donneesid"] == "ca")	
	{
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facturedet.total_ht) AS totalht';
		$varsearch_motcle_url41='sum('.MAIN_DB_PREFIX.'facture_fourn_det.total_ht) AS totalht';
		$varsearch_motcle_url5='sum('.MAIN_DB_PREFIX.'propal.total_ht ) AS compteur';

	}
	elseif ($_POST["donneesid"] == "marge")	
	{
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facturedet.total_ht - ( '.MAIN_DB_PREFIX.'facturedet.buy_price_ht)*'.MAIN_DB_PREFIX.'facturedet.qty) AS totalht';
		$varsearch_motcle_url5='count('.MAIN_DB_PREFIX.'propal.total_ht) AS compteur';

	}



if ($user->rights->statistiques->Clients->SAClients)
{

	if (GETPOST('anneedeb')>1)
	{

		$debut=GETPOST('anneedeb');
	}
	else
	{
		$debut=DATE("Y");

	}
	if (isset ($_GET["id"]))
	{
		$client=$_GET["id"];
	}
	else
	{
		$client=$_POST["clientid"];
	}



	echo $langs->trans("Année").' '.$debut;

	$resqlnom=$db->query('SELECT nom, client, fournisseur 
	FROM '.MAIN_DB_PREFIX.'societe
	where rowid='.$client.'');

	if ($resqlnom)
	{
				$objnom = $db->fetch_object($resqlnom);
				if ($objnom)
				{
									echo " - ".$objnom->nom;
									$var_client=$objnom->client;
									$var_fournisseur=$objnom->fournisseur;
				}
				
	}
	
if ($var_client==1)
{
	print load_fiche_titre($langs->trans("CumulVentes"),'','title_accountancy.png');
	
	
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
										from '.MAIN_DB_PREFIX.'facture 
										INNER JOIN '.MAIN_DB_PREFIX.'facturedet ON '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid
										where (YEAR('.MAIN_DB_PREFIX.'facture.datef)="'.$annee.'") and ('.MAIN_DB_PREFIX.'facture.fk_soc = '.$client.') and (month('.MAIN_DB_PREFIX.'facture.datef)<='.($mois+1).');');								
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
	

	
	
	














				$resqmax=$db->query('SELECT '.$varsearch_motcle_url4.', YEAR('.MAIN_DB_PREFIX.'facture.datef) as annee 
									from '.MAIN_DB_PREFIX.'facture 
									INNER JOIN '.MAIN_DB_PREFIX.'facturedet ON '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid
									where (YEAR('.MAIN_DB_PREFIX.'facture.datef)<="'.$debut.'") and ('.MAIN_DB_PREFIX.'facture.fk_soc = '.$client.') group by annee;');
					
													
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
				if ($max>100000) 
				{
					$premiere_grad=round(($max/8),-4);
				}
				else if ($max>10000) 
				{
					$premiere_grad=round(($max/8),-3);
				}
				else if ($max>1000) 
				{
					$premiere_grad=round(($max/8),-2);
				}
				else if ($max>100) 
				{
					$premiere_grad=round(($max/8),-1);
				}
				else if ($max>10) 
				{
					$premiere_grad=round(($max/8),0);
				}

				
					$coef=40/$premiere_grad;
					$largeur=67;

?>


<canvas id="canvas3" width="1000" height="500" style="display:inline-block;">
	Requiert un navigateur récent: Internet Explorer 9, Chrome, Firefox, Safari.
</canvas>

	<script type="text/javascript">
	function draw(x1,y1,x2,y2,couleur)
	{
	  var canvas = document.getElementById("canvas3"); 
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
		var canvas = document.getElementById("canvas3"); 
		var ctx = canvas.getContext("2d");
		ctx.fillStyle = couleur;
		ctx.fillRect(x2,y2,5,5);
	}

	function drawx(mois,moisnom,largeur)
	{
		var canvas = document.getElementById("canvas3"); 
		var ctx = canvas.getContext("2d");
		ctx.font = "08pt Verdana";
		ctx.fillStyle = "black";
		ctx.textAlign="center";
		ctx.fillText(moisnom,738-largeur*mois,480);
	}


	function legende(cat,y,couleur)
	{
		var canvas = document.getElementById("canvas3"); 
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
		var canvas = document.getElementById("canvas3"); 
		var ctx1 = canvas.getContext("2d");
		ctx1.font = "10pt Verdana";
		ctx1.textAlign = "left";
		ctx1.beginPath();

	for (i = 1; i < 11; i++) 
	{ 
	   ctx1.textAlign = "right";
	   ctx1.fillText(valeurgrad*i,800,450-i*40);
	} 
	for (i = 0; i < 11; i++) 
	{ 
	// lignes horizontales (grille)
		ctx1.moveTo(0,450-i*40);
		ctx1.lineTo(750,450-i*40);
	} 

	for (i = 0; i < 11; i++) 
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
										from '.MAIN_DB_PREFIX.'facture 
										INNER JOIN '.MAIN_DB_PREFIX.'facturedet ON '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid 
										where (YEAR('.MAIN_DB_PREFIX.'facture.datef)="'.$annee.'") and (month('.MAIN_DB_PREFIX.'facture.datef)<='.($mois).') and ('.MAIN_DB_PREFIX.'facture.fk_soc = '.$client.') ;');								
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



$resql=$db->query('SELECT '.$varsearch_motcle_url5.' 
FROM '.MAIN_DB_PREFIX.'propal
where (YEAR('.MAIN_DB_PREFIX.'propal.datec)="'.$debut.'") and ('.MAIN_DB_PREFIX.'propal.fk_soc="'.$client.'");');
				
				

				

if ($resql)
{
			$obj = $db->fetch_object($resql);
			if ($obj)
			{
								$total2=$obj->compteur;								
			}
			
}












if ($var_client==1)
{
print load_fiche_titre($langs->trans("Statutdesdevis"),'','title_accountancy.png');?>
<canvas id="canvas2" width="600" height="500">
	Requiert un navigateur récent: Internet Explorer 9, Chrome, Firefox, Safari.
</canvas>
<?php
	if ($_POST["donneesid"] == "ca")
	{
		print load_fiche_titre($langs->trans("SalesProductServiceCategoryDistribution"),'','title_accountancy.png');
	}
	elseif ($_POST["donneesid"] == "marge")		
	{
		print load_fiche_titre($langs->trans("MarginProductServiceCategoryDistribution"),'','title_accountancy.png');		
	}
	else
	{
		print load_fiche_titre($langs->trans("SalesProductServiceCategoryDistribution"),'','title_accountancy.png');
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
	
    ctx.fillText(categorie, dx-(categorie.length*2.6), dy);
	var pourc=Math.round(pourcentage) + '%';
    ctx.fillText(pourc, dx-(pourc.length*2.4), dy+12);

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





function draw2(fin,debut,num,categorie)
{

  var canvas = document.getElementById("canvas2"); 
  var ctx = canvas.getContext("2d");

var couleur =["#73c613","#00afec","#f09600","#e6007e","#074f74","#aa1df9","#ff4b4b","#a3e4e4","#e6cbdb","#9d9494","#1c7fdf","#dadf0d","#6c1287","#56739a","#B666D2","#008284"];

	var radius = 200;
	var centerx = 200;
	var centery = 200;
	var offset = Math.PI / 2;
	var startAngle=debut * Math.PI
	var endAngle = fin * Math.PI

	


  	ctx.beginPath();
	ctx.moveTo(centerx,centery);
	ctx.fillStyle=couleur[num];   
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
    ctx.fillStyle = invertColor(couleur[num],true);
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
}
if ($var_client==1)
{
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

}


?>

<?php
$resql=$db->query('SELECT '.$varsearch_motcle_url5.','.MAIN_DB_PREFIX.'c_propalst.label as label

FROM '.MAIN_DB_PREFIX.'propal
inner join '.MAIN_DB_PREFIX.'c_propalst on '.MAIN_DB_PREFIX.'propal.fk_statut='.MAIN_DB_PREFIX.'c_propalst.id
where (YEAR('.MAIN_DB_PREFIX.'propal.datec)="'.$debut.'" and ('.MAIN_DB_PREFIX.'propal.fk_soc="'.$client.'"))
GROUP BY fk_statut order by compteur desc;');		



if ($resql)
{
$debutangle=0;
	$num = $db->num_rows($resql);
	$i = 0;
	if ($num)
	{
		
		while ($i < $num)
		{
			$couleur= '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)),6, '0', STR_PAD_LEFT);
			$obj = $db->fetch_object($resql);
			if ($obj)
			{
				$angle=$obj->compteur/$total2*2;
				$finangle=$debutangle+$angle;

				?>
				window.onload=draw2(<?php echo $debutangle;?>,<?php echo $finangle;?>,<?php echo $i;?>,"<?php echo $obj->label;?>"); 
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
<script>

<?php
$resql=$db->query('SELECT '.$varsearch_motcle_url41.' 
FROM '.MAIN_DB_PREFIX.'facture_fourn 
INNER JOIN '.MAIN_DB_PREFIX.'facture_fourn_det ON '.MAIN_DB_PREFIX.'facture_fourn_det.fk_facture_fourn = '.MAIN_DB_PREFIX.'facture_fourn.rowid 
INNER JOIN '.MAIN_DB_PREFIX.'product ON '.MAIN_DB_PREFIX.'product.rowid='.MAIN_DB_PREFIX.'facture_fourn_det.fk_product 
where(YEAR('.MAIN_DB_PREFIX.'facture_fourn.datef)="'.$debut.'") and ('.MAIN_DB_PREFIX.'facture_fourn.fk_soc="'.$client.'");');

if ($resql)
{
			$obj = $db->fetch_object($resql);
			if ($obj)
			{
								$total=$obj->totalht;								
			}			
}
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
				
				$resql2=$db->query('SELECT '.$varsearch_motcle_url41.' FROM '.MAIN_DB_PREFIX.'facture_fourn_det INNER JOIN '.MAIN_DB_PREFIX.'facture_fourn ON '.MAIN_DB_PREFIX.'facture_fourn_det.fk_facture_fourn = '.MAIN_DB_PREFIX.'facture_fourn.rowid INNER JOIN '.MAIN_DB_PREFIX.'product ON '.MAIN_DB_PREFIX.'product.rowid='.MAIN_DB_PREFIX.'facture_fourn_det.fk_product INNER JOIN '.MAIN_DB_PREFIX.'categorie_product ON '.MAIN_DB_PREFIX.'categorie_product.fk_product = '.MAIN_DB_PREFIX.'product.rowid INNER JOIN '.MAIN_DB_PREFIX.'categorie on '.MAIN_DB_PREFIX.'categorie.rowid = '.MAIN_DB_PREFIX.'categorie_product.fk_categorie where ('.MAIN_DB_PREFIX.'facture_fourn.fk_soc="'.$client.'") and (YEAR('.MAIN_DB_PREFIX.'facture_fourn.datef)="'.$debut.'") and ('.MAIN_DB_PREFIX.'categorie.rowid='.$obj->rowid.' or '.MAIN_DB_PREFIX.'categorie.fk_parent='.$obj->rowid.');');

				//echo 'SELECT '.$varsearch_motcle_url41.' FROM '.MAIN_DB_PREFIX.'facture_fourn_det INNER JOIN '.MAIN_DB_PREFIX.'facture_fourn ON '.MAIN_DB_PREFIX.'facture_fourn_det.fk_facture_fourn = '.MAIN_DB_PREFIX.'facture_fourn.rowid INNER JOIN '.MAIN_DB_PREFIX.'product ON '.MAIN_DB_PREFIX.'product.rowid='.MAIN_DB_PREFIX.'facture_fourn_det.fk_product INNER JOIN '.MAIN_DB_PREFIX.'categorie_product ON '.MAIN_DB_PREFIX.'categorie_product.fk_product = '.MAIN_DB_PREFIX.'product.rowid INNER JOIN '.MAIN_DB_PREFIX.'categorie on '.MAIN_DB_PREFIX.'categorie.rowid = '.MAIN_DB_PREFIX.'categorie_product.fk_categorie where ('.MAIN_DB_PREFIX.'facture_fourn.fk_soc="'.$client.'") and (YEAR('.MAIN_DB_PREFIX.'facture_fourn.datef)="'.$debut.'") and ('.MAIN_DB_PREFIX.'categorie.rowid='.$obj->rowid.' or '.MAIN_DB_PREFIX.'categorie.fk_parent='.$obj->rowid.');';

				
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

	
	if ($var_fournisseur>0)
	{
	echo '<div class="div-table-responsive-no-min">
			<table class="noborder" max-width="600px"><tr class="liste_titre"><td>'.$langs->trans("Category").'</td><td style="text-align:right;">'.($debut-3).'</td><td style="text-align:right;">'.($debut-2).'</td><td style="text-align:right;">'.($debut-1).'</td><td style="text-align:right;">'.$debut.'</td></tr>';
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
						echo '<td>'.$obj2->label.'</td>';
						//for ($l=$debut-3); $l<=$debut;$l++)
						for ($l=($debut-3); $l<=($debut);$l++)
						{
							$resql3=$db->query('SELECT '.$varsearch_motcle_url41.' FROM '.MAIN_DB_PREFIX.'facture_fourn_det INNER JOIN '.MAIN_DB_PREFIX.'facture_fourn ON '.MAIN_DB_PREFIX.'facture_fourn_det.fk_facture_fourn = '.MAIN_DB_PREFIX.'facture_fourn.rowid INNER JOIN '.MAIN_DB_PREFIX.'product ON '.MAIN_DB_PREFIX.'product.rowid='.MAIN_DB_PREFIX.'facture_fourn_det.fk_product INNER JOIN '.MAIN_DB_PREFIX.'categorie_product ON '.MAIN_DB_PREFIX.'categorie_product.fk_product = '.MAIN_DB_PREFIX.'product.rowid INNER JOIN '.MAIN_DB_PREFIX.'categorie on '.MAIN_DB_PREFIX.'categorie.rowid = '.MAIN_DB_PREFIX.'categorie_product.fk_categorie where ('.MAIN_DB_PREFIX.'facture_fourn.fk_soc="'.$client.'") and (YEAR('.MAIN_DB_PREFIX.'facture_fourn.datef)="'.$l.'") and ('.MAIN_DB_PREFIX.'categorie.rowid='.$obj2->rowid.' or '.MAIN_DB_PREFIX.'categorie.fk_parent='.$obj2->rowid.');');
							$obj3 = $db->fetch_object($resql3);
							echo '<td style="text-align:right;">'.round($obj3->totalht,0).'</td>';	
						}		
					}
					$i++;
					echo '</tr>';
				}	

				echo '<tr><td><br>Total<b></td>';
				$annee=($debut-3);
				while ($annee<=($debut))
				{	
					$reqcumul=$db->query('SELECT '.$varsearch_motcle_url41.' 
										from '.MAIN_DB_PREFIX.'facture_fourn 
										INNER JOIN '.MAIN_DB_PREFIX.'facture_fourn_det ON '.MAIN_DB_PREFIX.'facture_fourn_det.fk_facture_fourn = '.MAIN_DB_PREFIX.'facture_fourn.rowid
										where (YEAR('.MAIN_DB_PREFIX.'facture_fourn.datef)="'.$annee.'") and '.MAIN_DB_PREFIX.'facture_fourn.fk_soc="'.$client.'";');								
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
			echo '</table></div>';
	}
			?>

</section>
<section>
<?php
	if ($var_client>0)
	{
	echo '<div class="div-table-responsive-no-min">
	<table class="noborder" max-width="600px"><tr class="liste_titre"><td>'.$langs->trans("Category").'</td><td style="text-align:right;">'.($debut-3).'</td><td style="text-align:right;">'.($debut-2).'</td><td style="text-align:right;">'.($debut-1).'</td><td style="text-align:right;">'.$debut.'</td></tr>';

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
					for ($l=($debut-3); $l<=($debut);$l++)
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
	$annee=($debut-3);
	while ($annee<=($debut))
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

	echo '</table></div>';
	}
?>


</section>
</section>
<?php

					//if (empty($socid))
//{
	// Show filter box
	print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$client.'">';
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
	if ($var_client==1)
{
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
}	
	

	print '<tr><td align="center" colspan="2"><input type="submit" name="submit" class="button" value="'.$langs->trans("Refresh").'"></td></tr>';
	print '</table>';
	print '</form>';
























	}
else
{
	echo $langs->trans("Accès non autorisé.");
}








if (! $id)
{
    dol_fiche_end();
}

// End of page
llxFooter();
$db->close();

