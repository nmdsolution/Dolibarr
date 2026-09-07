<?php
/* Copyright (C) 2001-2007	Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (c) 2004-2017	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012	Regis Houssin			<regis.houssin@inodbox.com>
 * Copyright (C) 2005		Eric Seigne				<eric.seigne@ryxeo.com>
 * Copyright (C) 2013		Juanjo Menent			<jmenent@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *       \file       htdocs/product/stats/card.php
 *       \ingroup    product
 *       \brief      Page of product statistics
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


require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';

$WIDTH=DolGraph::getDefaultGraphSizeForStats('width',380);
$HEIGHT=DolGraph::getDefaultGraphSizeForStats('height',160);

// Load translation files required by the page
$langs->loadLangs(array('companies', 'products', 'stocks', 'bills', 'other'));

$id		= GETPOST('id','int');         // For this page, id can also be 'all'
$ref	= GETPOST('ref');
$mode	= (GETPOST('mode') ? GETPOST('mode') : 'byunit');
$search_year   = GETPOST('search_year','int');
$search_categ  = GETPOST('search_categ','int');

$error	= 0;
$mesg	= '';
$graphfiles=array();

$socid='';
if (! empty($user->societe_id)) $socid=$user->societe_id;

// Security check
$fieldvalue = (! empty($id) ? $id : $ref);
$fieldtype = (! empty($ref) ? 'ref' : 'rowid');
$result=restrictedArea($user,'produit|service',$fieldvalue,'product&product','','',$fieldtype);

$tmp=dol_getdate(dol_now());
$currentyear=$tmp['year'];
if (empty($search_year)) $search_year=$currentyear;


/*
 * Actions
 */

// None


/*
 *	View
 */
 
 

$form = new Form($db);
$htmlother = new FormOther($db);
$object = new Product($db);



if (! $id && empty($ref))
{
    llxHeader("",$langs->trans("ProductStatistics"));

    $type = GETPOST('type','int');

   	$helpurl='';
    if ($type == '0')
    {
        $helpurl='EN:Module_Products|FR:Module_Produits|ES:M&oacute;dulo_Productos';
        $title=$langs->trans("StatisticsOfProducts");
        $title=$langs->trans("Graphics");
    }
    else if ($type == '1')
    {
        $helpurl='EN:Module_Services_En|FR:Module_Services|ES:M&oacute;dulo_Servicios';
        $title=$langs->trans("StatisticsOfServices");
        $title=$langs->trans("Statistics");
    }
    else
    {
        $helpurl='EN:Module_Services_En|FR:Module_Services|ES:M&oacute;dulo_Servicios';
        //$title=$langs->trans("StatisticsOfProductsOrServices");
        $title=$langs->trans("Statistics");
    }

    print load_fiche_titre($title, $mesg,'title_products.png');
}
else
{
    $result = $object->fetch($id,$ref);

	$title = $langs->trans('ProductServiceCard');
	$helpurl = '';
	$shortlabel = dol_trunc($object->label,16);
	if (GETPOST("type") == '0' || ($object->type == Product::TYPE_PRODUCT))
	{
		$title = $langs->trans('Product')." ". $shortlabel ." - ".$langs->trans('Graphics');
		$helpurl='EN:Module_Products|FR:Module_Produits|ES:M&oacute;dulo_Productos';
	}
	if (GETPOST("type") == '1' || ($object->type == Product::TYPE_SERVICE))
	{
		$title = $langs->trans('Service')." ". $shortlabel ." - ".$langs->trans('Graphics');
		$helpurl='EN:Module_Services_En|FR:Module_Services|ES:M&oacute;dulo_Servicios';
	}

	llxHeader('', $title, $helpurl);
}


if ($result && (! empty($id) || ! empty($ref)))
{
	$head=product_prepare_head($object);
	$titre=$langs->trans("CardProduct".$object->type);
	$picto=($object->type==Product::TYPE_SERVICE?'service':'product');

	dol_fiche_head($head, 'produit2', $titre, -1, $picto);

	$linkback = '<a href="'.DOL_URL_ROOT.'/product/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

    dol_banner_tab($object, 'ref', $linkback, ($user->societe_id?0:1), 'ref', '', '', '', 0, '', '', 1);

    dol_fiche_end();
}
if (empty($id) & empty($ref))
{
    $h=0;
    $head = array();

    $head[$h][0] = DOL_URL_ROOT.'/product/stats/card.php'.($type != ''?'?type='.$type:'');
    $head[$h][1] = $langs->trans("Chart");
    $head[$h][2] = 'chart';
    $h++;

	$title = $langs->trans("ListProductServiceByPopularity");
    if ((string) $type == '1') {
    	$title = $langs->trans("ListServiceByPopularity");
    }
    if ((string) $type == '0') {
    	$title = $langs->trans("ListProductByPopularity");
    }

    $head[$h][0] = DOL_URL_ROOT.'/product/popuprop.php'.($type != ''?'?type='.$type:'');
    $head[$h][1] = $title;
    $head[$h][2] = 'popularityprop';
    $h++;

    dol_fiche_head($head, 'chart', $langs->trans("Graphics"), -1);
}


