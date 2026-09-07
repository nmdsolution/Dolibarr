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

$now=dol_now();



$form = new Form($db);
$formfile = new FormFile($db);


llxHeader("",$langs->trans("BrandsCategoriesDistribution"));

print load_fiche_titre($langs->trans("BrandsCategoriesDistribution"),'','title_accountancy.png');


print '<div class="fichecenter"><div class="fichethirdleft">';

if ($user->rights->statistiques->Ventes->SAVentes)
{


if (GETPOST('anneedeb')>1)
{

	$debut=GETPOST('anneedeb');

}
else
{
	$debut=DATE("Y");

}


	echo $langs->trans("Year").' '.$debut.'<br>';







					

	





				echo '<table><tr style="color:#aaa;"><td style="width:230px;text-align:right;">'.$langs->trans("CategoryBrand").'</td><td style="width:50px;text-align:right;">'.$langs->trans("Qty").'</td><td style="width:150px;text-align:right;">'.$langs->trans("Sells").'</td><td style="width:150px;text-align:right;">'.$langs->trans("Margin").'</td></tr>';
															
				
$resql4=$db->query('SELECT '.MAIN_DB_PREFIX.'categorie.label,'.MAIN_DB_PREFIX.'categorie.rowid, '.MAIN_DB_PREFIX.'categorie.color from '.MAIN_DB_PREFIX.'categorie
													where ('.MAIN_DB_PREFIX.'categorie.fk_parent=0) and (type=0);');								
if ($resql4)
	{
		
		$num4 = $db->num_rows($resql4);
		$m = 0;
		if ($num4)
		{
			while ($m < $num4)
			{
				$obj4 = $db->fetch_object($resql4);
				if ($obj4)
				{
					$totalcat=0;
					echo '<tr style="border:1px solid #aaa;"><td><b>'.$obj4->label.'</b></td>';
													$resqlHC=$db->query('SELECT sum('.MAIN_DB_PREFIX.'facturedet.total_ht) AS totalht, sum('.MAIN_DB_PREFIX.'facturedet.total_ht - ( '.MAIN_DB_PREFIX.'facturedet.buy_price_ht)*'.MAIN_DB_PREFIX.'facturedet.qty ) AS marge, sum('.MAIN_DB_PREFIX.'facturedet.qty) as nombre from '.MAIN_DB_PREFIX.'facturedet
													inner join '.MAIN_DB_PREFIX.'product on '.MAIN_DB_PREFIX.'facturedet.fk_product = '.MAIN_DB_PREFIX.'product.rowid
													inner join '.MAIN_DB_PREFIX.'facture on '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid
													inner join '.MAIN_DB_PREFIX.'categorie_product on '.MAIN_DB_PREFIX.'categorie_product.fk_product = '.MAIN_DB_PREFIX.'product.rowid
													where (YEAR('.MAIN_DB_PREFIX.'facture.datef)="'.($debut).'") and '.MAIN_DB_PREFIX.'categorie_product.fk_categorie= '.$obj4->rowid.';');

													if ($resqlHC)
													{		
												$objHC = $db->fetch_object($resqlHC);
														if (($objHC->totalht)>0)
														{
																echo'<td style="width:50px;text-align:right;">'.round($objHC->nombre,0).'</td><td style="width:50px;text-align:right;">'.round($objHC->totalht,0).' € </td><td style="width:150px;text-align:right;">'. round($objHC->marge,0) .' €</td></tr>';					
																$totalcat=$totalcat+round($objHC->totalht,0);
														}
													}
					
					
	
						
						
						
						
						
						
						
						
													$resql3=$db->query('SELECT sum('.MAIN_DB_PREFIX.'facturedet.total_ht) AS totalht, sum('.MAIN_DB_PREFIX.'facturedet.total_ht - ( '.MAIN_DB_PREFIX.'facturedet.buy_price_ht)*'.MAIN_DB_PREFIX.'facturedet.qty ) AS marge, sum('.MAIN_DB_PREFIX.'facturedet.qty) as nombre from '.MAIN_DB_PREFIX.'facturedet
													inner join '.MAIN_DB_PREFIX.'product on '.MAIN_DB_PREFIX.'facturedet.fk_product = '.MAIN_DB_PREFIX.'product.rowid
													inner join '.MAIN_DB_PREFIX.'facture on '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid
													inner join '.MAIN_DB_PREFIX.'categorie_product on '.MAIN_DB_PREFIX.'categorie_product.fk_product = '.MAIN_DB_PREFIX.'product.rowid
													where (YEAR('.MAIN_DB_PREFIX.'facture.datef)="'.($debut).'") and ('.MAIN_DB_PREFIX.'categorie.fk_parent>0) and '.MAIN_DB_PREFIX.'categorie_product.fk_categorie= '.$obj4->rowid.';');

													if ($resql3)
													{		
												$obj3 = $db->fetch_object($resql3);
														if (($obj3->totalht)>0)
														{
																echo'<tr style="background:#888;"><td colspan=2 style="width:230px;text-align:left;">'.$obj4->label.'</td><td style="width:50px;text-align:right;">'.round($obj3->totalht,0).' € </td><td style="width:150px;text-align:right;">'. round($obj3->marge,0) .' €</td></tr>';					
																$totalcat=$totalcat+round($obj3->totalht,0);
														}
													}
					
						$resql=$db->query('SELECT rowid,label from '.MAIN_DB_PREFIX.'categorie where fk_parent='.$obj4->rowid.' and type=0;');								
						if ($resql)
							{
								$num = $db->num_rows($resql);
								$n = 0;
								if ($num)

								{
									while ($n < $num)
									{
										$obj = $db->fetch_object($resql);
										if ($obj)
										{
													$resql3=$db->query('SELECT sum('.MAIN_DB_PREFIX.'facturedet.total_ht) AS totalht, sum('.MAIN_DB_PREFIX.'facturedet.total_ht - ( '.MAIN_DB_PREFIX.'facturedet.buy_price_ht)*'.MAIN_DB_PREFIX.'facturedet.qty ) AS marge,sum('.MAIN_DB_PREFIX.'facturedet.qty) as nombre from '.MAIN_DB_PREFIX.'facturedet
													inner join '.MAIN_DB_PREFIX.'product on '.MAIN_DB_PREFIX.'facturedet.fk_product = '.MAIN_DB_PREFIX.'product.rowid
													inner join '.MAIN_DB_PREFIX.'facture on '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid
													inner join '.MAIN_DB_PREFIX.'categorie_product on '.MAIN_DB_PREFIX.'categorie_product.fk_product = '.MAIN_DB_PREFIX.'product.rowid
													where (YEAR('.MAIN_DB_PREFIX.'facture.datef)="'.($debut).'") and '.MAIN_DB_PREFIX.'categorie_product.fk_categorie= '.$obj->rowid.';');

													if ($resql3)
													{		
																$obj3 = $db->fetch_object($resql3);
																echo'<tr style="background:#ccc;"><td style="width:250px;text-align:left;text-indent:25px;">'.$obj->label.'</td><td style="width:150px;text-align:right;">'.round($obj3->nombre,0).'</td><td style="width:50px;text-align:right;">'.round($obj3->totalht,0).' €</td><td style="width:50px;text-align:right;">'.round($obj3->marge,0).' € </td></tr>';
																$totalcat=$totalcat+round($obj3->totalht,0);
																			
													}
											
										
											
													$resql2=$db->query('SELECT '.MAIN_DB_PREFIX.'product_extrafields.marque as marque, sum('.MAIN_DB_PREFIX.'facturedet.total_ht) AS totalht, sum('.MAIN_DB_PREFIX.'facturedet.total_ht - ( '.MAIN_DB_PREFIX.'facturedet.buy_price_ht)*'.MAIN_DB_PREFIX.'facturedet.qty ) AS marge, sum('.MAIN_DB_PREFIX.'facturedet.qty) as nombre from '.MAIN_DB_PREFIX.'product_extrafields
																		inner join '.MAIN_DB_PREFIX.'product on '.MAIN_DB_PREFIX.'product_extrafields.fk_object = '.MAIN_DB_PREFIX.'product.rowid
																		inner join '.MAIN_DB_PREFIX.'facturedet on '.MAIN_DB_PREFIX.'facturedet.fk_product = '.MAIN_DB_PREFIX.'product.rowid
																		inner join '.MAIN_DB_PREFIX.'facture on '.MAIN_DB_PREFIX.'facturedet.fk_facture = '.MAIN_DB_PREFIX.'facture.rowid
																		inner join '.MAIN_DB_PREFIX.'categorie_product on '.MAIN_DB_PREFIX.'categorie_product.fk_product = '.MAIN_DB_PREFIX.'product.rowid
																		where (YEAR('.MAIN_DB_PREFIX.'facture.datef)="'.($debut).'") and '.MAIN_DB_PREFIX.'categorie_product.fk_categorie= '.$obj->rowid.' group by marque;');

													if ($resql2)
													{														
														$num2 = $db->num_rows($resql2);

														$o=0;
														if ($num2)
														{
															
															while ($o < $num2)
															{
																$obj2 = $db->fetch_object($resql2);
																if ($obj2)
																{
																	if ($obj2->marque)
																	{
																		echo '<tr><td style="width:180px;text-align:left;text-indent:50px;">'. $obj2->marque .'</td><td style="width:150px;text-align:right;">'. round($obj2->nombre) .'</td><td style="width:150px;text-align:right;">'. round($obj2->totalht,0) .' €</td><td style="width:150px;text-align:right;">'. round($obj2->marge,0) .' €</td></tr>';
																	}
																}
															$o++;
															}
															
														}																				
													}
										}
										$n++;
									}
								}
							}
						echo '<tr><td>Total</td><td></td><td style="width:150px;text-align:right;"><b>'.$totalcat.' €</b></td></tr>';
						
				}
				$m++;
			}
		}
	}echo '</table>';


























	




				
					
					
		
					//if (empty($socid))
//{
	// Show filter box
	print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="mode" value="'.$mode.'">';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre"><td class="liste_titre" colspan="2">'.$langs->trans("Filter").'</td></tr>';

								

								

			
											
												 
										
								print '</select>';
	print '</td></tr>';
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
