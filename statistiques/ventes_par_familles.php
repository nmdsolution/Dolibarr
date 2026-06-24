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


llxHeader("",$langs->trans("ProductsServicesCategorySales"));

print load_fiche_titre($langs->trans("ProductsServicesCategorySales"),'','title_accountancy.png');


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



								




















													$resqmax=$db->query('SELECT sum(llx_facturedet.total_ht) as total, YEAR(llx_facture.datef) as annee, month(llx_facture.datef) as mois from llx_facturedet inner join llx_facture on llx_facture.rowid = llx_facturedet.fk_facture inner JOIN llx_societe on llx_facture.fk_soc= llx_societe.rowid where (YEAR(llx_facture.datef)<="'.$debut.'") group by annee, mois ,llx_societe.fk_typent;');

													
												if ($resqmax)
												{
													$nummax = $db->num_rows($resqlmax);

													$b = 0;
													if ($nummax)
													{
														
														while ($b < $nummax)
														{
															$objmax = $db->fetch_object($resqmax);
															if ($objmax)
															{

																if ($objmax->total>$max)
																{
																	$max=$objmax->total;
																}		
																
																
															}
															$b++;
														}
													}
												}

include ("calcul_echelle.php");		
										if (GETPOST('zoom')>1)

					{

						$premiere_grad=$premiere_grad/GETPOST('zoom');

					}
					
					$coef=50/$premiere_grad;





					$largeur=67;
					





?>

<canvas id="canvas1" width="1000" height="500">
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
	
	   		ctx.shadowOffsetX = 1;
		ctx.shadowOffsetY = 1;
		ctx.shadowBlur = 2;
		ctx.shadowColor   = 'rgba(100, 100, 100, 0.5)';
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

