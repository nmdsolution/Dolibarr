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


llxHeader("",$langs->trans("SellsMulticriteria"));

print load_fiche_titre($langs->trans("SellsMulticriteria"),'','title_accountancy.png');


print '<div class="fichecenter"><div>';
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

$familleid=$_POST["familleid"];
$produitid=$_POST["produitid"];
$factureurid=$_POST["factureurid"];
$commercialid=$_POST["commercialid"];
$clientid=$_POST["clientid"];
$donneesid=$_POST["donneesid"];
$periodeid=$_POST["periodeid"];
$marqueid=$_POST["marqueid"];



if (($_POST["familleid"] > 0) or ($_POST["factureurid"] > 0) or ($_POST["produitid"] > 0) or ($_POST["commercialid"] > 0)or ($_POST["clientid"] > 0)or($_POST["periodeid"] > 0)or($_POST["marqueid"] > 0))
{
	echo $langs->trans("Year").' '.$debut;
	
	$resql=$db->query('select libelle from '.MAIN_DB_PREFIX.'c_typent where id='.GETPOST('familleid').'');
	if ($resql)
	{
		$num = $db->num_rows($resql);		
		$obj = $db->fetch_object($resql);							
		echo $obj->libelle.'<br>';		
	}
	
	$resql2=$db->query('select label from '.MAIN_DB_PREFIX.'categorie where rowid='.GETPOST('produitid').'');
	if ($resql2)
	{
		$num2 = $db->num_rows($resql2);
		$obj2 = $db->fetch_object($resql2);							
		echo $obj2->label.'<br>';		
	}
	
	if ($_POST["familleid"] > 0)
	{
		$varsearch_motcle_url='and ('.MAIN_DB_PREFIX.'societe.fk_typent="'.$familleid.'")';
	}
	else
	{
		$varsearch_motcle_url='';
	}
	
	if ($_POST["produitid"] > 0)	
	{
		$varsearch_motcle_url2='and ('.MAIN_DB_PREFIX.'categorie.rowid="'.$produitid.'" or '.MAIN_DB_PREFIX.'categorie.fk_parent="'.$produitid.'")';
		$varsearch_motcle_url5='left join '.MAIN_DB_PREFIX.'categorie_product on '.MAIN_DB_PREFIX.'categorie_product.fk_product='.MAIN_DB_PREFIX.'facturedet.fk_product inner join '.MAIN_DB_PREFIX.'categorie on '.MAIN_DB_PREFIX.'categorie.rowid = '.MAIN_DB_PREFIX.'categorie_product.fk_categorie';
	}
		else
	{
		$varsearch_motcle_url2='';
		$varsearch_motcle_url5='';
	}
	
	if ($_POST["commercialid"] > 0)	
	{
		$varsearch_motcle_urlcomm='and ('.MAIN_DB_PREFIX.'facture.fk_user_author = '.$commercialid.')';
	}
		else
	{
		$varsearch_motcle_urlcomm='';
	}
	
	if ($_POST["factureurid"] > 0)	
	{
		$varsearch_motcle_urlfactureur='and ('.MAIN_DB_PREFIX.'societe_commerciaux.fk_user = '.$factureurid.')';
	}
		else
	{
		$varsearch_motcle_urlfactureur='';
	}
	
	if ($_POST["clientid"] > 0)	
	{
		$varsearch_motcle_url3='and ('.MAIN_DB_PREFIX.'facture.fk_soc = '.$clientid.')';
	}
		else
	{
		$varsearch_motcle_url3='';
	}
	if ($_POST["donneesid"] == "ca")	
	{
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facturedet.total_ht ) AS totalht';
		$varsearch_motcle_url7='';
		$unite=$conf->global->MAIN_MONNAIE;
	}
	if ($_POST["donneesid"] == "marge")	
	{
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facturedet.total_ht - ( '.MAIN_DB_PREFIX.'facturedet.buy_price_ht)*'.MAIN_DB_PREFIX.'facturedet.qty*SIGN('.MAIN_DB_PREFIX.'facturedet.total_ht)) AS totalht';
		$varsearch_motcle_url7='';
		$unite=$conf->global->MAIN_MONNAIE;
	}
	if ($_POST["donneesid"] == "nb")	
	{
		$varsearch_motcle_url4='sum('.MAIN_DB_PREFIX.'facturedet.qty) AS totalht';
		$varsearch_motcle_url7='';
	}
	if ($_POST["periodeid"] == 1)	
	{
		$varsearch_motcle_url6='GROUP BY annee,mois,moisnom ORDER by annee DESC, mois DESC';
		$varsearch_etiquetteX='monthname( '.MAIN_DB_PREFIX.'facture.datef ) AS moisnom,  month( '.MAIN_DB_PREFIX.'facture.datef ) AS mois,YEAR( '.MAIN_DB_PREFIX.'facture.datef ) AS annee ';
		
		$varsearch_motcle_url10='month( '.MAIN_DB_PREFIX.'facture.datef ) AS mois,monthname( '.MAIN_DB_PREFIX.'facture.datef ) AS moisnom, YEAR( '.MAIN_DB_PREFIX.'facture.datef ) AS annee';
	}
	if ($_POST["periodeid"] == 2)	
	{
		$varsearch_motcle_url6='GROUP BY annee ORDER by annee DESC';
		$varsearch_etiquetteX='YEAR( '.MAIN_DB_PREFIX.'facture.datef ) AS annee ';	
		$varsearch_motcle_url10='YEAR( '.MAIN_DB_PREFIX.'facture.datef ) AS annee';
	}
	if (strlen($marqueid)>0)
	{
		$varsearch_motcle_url8='and ('.MAIN_DB_PREFIX.'product_extrafields.marque = "'.$marqueid.'")';
		$varsearch_motcle_url9='left join '.MAIN_DB_PREFIX.'product_extrafields on '.MAIN_DB_PREFIX.'product_extrafields.fk_object='.MAIN_DB_PREFIX.'facturedet.fk_product';
		
	}
	else
	{
		$varsearch_motcle_url8='';
		$varsearch_motcle_url9="";
		
}		

	$max=1;
	$nbenr=1;
						
	$resql=$db->query('SELECT '.$varsearch_motcle_url4.','.$varsearch_motcle_url10.' FROM '.MAIN_DB_PREFIX.'facture 
	INNER JOIN '.MAIN_DB_PREFIX.'facturedet ON '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid 
	INNER JOIN '.MAIN_DB_PREFIX.'societe ON '.MAIN_DB_PREFIX.'societe.rowid='.MAIN_DB_PREFIX.'facture.fk_soc
	LEFT JOIN '.MAIN_DB_PREFIX.'societe_commerciaux on '.MAIN_DB_PREFIX.'societe.rowid='.MAIN_DB_PREFIX.'societe_commerciaux.fk_soc
	 '.$varsearch_motcle_url5.' 
	'.$varsearch_motcle_url9.' 
	where (YEAR('.MAIN_DB_PREFIX.'facture.datef)<="'.$debut.'") '.$varsearch_motcle_url.' '.$varsearch_motcle_url2.' '.$varsearch_motcle_urlcomm.' '.$varsearch_motcle_urlfactureur.' '.$varsearch_motcle_url3.' '.$varsearch_motcle_url8.'
	'.$varsearch_motcle_url6.' limit 0,24;');
	

	


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
						$nbenr++;
					
				}
				$i++;
			}
		}
	}
	

