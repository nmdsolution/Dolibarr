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

llxHeader("",$langs->trans("Statutdesdevis"));



			$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'propal.total_ht ) AS compteur';
	if ($_POST["donneesid"] == "ca")	
	{
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'propal.total_ht ) AS compteur';
print load_fiche_titre($langs->trans("Statutdesdevis"),'','title_accountancy.png');
	}
	elseif ($_POST["donneesid"] == "nombre")	
	{
		$varsearch_motcle_url4='count('.MAIN_DB_PREFIX.'propal.total_ht) AS compteur';
print load_fiche_titre($langs->trans("Statutdesdevis"),'','title_accountancy.png');
	}
	else
	{
		print load_fiche_titre($langs->trans("Statutdesdevis"),'','title_accountancy.png');
	}


print '<div class="fichecenter"><div class="fichethirdleft">';

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

	echo $langs->trans("Year").' '.$debut;
	

	

$resql=$db->query('SELECT '.$varsearch_motcle_url4.' 
FROM '.MAIN_DB_PREFIX.'propal
where (YEAR('.MAIN_DB_PREFIX.'propal.datec)="'.$debut.'");');
				
				

				

if ($resql)
{
			$obj = $db->fetch_object($resql);
			if ($obj)
			{
								$total=$obj->compteur;								
			}
}




?>

<canvas id="canvas1" width="600" height="410">
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
function draw(fin,debut,num,categorie)
{

  var canvas = document.getElementById("canvas1"); 
  var ctx = canvas.getContext("2d");

var couleur =["#00afec","#f09600","#e6007e","#074f74","#aa1df9","#ff4b4b","#a3e4e4","#e6cbdb","#9d9494","#1c7fdf","#dadf0d","#73c613","#6c1287","#56739a","#B666D2","#008284"];

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
	
			ctx.shadowOffsetX = 3;
		ctx.shadowOffsetY = 3;
		ctx.shadowBlur = 2;
		ctx.shadowColor   = 'rgba(100, 100, 100, 0.5)';
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
$resql=$db->query('SELECT '.$varsearch_motcle_url4.','.MAIN_DB_PREFIX.'c_propalst.code as code
FROM '.MAIN_DB_PREFIX.'propal
inner join '.MAIN_DB_PREFIX.'c_propalst on '.MAIN_DB_PREFIX.'propal.fk_statut='.MAIN_DB_PREFIX.'c_propalst.id
where (YEAR('.MAIN_DB_PREFIX.'propal.datec)="'.$debut.'")
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
				$angle=$obj->compteur/$total*2;
				$finangle=$debutangle+$angle;
				?>
				window.onload=draw(<?php echo $debutangle;?>,<?php echo $finangle;?>,<?php echo $i;?>,"<?php echo $langs->trans("$obj->code");?>"); 
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
<?php
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