if ($result || empty($id))
{
    print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
    print '<input type="hidden" name="id" value="'.$id.'">';

$resql=$db->query('SELECT label from '.MAIN_DB_PREFIX.'product where rowid='.GETPOST('id').'');



		print load_fiche_titre($langs->trans("PurchasePrice"),'','title_accountancy.png');
if ($user->rights->statistiques->Achats->SAAchats)
{
echo '<section style="position:relative; display:inline-block;height:600px;width:1000px;background-color:#fff;">';

if ($_GET["id"] > 0)
{
$varsearch_motcle_url='('.MAIN_DB_PREFIX.'facture_fourn_det.fk_product="'.GETPOST('id').'")';

					$max=1;
					$nbenr=1;

					
$resql=$db->query('SELECT '.MAIN_DB_PREFIX.'facture_fourn_det.pu_ht,'.MAIN_DB_PREFIX.'facture_fourn.datef as datef from '.MAIN_DB_PREFIX.'facture_fourn_det inner join '.MAIN_DB_PREFIX.'facture_fourn on '.MAIN_DB_PREFIX.'facture_fourn.rowid='.MAIN_DB_PREFIX.'facture_fourn_det.fk_facture_fourn where'.$varsearch_motcle_url.'  and ('.MAIN_DB_PREFIX.'facture_fourn_det.pu_ht>0) order by datef desc limit 20');

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
					if ($largeur>75)
					{
						$largeur=75;
					}
						

$resql=$db->query('SELECT '.MAIN_DB_PREFIX.'facture_fourn.fk_soc as fksoc, DAY('.MAIN_DB_PREFIX.'facture_fourn.datef) as jour, monthname('.MAIN_DB_PREFIX.'facture_fourn.datef) as mois, YEAR('.MAIN_DB_PREFIX.'facture_fourn.datef) as annee, '.MAIN_DB_PREFIX.'facture_fourn_det.pu_ht,'.MAIN_DB_PREFIX.'facture_fourn.datef as datef from '.MAIN_DB_PREFIX.'facture_fourn_det inner join '.MAIN_DB_PREFIX.'facture_fourn on '.MAIN_DB_PREFIX.'facture_fourn.rowid='.MAIN_DB_PREFIX.'facture_fourn_det.fk_facture_fourn where'.$varsearch_motcle_url.'  and ('.MAIN_DB_PREFIX.'facture_fourn_det.pu_ht>0) order by datef desc limit 20 ');


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
						echo'<div style="display: inline-block;transform: rotate(315deg); position: absolute; left: '.(750-($largeur*$enr)).'px; width:'.($largeur-5).'px; text-align:center;top:0px; height:15px; border-radius:3px;z-index:1;">'.$objsoc->nom.'</div>';

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
					echo'<div class="barreN" style="display: inline-block; position: absolute; left: '.(750-($largeur*$enr)).'px; width:'.($largeur-5).'px; bottom:0px; height:'.($obj->pu_ht*$coef).'px; border-radius:3px;background-color:#'.$couleur.';z-index:1;"><div class="bulle">'.number_format($obj->pu_ht, 2, ',', ' ').' '.$conf->global->MAIN_MONNAIE.'</div></div>';
								
						
echo '<div style="display: inline-block; position: absolute; text-align:right; transform: rotate(315deg); left: '.(720-($largeur*$enr)).'px; width:'.($largeur+35).'px; bottom:-80px;  height:55px;text-align:center;color:#666;font-size:10px;">'.$obj->jour.' '.$langs->trans($obj->mois).' '.$obj->annee.'</div>';
					echo '<div class="valeurhistogramme" style="display: inline-block; position: absolute; left: '.(750-($largeur*$enr)).'px; width:'.($largeur-5).'px; bottom:'.(($obj->pu_ht*$coef)-20).'px; height:20px;background:rgba(175, 175, 175, 0.4);text-align:center;z-index:3">'.round($obj->pu_ht,2).'</div>';			

							
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
						echo '<div style="text-align:right;diplay:block;position:absolute; width:50px; height:30px;left:00px; top:'.(((7-$i)*50)+43).'px;bottom:0px; margin:0px; z-index:0;color:#888;">'.$premiere_grad*$i.'</div>';
					}
					echo '</div></section>';
					

		
}			
					
					
		

	


	}
else
{
	echo $langs->trans("Accès non autorisé.");
}









}

if (! $id)
{
    dol_fiche_end();
}

// End of page
llxFooter();
$db->close();