include ("calcul_echelle.php");					
					


					$coef=44/$premiere_grad;
					
					$largeur=(800/12);
					$largeur=round($largeur,2);
					

						
	$resql=$db->query('SELECT '.$varsearch_motcle_url4.', '.$varsearch_etiquetteX.' FROM '.MAIN_DB_PREFIX.'facture 
	INNER JOIN '.MAIN_DB_PREFIX.'facturedet ON '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid 
	INNER JOIN '.MAIN_DB_PREFIX.'societe ON '.MAIN_DB_PREFIX.'societe.rowid='.MAIN_DB_PREFIX.'facture.fk_soc
	LEFT JOIN '.MAIN_DB_PREFIX.'societe_commerciaux on '.MAIN_DB_PREFIX.'societe.rowid='.MAIN_DB_PREFIX.'societe_commerciaux.fk_soc
	 '.$varsearch_motcle_url5.' '.$varsearch_motcle_url9.' 
	where (YEAR('.MAIN_DB_PREFIX.'facture.datef)<="'.$debut.'") '.$varsearch_motcle_url.' '.$varsearch_motcle_url2.' '.$varsearch_motcle_urlcomm.' '.$varsearch_motcle_urlfactureur.' '.$varsearch_motcle_url3.' '.$varsearch_motcle_url8.'
	'.$varsearch_motcle_url6.' limit 0,36;');

	

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
						echo'<div class="barreN" style="display: inline-block;position: absolute; left: '.(765-($largeur*$enr)).'px; width:'.($largeur-25).'px;  bottom:0px; height:'.($obj->totalht*$coef).'px; border-radius:3px;background-color:#1E7FCB;z-index:1;"><div class="bulle">'.number_format($obj->totalht, 2, ',', ' ').' '.$unite.'</div></div>';
									
	if ($_POST["periodeid"] == 1)	
	{
echo '<div style="display: inline-block; position: absolute; text-align:right; transform: rotate(315deg); left: '.(745-($largeur*$enr)).'px; width:'.($largeur+35).'px; bottom:-80px;  height:55px;text-align:center;color:#666;font-size:10px;">'.$obj->jour.' '.$langs->trans($obj->moisnom).' '.$obj->annee.'</div>';
	

	}
	if ($_POST["periodeid"] == 2)	
	{
echo '<div style="display: inline-block; position: absolute; text-align:right; transform: rotate(315deg); left: '.(745-($largeur*$enr)).'px; width:'.($largeur+35).'px; bottom:-80px;  height:55px;text-align:center;color:#666;font-size:10px;">'.$obj->annee.'</div>';
	

	}										
								
								echo '<div class="valeurhistogramme" style="display: inline-block; position: absolute; left: '.(765-($largeur*$enr)).'px; width:'.($largeur-25).'px; text-align:center;bottom:+0px; height:20px;z-index:3">'.number_format(($obj->totalht), 0, ',', ' ').'</div>';
								$enr++;
				}
				$i++;
			}
			while ($i < 24)
			{
				$obj = $db->fetch_object($resql);
				if ($obj)
				{
						echo'<div style="display: inline-block; opacity:0.5; position: absolute; left: '.(775-($largeur*($enr-12))).'px; width:'.($largeur-25).'px; bottom:0px; height:'.($obj->totalht*$coef).'px; border-radius:3px;background-color:#1E7FCB;z-index:1;"></div>';
									
										
	$enr++;
				}
				$i++;
			}
			while ($i < 36)
			{
				$obj = $db->fetch_object($resql);
				if ($obj)
				{
						echo'<div style="display: inline-block; opacity:0.3; position: absolute; left: '.(785-($largeur*($enr-24))).'px; width:'.($largeur-25).'px; bottom:0px; height:'.($obj->totalht*$coef).'px; border-radius:3px;background-color:#1E7FCB;z-index:1;"></div>';
									
										
	$enr++;
				}
				$i++;
			}

		}
	}
							echo '</div>';
					echo '<div style="diplay:block;position:absolute; background-color:#222; width:830px; height:3px;left:70px; top:400px;bottom:0px; margin:0px;"></div>';
															//quadrillage et echelle des ordonnées
					for ($i = 1; $i <= 8; $i++) {
						echo '<div style="diplay:block;position:absolute; background-color:#888; width:830px; height:1px;left:70px; top:'.(((8-$i)*45)+45).'px;bottom:0px; margin:0px;"></div>';
						echo '<div style="diplay:block;position:absolute; width:50px; height:30px;left:10px; top:'.(((8-$i)*45)+38).'px;bottom:0px; margin:0px;text-align:right; z-index:0;color:#888;">'.number_format(($premiere_grad*$i), 0, ',', ' ').'</div>';
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
	// Familles
	print '<tr><td>'.$langs->trans("CustomerCategory").'</td><td>';

								print '<select class="field" id="familleid" name="familleid">';
								print '<OPTION value="0">'.$langs->trans("All").'</OPTION>';
								$resql2=$db->query('select distinct libelle, id from '.MAIN_DB_PREFIX.'c_typent where active="1"');
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
												
													if ($_POST["familleid"] == $obj->id)	
												{
													
													echo '<OPTION selected value="'.$obj->id.'">'.$obj->libelle.'</OPTION>';
												}
												else
												{
													echo '<OPTION value="'.$obj->id.'">'.$obj->libelle.'</OPTION>';	
												}
											}
											$j++;
										}
									}
								}
								print '</select></td></tr>';
	// Familles
	print '<tr><td>'.$langs->trans("ProductCategory").'</td><td>';
								print '<select class="texte" name="produitid">';
								print '<OPTION value="0">'.$langs->trans("All").'</OPTION>';
								$resql2=$db->query('select distinct label, '.MAIN_DB_PREFIX.'categorie.rowid as row_id, '.MAIN_DB_PREFIX.'categorie.fk_parent as fk_parent from '.MAIN_DB_PREFIX.'categorie where type=0 order by fk_parent asc,label asc');
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
												if ($_POST["produitid"] == $obj->row_id)	
												{
													if (($obj->fk_parent)>0)
													{
														print '<OPTION selected value="'.$obj->row_id.'">- '.$obj->label.' -</OPTION>';
													}
													else
													{
														print '<OPTION selected value="'.$obj->row_id.'">'.$obj->label.'</OPTION>';
													}
												}
												else
												{
													if (($obj->fk_parent)>0)
													{
														print '<OPTION value="'.$obj->row_id.'">- '.$obj->label.' -</OPTION>';
													}
													else
													{
														print '<OPTION value="'.$obj->row_id.'">'.$obj->label.'</OPTION>';
													}
												}													
												// You can use here results
												
											}
											$j++;
										}
									}
								}
								print '</select></td></tr>';
								
	// type de données
	print '<tr><td>'.$langs->trans("Unit").'</td><td>';
	if ($_POST["donneesid"]=="ca")	
	{
		print '<input type="radio" name="donneesid" value="ca" id="ca" checked/> <label for="ca">'.$langs->trans("Sales").'</label><br />
			<input type="radio" name="donneesid" value="marge" id="marge" /> <label for="marge">'.$langs->trans("Margin").'</label><br>
			<input type="radio" name="donneesid" value="nb" id="nb" /> <label for="marge">'.$langs->trans("Number").'</label></select></td></tr>';
	}
	elseif ($_POST["donneesid"]=="nb")
	{
				print '<input type="radio" name="donneesid" value="ca" id="ca"> <label for="ca">'.$langs->trans("Sales").'</label><br />
			<input type="radio" name="donneesid" value="marge" id="marge" /> <label for="marge">'.$langs->trans("Margin").'</label><br>
			<input type="radio" name="donneesid" value="nb" id="nb" checked/> <label for="marge">'.$langs->trans("Number").'</label></select></td></tr>';
	}