function draw2(cat,y,couleur)
{
	var canvas = document.getElementById("canvas1"); 
	var ctx = canvas.getContext("2d");
	ctx.lineWidth="2"; 


			
					ctx.fill();
					ctx.font="12px Arial";
					ctx.fillStyle = couleur;
	ctx.fillText(cat, 910,y);
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
window.onload=draw3(<?php echo $premiere_grad;?>,450,67);	

<?php















	
				
	
					
$resql=$db->query('SELECT monthname( llx_facture.datef ) AS moisnom, month(llx_facture.datef) as mois, YEAR( llx_facture.datef ) AS annee FROM llx_facture 
where (YEAR(llx_facture.datef)<="'.$debut.'")
GROUP BY annee, mois,moisnom ORDER by annee DESC, mois DESC limit 0,13;');
				

					$couleur =["#0000ff","#BF3030","#1FA055","#FF7F00","#8C008c","#C8ADF1","#FEE347","#606060","#dddddd","#BBD2E1","#FF69B4","#77b5fe","#8C4510","#56739a","#B666D2","#008284"];
				
if ($resql)
{
	$num = $db->num_rows($resql);
$array_mois = array("Janvier", "Février", "Mars", "Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre");				

	$i = 0;
	if ($num)
	{
		while ($i < $num)
		{
			$obj = $db->fetch_object($resql);
			if ($obj)
				
				$mois=$array_mois[($obj->mois)-1];
			{
				//echo $obj->mois.' '.$obj->annee.'<br>';
																?>
													window.onload=drawx(<?php echo $i;?>,"<?php echo $mois;?>",<?php echo $largeur;?>); 
												<?php
				
				$resq2=$db->query('SELECT * FROM llx_categorie where type=0 ;');
				if ($resq2)
				{
					$num2 = $db->num_rows($resql2);

					$j = 0;
					if ($num2)
					{
						while ($j < $num2)
						{
							$obj2 = $db->fetch_object($resq2);
							if ($obj2)
							{
								//echo '&nbsp&nbsp&nbsp&nbsp'.$obj2->libelle.':';
								
													$resq3=$db->query('SELECT sum( llx_facturedet.total_ht) AS totalht, llx_categorie.color as color
													FROM llx_facture INNER JOIN llx_facturedet ON llx_facturedet.fk_facture = llx_facture.rowid 
													INNER JOIN llx_product ON llx_product.rowid=llx_facturedet.fk_product
													left join llx_categorie_product on llx_categorie_product.fk_product=llx_facturedet.fk_product
													inner join llx_categorie on llx_categorie.rowid = llx_categorie_product.fk_categorie
													where (YEAR(llx_facture.datef)="'.$obj->annee.'") and (month(llx_facture.datef)="'.$obj->mois.'") and ((llx_categorie.fk_parent>=0) and (llx_categorie.rowid='.$obj2->rowid.'));');
													
													
													

													
												if ($resq3)
												{
													$num3 = $db->num_rows($resql3);

													$k = 0;
													if ($num3)
													{
														while ($k < $num3)
														{
															$obj3 = $db->fetch_object($resq3);
															if ($obj3)
															{	
																	$resql6=$db->query('SELECT sum( llx_facturedet.total_ht) AS totalht FROM llx_facturedet INNER JOIN llx_facture ON llx_facturedet.fk_facture = llx_facture.rowid INNER JOIN llx_product ON llx_product.rowid=llx_facturedet.fk_product INNER JOIN llx_categorie_product ON llx_categorie_product.fk_product = llx_product.rowid INNER JOIN llx_categorie on llx_categorie.rowid = llx_categorie_product.fk_categorie where (YEAR(llx_facture.datef)="'.$obj->annee.'") and (month(llx_facture.datef)="'.$obj->mois.'") and (llx_categorie.rowid='.$obj2->rowid.' or llx_categorie.fk_parent='.$obj2->rowid.');');
																	
																	$obj6 = $db->fetch_object($resq4);
																	if ($obj6)
																	{
																		$mt_fact=$obj6->totalht;
																	}
																	
																	
																
																if ($mt_fact>$max)
																{
																	$max=round($mt_fact,0);
																}
																//echo '&nbsp&nbsp&nbsp&nbsp'.$mt_fact.'<br>';
																
																		if ($i<1)
																		{
																			$valx1=855-$i*$largeur;
																		}
																		else
																		{
																			$valx1=805-$i*$largeur;
																			$valy1=450-$montant_last[$j]*$coef;
																			$valx2=$valx1-$largeur;
																			$valy2=450-(($mt_fact)*$coef);
																			
																			if ($_POST[$obj2->rowid]==1)
																			{?>
																			window.onload=draw(<?php echo $valx1;?>,<?php echo $valy1;?>,<?php echo $valx2;?>,<?php echo $valy2;?>,"#<?php echo $obj3->color;?>"); 
																			<?php
																			}
																		}		
																		$montant_last[$j]=$mt_fact;	
																																				$num_coul_last=$num_coul;
														$valx2=735-$i*$largeur;
														$valy2=448-(($mt_fact)*$coef);
														if ($_POST[$obj2->rowid]==1)
														{			
														?>
															window.onload=drawpoint(<?php echo $valx2;?>,<?php echo $valy2;?>,"#<?php echo $obj3->color;?>");
														<?php														
														}
																		
															}
															$k++;
														}
													}
												}
		



				

								
							}
										
							$j++;

							
						}
					}
				}

				
			}
			$i++;
		}
	}
}

// légende
								$resql5=$db->query("select distinct llx_categorie.label, llx_categorie.rowid as rowid, llx_categorie.fk_parent as fk_parent,llx_categorie.color as color from llx_categorie where fk_parent >=0 order by fk_parent asc,label ASC ");
								if ($resql5)
								{
									$num5 = $db->num_rows($resql5);
									$m = 0;
									$n=0;
									if ($num5)
									{
										while ($m < $num5)
										{
											$obj5 = $db->fetch_object($resq5);
											
											
												if ($_POST[$obj5->rowid]==1)
												{
													$n=$n+1; //(on incrémente que si la ligne doit être affichée afin de trouver l'ordonnée)
													$ordonnee=$n*20;
												
												?>window.onload=legende("<?php echo $obj5->label;?>",<?php echo ($n*20);?>,"#<?php echo $obj5->color;?>");<?php
												}
											
											$m++;
										}
									}
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

	print '<tr><td>'.$langs->trans("Zoom").'</td><td>';



	



	print '<input type="hidden" name="zoom">';

								echo '<select class="field" name="zoom" id="zoom">';

								if (GETPOST('zoom')==1)

								{

									echo '<OPTION selected id="1">1</OPTION>';

								}

								else

								{

									echo '<OPTION id="1">1</OPTION>';

								}									

								if (GETPOST('zoom')==2)

								{

									echo '<OPTION selected id="2">2</OPTION>';

								}

								else

								{

									echo '<OPTION id="2">2</OPTION>';

								}

								if (GETPOST('zoom')==4)

								{

									echo '<OPTION selected id="4">4</OPTION>';

								}

								else

								{

									echo '<OPTION id="4">4</OPTION>';

								}								

								if (GETPOST('zoom')==8)

								{

									echo '<OPTION selected id="8">8</OPTION>';

								}

								else

								{

									echo '<OPTION id="8">8</OPTION>';

								}	

								if (GETPOST('zoom')==16)

								{

									echo '<OPTION selected id="16">16</OPTION>';

								}

								else

								{

									echo '<OPTION id="16">16</OPTION>';

								}													

										

								echo '</select>';

								

	

								

								



	

	print '</td></tr>';								
	// Familles
	print '<tr><td>'.$langs->trans("ProductCategory").'</td><td>';

								
								$resql4=$db->query("select distinct llx_categorie.label, llx_categorie.rowid as row_id, llx_categorie.fk_parent as fk_parent from llx_categorie where type=0 and fk_parent >=0 order by fk_parent asc,label asc");
								if ($resql4)
								{
									$num4 = $db->num_rows($resql4);
									$l = 0;
									if ($num4)
									{
										while ($l < $num4)
										{
											$obj4 = $db->fetch_object($resq4);
											if ($obj4)
											{
					
												if ($_POST[$obj4->row_id]==1)
												{
													if ($obj4->fk_parent>0)
													{
														print '<input type="checkbox" checked name="'.$obj4->row_id.'" value=1>'.$obj4->label.'</br>';
													}
													else
													{
														print '<input type="checkbox" checked name="'.$obj4->row_id.'" value=1><b>'.$obj4->label.'</b></br>';
													}
												}
												else
												{
													if ($obj4->fk_parent>0)
													{
														print '<input type="checkbox" name="'.$obj4->row_id.'" value=1>'.$obj4->label.'</br>';
													}
													else
													{
														print '<input type="checkbox" name="'.$obj4->row_id.'" value=1><b>'.$obj4->label.'</b></br>';
													}
												
												}
											}
											$l++;
										}
									}
								}
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
								</select></td></tr>
									
								
								<?php
	
	
								
						

	

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
