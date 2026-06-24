<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 */

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

// Récupération des filtres
$date_start = GETPOST('date_start', 'alpha');
$date_end = GETPOST('date_end', 'alpha');
$warehouse_id = GETPOST('warehouse_id', 'int');
$category_id = GETPOST('category_id', 'int');
$supplier_id = GETPOST('supplier_id', 'int');
$product_id = GETPOST('product_id', 'int'); // NOUVEAU: Filtre par produit
$display_type = GETPOST('display_type', 'alpha'); // NOUVEAU: Type d'affichage (value ou quantity)

// Type d'affichage par défaut
if (empty($display_type)) {
    $display_type = 'value'; // 'value' ou 'quantity'
}

// Dates par défaut si non définies
if (empty($date_start)) {
    $date_start = date('Y-m-d', strtotime('-18 months'));
}
if (empty($date_end)) {
    $date_end = date('Y-m-d');
}

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

/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("",$langs->trans("Stock"));

print load_fiche_titre($langs->trans("Stock"),'','title_accountancy.png');

print '<div class="fichecenter"><div class="fichethirdleft">';

if ($user->rights->statistiques->Stock->SAStock)
{	
    echo '<section style="position:relative; display:inline-block;height:600px;width:1000px;background-color:#fff;">';

    // Déterminer la période (jour ou mois)
    $period_type = ($_POST["donneesid"] == "mois") ? "month" : "day";
    
    // Construction des conditions WHERE pour les filtres
    $where_conditions = array();
    $join_conditions = array();
    
    // Filtre par entrepôt
    if (!empty($warehouse_id)) {
        $where_conditions[] = "ps.fk_entrepot = ".$warehouse_id;
        $where_conditions[] = "sm.fk_entrepot = ".$warehouse_id;
    }
    
    // Filtre par produit - NOUVEAU
    if (!empty($product_id)) {
        $where_conditions[] = "p.rowid = ".$product_id;
    }
    
    // Filtre par catégorie
    if (!empty($category_id)) {
        $join_conditions[] = "LEFT JOIN ".MAIN_DB_PREFIX."categorie_product cp ON cp.fk_product = p.rowid";
        $where_conditions[] = "cp.fk_categorie = ".$category_id;
    }
    
    // Filtre par fournisseur
    if (!empty($supplier_id)) {
        $join_conditions[] = "LEFT JOIN ".MAIN_DB_PREFIX."product_fournisseur pf ON pf.fk_product = p.rowid";
        $where_conditions[] = "pf.fk_soc = ".$supplier_id;
    }
    
    $join_sql = !empty($join_conditions) ? implode(' ', $join_conditions) : '';
    $where_sql = !empty($where_conditions) ? 'AND ('.implode(' AND ', $where_conditions).')' : '';
    
    // Calcul du stock actuel avec choix entre valeur et quantité - MODIFIÉ
    $stock_actuel = 0;
    $unit_label = ($display_type == 'quantity') ? '' : $conf->global->MAIN_MONNAIE;
    
    if ($display_type == 'quantity') {
        // Affichage en QUANTITÉ
        $sql_stock = 'SELECT SUM(ps.reel) AS total
            FROM '.MAIN_DB_PREFIX.'product_stock ps
            INNER JOIN '.MAIN_DB_PREFIX.'product p ON p.rowid = ps.fk_product
            '.$join_sql.'
            WHERE ps.reel IS NOT NULL '.$where_sql;
    } else {
        // Affichage en VALEUR (comme avant)
        $sql_stock = 'SELECT SUM(p.pmp * ps.reel) AS total
            FROM '.MAIN_DB_PREFIX.'product_stock ps
            INNER JOIN '.MAIN_DB_PREFIX.'product p ON p.rowid = ps.fk_product
            '.$join_sql.'
            WHERE ps.reel IS NOT NULL AND p.pmp IS NOT NULL '.$where_sql;
    }
    
    $resql_stock = $db->query($sql_stock);
    
    if ($resql_stock) {
        $obj_stock = $db->fetch_object($resql_stock);
        if ($obj_stock) {
            $stock_actuel = $obj_stock->total;
        }
    }
    
    // Requête optimisée pour obtenir l'historique - MODIFIÉ
    if ($period_type == "month") {
        echo 'Par mois';
        if ($display_type == 'quantity') {
            $sql_periods = 'SELECT 
                YEAR(sm.datem) as annee, 
                MONTH(sm.datem) as mois, 
                MONTHNAME(sm.datem) as moisnom,
                DATE_FORMAT(sm.datem, "%Y-%m-01") as date_comp,
                SUM(sm.value * -1) as variation_stock
            FROM '.MAIN_DB_PREFIX.'stock_mouvement sm
            INNER JOIN '.MAIN_DB_PREFIX.'product p ON p.rowid = sm.fk_product
            '.$join_sql.'
            WHERE sm.datem BETWEEN "'.$date_start.'" AND "'.$date_end.'" '.$where_sql.'
            GROUP BY YEAR(sm.datem), MONTH(sm.datem), MONTHNAME(sm.datem), DATE_FORMAT(sm.datem, "%Y-%m-01")
            ORDER BY annee DESC, mois DESC
            LIMIT 18';
        } else {
            $sql_periods = 'SELECT 
                YEAR(sm.datem) as annee, 
                MONTH(sm.datem) as mois, 
                MONTHNAME(sm.datem) as moisnom,
                DATE_FORMAT(sm.datem, "%Y-%m-01") as date_comp,
                SUM(p.pmp * sm.value * -1) as variation_stock
            FROM '.MAIN_DB_PREFIX.'stock_mouvement sm
            INNER JOIN '.MAIN_DB_PREFIX.'product p ON p.rowid = sm.fk_product
            '.$join_sql.'
            WHERE sm.datem BETWEEN "'.$date_start.'" AND "'.$date_end.'" '.$where_sql.'
            GROUP BY YEAR(sm.datem), MONTH(sm.datem), MONTHNAME(sm.datem), DATE_FORMAT(sm.datem, "%Y-%m-01")
            ORDER BY annee DESC, mois DESC
            LIMIT 18';
        }
    } else {
        echo 'Par jour';
        if ($display_type == 'quantity') {
            $sql_periods = 'SELECT 
                YEAR(sm.datem) as annee, 
                MONTH(sm.datem) as mois, 
                DAY(sm.datem) as jour,
                WEEK(sm.datem) as semaine, 
                MONTHNAME(sm.datem) as moisnom,
                DATE(sm.datem) as date_comp,
                SUM(sm.value * -1) as variation_stock
            FROM '.MAIN_DB_PREFIX.'stock_mouvement sm
            INNER JOIN '.MAIN_DB_PREFIX.'product p ON p.rowid = sm.fk_product
            '.$join_sql.'
            WHERE sm.datem BETWEEN "'.$date_start.'" AND "'.$date_end.'" '.$where_sql.'
            GROUP BY DATE(sm.datem)
            ORDER BY sm.datem DESC
            LIMIT 18';
        } else {
            $sql_periods = 'SELECT 
                YEAR(sm.datem) as annee, 
                MONTH(sm.datem) as mois, 
                DAY(sm.datem) as jour,
                WEEK(sm.datem) as semaine, 
                MONTHNAME(sm.datem) as moisnom,
                DATE(sm.datem) as date_comp,
                SUM(p.pmp * sm.value * -1) as variation_stock
            FROM '.MAIN_DB_PREFIX.'stock_mouvement sm
            INNER JOIN '.MAIN_DB_PREFIX.'product p ON p.rowid = sm.fk_product
            '.$join_sql.'
            WHERE sm.datem BETWEEN "'.$date_start.'" AND "'.$date_end.'" '.$where_sql.'
            GROUP BY DATE(sm.datem)
            ORDER BY sm.datem DESC
            LIMIT 18';
        }
    }
    
    // Récupération des données pour le calcul de l'échelle
    $stock_values = array();
    $resql_periods = $db->query($sql_periods);
    
    $maxi = $stock_actuel; // Le stock actuel est déjà le maximum de base
    $current_stock = $stock_actuel;
    
    if ($resql_periods) {
        $num = $db->num_rows($resql_periods);
        $periods_data = array();
        
        for ($i = 0; $i < $num; $i++) {
            $obj = $db->fetch_object($resql_periods);
            if ($obj) {
                $current_stock += $obj->variation_stock;
                $periods_data[] = array(
                    'stock_value' => $current_stock,
                    'variation' => $obj->variation_stock,
                    'date_comp' => $obj->date_comp,
                    'annee' => $obj->annee,
                    'mois' => $obj->mois,
                    'jour' => isset($obj->jour) ? $obj->jour : 1,
                    'moisnom' => isset($obj->moisnom) ? $obj->moisnom : ''
                );
                
                if ($current_stock > $maxi) {
                    $maxi = $current_stock;
                }
            }
        }
        
        // Inverser l'ordre pour afficher du plus ancien au plus récent
        $periods_data = array_reverse($periods_data);
    }
    
    // Calcul de l'échelle avec protection contre division par zéro
    if ($stock_actuel > 400) {
        $premiere_grad = round(($stock_actuel/4), -2);				
    } else {
        $premiere_grad = round(($maxi/5), -2);	
    }
    
    // Protection contre division par zéro
    if ($premiere_grad <= 0) {
        if ($maxi > 0) {
            $premiere_grad = max(1, round($maxi/5, 0));
        } else {
            $premiere_grad = 100; // Valeur par défaut
        }
    }
    
    $maxcoef = 50/$premiere_grad;
    
    // Affichage du graphique
    // Indicateur du stock actuel - MODIFIÉ
    echo '<div class="right" style="position: absolute; top: 10px; right: 10px;">';
    $display_label = ($display_type == 'quantity') ? 'Stock actuel (Qté)' : 'Stock actuel (Valeur)';
    echo '<span class="badge badge-status4 badge-status">'.$langs->trans($display_label).': '.number_format($stock_actuel, 0, ',', ' ').' '.$unit_label.'</span>';
    echo '</div>';
    echo '<div style="display:inline-block;position:absolute; background-color:#222; width:3px; height:350px; left:70px;top:50px; margin:0px;"></div>';
    
    $largeur = (800/20);
    
    // Affichage des barres historiques
    if (!empty($periods_data)) {
        foreach ($periods_data as $j => $period) {
            $stock_instant = max(0, $period['stock_value']); // S'assurer que la valeur n'est pas négative
            $hauteur_barre = $stock_instant * $maxcoef;
            
            // Protection contre les valeurs aberrantes
            if ($hauteur_barre > 350) {
                $hauteur_barre = 350;
            }
            
            $decimals = ($display_type == 'quantity') ? 0 : 2;
            
            echo '<div class="barreN" style="display: inline-block; position: absolute; left: '.(808-($largeur*$j)).'px; width:'.($largeur-5).'px; bottom:+200px; height:'.$hauteur_barre.'px; border-radius:3px;background-color:#1E7FCB;z-index:1;"><div class="bulle">'.number_format($stock_instant, $decimals, ',', ' ').' '.$unit_label.'</div></div>';
            echo '<div style="display: inline-block; position: absolute;  transform: rotate(315deg); left: '.(829-($largeur*$j)).'px; width:'.($largeur+35).'px; bottom:120px;  height:50px;text-align:center;color:#666;font-size:10px;">'.$period['jour'].'/'.$period['mois'].'/'.$period['annee'].'</div>';
            echo '<div class="valeurhistogramme" style="display: inline-block; position: absolute; transform: rotate(270deg);left: '.(810-($largeur*$j)).'px; width:'.($largeur-5).'px; bottom:+220px; height:20px;text-align:center;z-index:3">'.number_format($stock_instant, 0, ',', ' ').'</div>';
        }
    }
    
    // Affichage de la dernière barre: le stock actuel
    $stock_actuel_secure = max(0, $stock_actuel);
    $hauteur_stock_actuel = $stock_actuel_secure * $maxcoef;
    if ($hauteur_stock_actuel > 350) {
        $hauteur_stock_actuel = 350;
    }
    
    $decimals = ($display_type == 'quantity') ? 0 : 2;
    
    echo '<div class="barreN" style="display: inline-block; position: absolute; left: '.(848).'px; width:'.($largeur-5).'px; bottom:+200px; height:'.$hauteur_stock_actuel.'px; border-radius:3px;background-color:#1E7FCB;z-index:1;"><div class="bulle">'.number_format($stock_actuel_secure, $decimals, ',', ' ').' '.$unit_label.'</div></div>';
    echo '<div class="valeurhistogramme" style="display: inline-block; position: absolute; transform: rotate(270deg);left: '.(850).'px; width:'.($largeur-5).'px; bottom:+220px; height:20px;text-align:center;z-index:3">'.number_format($stock_actuel_secure, 0, ',', ' ').'</div>';
    
    // Axes et quadrillage
    echo '<div style="display:block;position:absolute; background-color:#222; width:830px; height:3px;left:70px; top:400px;bottom:0px; margin:0px;"></div>';
    
    // Quadrillage et échelle des ordonnées
    for ($l = 1; $l <= 6; $l++) {
        echo '<div style="display:block;position:absolute; background-color:#888; width:830px; height:1px;left:70px; top:'.(((7-$l)*50)+50).'px;bottom:0px; margin:0px;"></div>';
        echo '<div style="display:block;position:absolute; width:50px; text-align:right;height:30px;left:15px; top:'.(((7-$l)*50)+43).'px;bottom:0px; margin:0px; z-index:0;color:#888;">'.number_format(($premiere_grad*$l), 0, ',', ' ').'</div>';
    }

    echo '</section>';
    
    // Formulaire de filtre étendu - MODIFIÉ
    print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
    print '<input type="hidden" name="mode" value="'.$mode.'">';
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre"><td class="liste_titre" colspan="2">'.$langs->trans("Filter").'</td></tr>';
    
    // NOUVEAU: Type d'affichage (Valeur ou Quantité)
    print '<tr><td><strong>'.$langs->trans("DisplayType").'</strong></td><td>';
    print '<input type="radio" name="display_type" value="value" id="display_value" '.($display_type == 'value' ? 'checked' : '').'/> <label for="display_value">'.$langs->trans("Value").' ('.$conf->global->MAIN_MONNAIE.')</label><br />';
    print '<input type="radio" name="display_type" value="quantity" id="display_quantity" '.($display_type == 'quantity' ? 'checked' : '').'/> <label for="display_quantity">'.$langs->trans("Quantity").'</label>';
    print '</td></tr>';
    
    // NOUVEAU: Filtre par produit
    print '<tr><td>'.$langs->trans("Product").'</td><td>';
    print '<select name="product_id" class="flat">';
    print '<option value="">'.$langs->trans("AllProducts").'</option>';
    
    $sql_products = "SELECT rowid, ref, label FROM ".MAIN_DB_PREFIX."product WHERE entity IN (".getEntity('product').") ORDER BY ref";
    $resql_products = $db->query($sql_products);
    if ($resql_products) {
        while ($obj_prod = $db->fetch_object($resql_products)) {
            $selected = ($product_id == $obj_prod->rowid) ? 'selected' : '';
            print '<option value="'.$obj_prod->rowid.'" '.$selected.'>'.$obj_prod->ref.' - '.$obj_prod->label.'</option>';
        }
    }
    print '</select></td></tr>';
    
    // Sélecteur de période
    print '<tr><td>'.$langs->trans("DateRange").'</td><td>';
    print '<input type="date" name="date_start" value="'.$date_start.'" /> ';
    print $langs->trans("To").' ';
    print '<input type="date" name="date_end" value="'.$date_end.'" />';
    print '</td></tr>';
    
    // Filtre par entrepôt
    print '<tr><td>'.$langs->trans("Warehouse").'</td><td>';
    print '<select name="warehouse_id" class="flat">';
    print '<option value="">'.$langs->trans("AllWarehouses").'</option>';
    
    $sql_warehouses = "SELECT rowid, label FROM ".MAIN_DB_PREFIX."entrepot WHERE entity IN (".getEntity('stock').") ORDER BY label";
    $resql_warehouses = $db->query($sql_warehouses);
    if ($resql_warehouses) {
        while ($obj_wh = $db->fetch_object($resql_warehouses)) {
            $selected = ($warehouse_id == $obj_wh->rowid) ? 'selected' : '';
            print '<option value="'.$obj_wh->rowid.'" '.$selected.'>'.$obj_wh->label.'</option>';
        }
    }
    print '</select></td></tr>';
    
    // Filtre par catégorie
    print '<tr><td>'.$langs->trans("Category").'</td><td>';
    print '<select name="category_id" class="flat">';
    print '<option value="">'.$langs->trans("AllCategories").'</option>';
    
    $sql_categories = "SELECT rowid, label FROM ".MAIN_DB_PREFIX."categorie WHERE type = 0 AND entity IN (".getEntity('category').") ORDER BY label";
    $resql_categories = $db->query($sql_categories);
    if ($resql_categories) {
        while ($obj_cat = $db->fetch_object($resql_categories)) {
            $selected = ($category_id == $obj_cat->rowid) ? 'selected' : '';
            print '<option value="'.$obj_cat->rowid.'" '.$selected.'>'.$obj_cat->label.'</option>';
        }
    }
    print '</select></td></tr>';
    
    // Filtre par fournisseur
    print '<tr><td>'.$langs->trans("Supplier").'</td><td>';
    print '<select name="supplier_id" class="flat">';
    print '<option value="">'.$langs->trans("AllSuppliers").'</option>';
    
    $sql_suppliers = "SELECT s.rowid, s.nom FROM ".MAIN_DB_PREFIX."societe s 
                     WHERE s.fournisseur = 1 AND s.entity IN (".getEntity('societe').") 
                     ORDER BY s.nom";
    $resql_suppliers = $db->query($sql_suppliers);
    if ($resql_suppliers) {
        while ($obj_sup = $db->fetch_object($resql_suppliers)) {
            $selected = ($supplier_id == $obj_sup->rowid) ? 'selected' : '';
            print '<option value="'.$obj_sup->rowid.'" '.$selected.'>'.$obj_sup->nom.'</option>';
        }
    }
    print '</select></td></tr>';
    
    // Type de données (jour/mois)
    print '<tr><td>'.$langs->trans("Scale").'</td><td>';
    if ($_POST["donneesid"]=="mois") {
        print '<input type="radio" name="donneesid" value="jour" id="jour"/> <label for="jour">'.$langs->trans("Day").'</label><br />
            <input type="radio" name="donneesid" value="mois" id="mois" checked/> <label for="mois">'.$langs->trans("Month").'</label></td></tr>';
    } else {
        print '<input type="radio" name="donneesid" value="jour" id="jour" checked/> <label for="jour">'.$langs->trans("Day").'</label><br />
            <input type="radio" name="donneesid" value="mois" id="mois" /> <label for="mois">'.$langs->trans("Month").'</label></td></tr>';
    }
    
    print '<tr><td align="center" colspan="2"><input type="submit" name="submit" class="button" value="'.$langs->trans("Refresh").'"></td></tr>';
    print '</table>';
    print '</form>';
    
    // Affichage des filtres actifs - MODIFIÉ
    if (!empty($product_id) || !empty($warehouse_id) || !empty($category_id) || !empty($supplier_id) || $date_start != date('Y-m-d', strtotime('-18 months')) || $date_end != date('Y-m-d')) {
        print '<div class="info">';
        print '<strong>'.$langs->trans("ActiveFilters").':</strong><br />';
        
        if ($display_type == 'quantity') {
            print $langs->trans("DisplayType").': '.$langs->trans("Quantity").'<br />';
        } else {
            print $langs->trans("DisplayType").': '.$langs->trans("Value").'<br />';
        }
        
        if (!empty($product_id)) {
            $sql_prod_name = "SELECT ref, label FROM ".MAIN_DB_PREFIX."product WHERE rowid = ".$product_id;
            $res_prod = $db->query($sql_prod_name);
            if ($res_prod) {
                $prod_obj = $db->fetch_object($res_prod);
                print $langs->trans("Product").': '.$prod_obj->ref.' - '.$prod_obj->label.'<br />';
            }
        }
        
        if (!empty($warehouse_id)) {
            $sql_wh_name = "SELECT label FROM ".MAIN_DB_PREFIX."entrepot WHERE rowid = ".$warehouse_id;
            $res_wh = $db->query($sql_wh_name);
            if ($res_wh) {
                $wh_obj = $db->fetch_object($res_wh);
                print $langs->trans("Warehouse").': '.$wh_obj->label.'<br />';
            }
        }
        
        if (!empty($category_id)) {
            $sql_cat_name = "SELECT label FROM ".MAIN_DB_PREFIX."categorie WHERE rowid = ".$category_id;
            $res_cat = $db->query($sql_cat_name);
            if ($res_cat) {
                $cat_obj = $db->fetch_object($res_cat);
                print $langs->trans("Category").': '.$cat_obj->label.'<br />';
            }
        }
        
        if (!empty($supplier_id)) {
            $sql_sup_name = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE rowid = ".$supplier_id;
            $res_sup = $db->query($sql_sup_name);
            if ($res_sup) {
                $sup_obj = $db->fetch_object($res_sup);
                print $langs->trans("Supplier").': '.$sup_obj->nom.'<br />';
            }
        }
        
        print $langs->trans("Period").': '.dol_print_date(strtotime($date_start), 'day').' '.$langs->trans("To").' '.dol_print_date(strtotime($date_end), 'day');
        print '</div>';
    }
    
    print '<br><br>';
}
else
{
    echo $langs->trans("Accès non autorisé.");
}
    
llxFooter();
$db->close();
?>