else
	{
		print '<input type="radio" name="donneesid" value="ca" id="ca"/> <label for="ca">'.$langs->trans("Sales").'</label><br />
			<input type="radio" name="donneesid" value="marge" id="marge" checked/> <label for="marge">'.$langs->trans("Margin").'</label><br>
			<input type="radio" name="donneesid" value="nb" id="nb" /> <label for="marge">'.$langs->trans("Number").'</label></select></td></tr>';
	}	
																				
									
								
	// période
	print '<tr><td>'.$langs->trans("Scale").'</td><td>';
	if ($_POST["periodeid"]==1)
	{
		print'
			<input type="radio" name="periodeid" value=1 id=1 checked/> <label for=1>'.$langs->trans("Month").'</label><br />
			<input type="radio" name="periodeid" value=2 id=2 /> <label for=2></label>'.$langs->trans("Year").'</select></td></tr>';		
	}
	else
	{
		print '
					<input type="radio" name="periodeid" value=1 id=1 /> <label for=1>'.$langs->trans("Month").'</label><br />
			<input type="radio" name="periodeid" value=2 id=2 checked/> <label for=2></label>'.$langs->trans("Year").'</select></td></tr>';
	}

											
																				
																	
	// Commercial
	print '<tr><td>'.$langs->trans("Seller").'</td><td>';
								print '<select class="texte" name="commercialid">';
								print '<OPTION value="0">'.$langs->trans("All").'</OPTION>';
								$resql2=$db->query('select distinct login, firstname, lastname, rowid from '.MAIN_DB_PREFIX.'user order by login asc');
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
												
													if ($_POST["commercialid"] == $obj->rowid)	
												{
													
													echo '<OPTION selected value="'.$obj->rowid.'">'.$obj->firstname.' '.$obj->lastname.'</OPTION>';
												}
												else
												{
													echo '<OPTION value="'.$obj->rowid.'">'.$obj->firstname.' '.$obj->lastname.'</OPTION>';	
												}
											}
											

											
											
											$j++;
										}
									}
								}								
print '</select></td></tr>';
			
			
	// Factureur
	print '<tr><td>'.$langs->trans("InvoicedBy").'</td><td>';
								print '<select class="texte" name="factureurid">';
								print '<OPTION value="0">'.$langs->trans("All").'</OPTION>';
								$resql2=$db->query('select distinct login, firstname, lastname, rowid from '.MAIN_DB_PREFIX.'user order by login asc');
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
												
													if ($_POST["factureurid"] == $obj->rowid)	
												{
													
													echo '<OPTION selected value="'.$obj->rowid.'">'.$obj->firstname.' '.$obj->lastname.'</OPTION>';
												}
												else
												{
													echo '<OPTION value="'.$obj->rowid.'">'.$obj->firstname.' '.$obj->lastname.'</OPTION>';	
												}
											}
											

											
											
											$j++;
										}
									}
								}								
print '</select></td></tr>';
			
			
			
			
			




						

																

	// Client
	print '<tr><td>'.$langs->trans("Customer").'</td><td>';

								print '<select class="field" id="clientid" name="clientid">';
								print '<OPTION value="0">'.$langs->trans("All").'</OPTION>';
								$resql2=$db->query('select distinct nom, rowid, town from '.MAIN_DB_PREFIX.'societe where (client=1 or client=3)  order by nom asc');
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
												
													if ($_POST["clientid"] == $obj->rowid)	
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
								print '</select></td></tr>';														
												 
										
										
										
										
										
										
										
										
										
										
										
										
										
										
								print '</select>';
	print '</td></tr>';
	
	// Marque
	print '<tr><td>'.$langs->trans("Brand").'</td><td>';
								print '<select class="field" name="marqueid" id="marqueid">';
								print '<OPTION value="0">'.$langs->trans("All").'</OPTION>';
								$resql2=$db->query('select marque, rowid from '.MAIN_DB_PREFIX.'product_extrafields group by marque order by marque asc');
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
													if ($_POST["marqueid"] == $obj->marque)	
												{
													
													echo '<OPTION selected value="'.$obj->marque.'">'.$obj->marque.'</OPTION>';
												}
												else
												{
													echo '<OPTION value="'.$obj->marque.'">'.$obj->marque.'</OPTION>';	
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
