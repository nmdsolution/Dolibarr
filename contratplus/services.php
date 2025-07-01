<?php
/* Copyright (C) 2017-2018	Eric GROULT			    <eric@code42.fr>
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
 *        \file       htdocs/contrat/services.php
 *      \ingroup    contrat
 *        \brief      Page to list services in contracts
 */

$res = 0;
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";         // to work if your module directory is into dolibarr root htdocs directory
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";       // to work if your module directory is into a subdir of root htdocs directory
require_once DOL_DOCUMENT_ROOT . "/contrat/class/contrat.class.php";
require_once DOL_DOCUMENT_ROOT . "/product/class/product.class.php";
require_once DOL_DOCUMENT_ROOT . "/societe/class/societe.class.php";

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT . "/cron/class/cronjob.class.php";
dol_include_once('/contratplus/core/function.php');
dol_include_once('/contratplus/core/automatefunction.php');
dol_include_once('/contratplus/lib/contratplus.lib.php');

$langs->loadLangs(array('products', 'contracts', 'companies'));

$object = new ContratLigne($db);
$extrafields = new ExtraFields($db);
$extralabels = $extrafields->fetch_name_optionals_label('contratdet');
$search_array_options=$extrafields->getOptionalsFromPost($object->table_element,'','search_');

$mode = GETPOST("mode");
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
$page = GETPOST("page", 'int');
if (empty($page) || $page == -1) {
    $page = 0;
}
$limit = GETPOST('limit') ? GETPOST('limit', 'int') : $conf->liste_limit;
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

$ctotalprice = 0;
$ctotallines = 0;
$totalTVA = 0;
$cqty = 0;
$stotalprice = 0;
$stotallines = 0;
$sqty = 0;

if ($conf->global->CONTRATPLUS_SUPPLIER_CONTRACT == 1) {
    if (!$sortfield) $sortfield = "ce.supplier_contract";
    if (!$sortorder) $sortorder = "DESC";
} else {
    if (!$sortfield) $sortfield = "c.rowid";
    if (!$sortorder) $sortorder = "ASC";
}

$filter = GETPOST("filter");
$search_name = GETPOST("search_name");
$search_contract = GETPOST("search_contract");
$search_service = GETPOST("search_service");
$search_status = GETPOST("search_status", "alpha");
$search_qty = GETPOST("search_qty");
$search_price = GETPOST("search_price");
$statut = GETPOST('statut') ? GETPOST('statut') : 1;
$search_product_category = GETPOST('search_product_category', 'int');
$socid = GETPOST('socid', 'int');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'contractservicelist' . $mode;

$opouvertureprevuemonth = (GETPOST('dateouvertureprevuemonth') != '00' ? GETPOST('dateouvertureprevuemonth') : '00');
$opouvertureprevueday = (GETPOST('dateouvertureprevueday') != '00' ? GETPOST('dateouvertureprevueday') : '00');
$opouvertureprevueyear = (GETPOST('dateouvertureprevueyear') != '00' ? GETPOST('dateouvertureprevueyear') : '00');
$filter_opouvertureprevue = GETPOST('filter_opouvertureprevue');
$filter_dateouvertureprevue = $opouvertureprevueyear . '-' . sprintf("%02d", $opouvertureprevuemonth) . '-' . sprintf("%02d", $opouvertureprevueday);

$options_serv_duree = GETPOST('options_serv_duree');

$opclotureprevuemonth = (GETPOST('dateclotureprevuemonth') != '00' ? GETPOST('dateclotureprevuemonth') : '00');
$opclotureprevueday = (GETPOST('dateclotureprevueday') != '00' ? GETPOST('dateclotureprevueday') : '00');
$opclotureprevueyear = (GETPOST('dateclotureprevueyear') != '00' ? GETPOST('dateclotureprevueyear') : '00');
$filter_opclotureprevue = GETPOST('filter_opclotureprevue');
$filter_dateclotureprevue = $opclotureprevueyear . '-' . sprintf("%02d", $opclotureprevuemonth) . '-' . sprintf("%02d", $opclotureprevueday);

$opcloturemonth = GETPOST('opcloturemonth');
$opclotureday = GETPOST('opclotureday');
$opclotureyear = GETPOST('opclotureyear');
$filter_opdateEnd = GETPOST('filter_opdateEnd');
$filter_datecloture = $opclotureyear . '-' . sprintf("%02d", $opcloturemonth) . '-' . sprintf("%02d", $opclotureday) . ' 00:00:00';

// Used with redirection from stats
$search_no_date = GETPOST('search_no_date'); // Will be 1 if we need to filter
$search_year = GETPOST('search_year'); // year to filter
$search_sale = GETPOST('search_sale'); // id of sale

// Used for massaction
$massaction = GETPOST('massaction');
$action = GETPOST('action');
$toselect = GETPOST('toselect', 'array');


if (GETPOST('search_cli_four') != '') $search_cli_four = GETPOST('search_cli_four');
else $search_cli_four = -1;
$search_product_category = GETPOST('search_product_category', 'int');

// Security check
$contratid = GETPOST('id', 'int');
if (!empty($user->societe_id)) $socid = $user->societe_id;
$result = restrictedArea($user, 'contrat', $contratid);

if ($search_status != '') {
    $tmp = explode('&', $search_status);
    $mode = $tmp[0];
    if (empty($tmp[1])) $filter = '';
    else {
        if ($tmp[1] == 'filter=notexpired') $filter = 'notexpired';
        if ($tmp[1] == 'filter=expired') $filter = 'expired';
    }
} else {
    $search_status = $mode;
    if ($filter == 'expired') $search_status .= '&filter=expired';
    if ($filter == 'notexpired') $search_status .= '&filter=notexpired';
}

// param for filter and print_barre_liste
$param = '';
if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param .= '&contextpage=' . urlencode($contextpage);
if ($limit > 0 && $limit != $conf->liste_limit) $param .= '&limit=' . $limit;
if ($search_contract)           $param .= '&search_contract=' . urlencode($search_contract);
if ($search_name)               $param .= '&search_name=' . urlencode($search_name);
if ($search_service)            $param .= '&search_service=' . urlencode($search_service);
if ($mode)                      $param .= '&mode=' . urlencode($mode);
if ($filter)                    $param .= '&filter=' . urlencode($filter);
if (!empty($filter_opouvertureprevue) && $filter_opouvertureprevue != -1) $param .= '&filter_opouvertureprevue=' . urlencode($filter_opouvertureprevue);
if (!empty($filter_opclotureprevue) && $filter_opclotureprevue != -1) $param .= '&filter_opclotureprevue=' . urlencode($filter_opclotureprevue);
if (!empty($filter_opdateEnd) && $filter_opdateEnd != -1) $param .= '&filter_opdateEnd=' . urlencode($filter_opdateEnd);
if (!empty($filter_opouvertureprevue) && $filter_opouvertureprevue != -1) $param .= '&filter_opouvertureprevue=' . urlencode($filter_opouvertureprevue);
if (!empty($filter_opcloture) && $filter_opcloture != -1) $param .= '&filter_opcloture=' . urlencode($filter_opcloture);
if ($filter_dateouvertureprevue != '') $param .= '&dateouvertureprevueday=' . sprintf("%02d", $opouvertureprevueday) . '&dateouvertureprevuemonth=' . sprintf("%02d", $opouvertureprevuemonth) . '&dateouvertureprevueyear=' . sprintf("%02d", $opouvertureprevueyear);
if ($filter_dateclotureprevue != '') $param .= '&dateclotureprevueday=' . sprintf("%02d", $opclotureprevueday) . '&dateclotureprevuemonth=' . sprintf("%02d", $opclotureprevuemonth) . '&dateclotureprevueyear=' . sprintf("%02d", $opclotureprevueyear);
if ($filter_datecloture != '')  $param .= '&opclotureday=' . $opclotureday . '&opcloturemonth=' . $opcloturemonth . '&opclotureyear=' . $opclotureyear;
if ($search_cli_four != '-1')   $param .= '&search_cli_four=' . $search_cli_four;
if ($search_qty)                $param .= '&search_qty=' . $search_qty;
if ($search_price)              $param .= '&search_price=' . $search_price;
if ($options_serv_duree)        $param .= '&options_serv_duree=' . $options_serv_duree;
if ($search_product_category)   $param .= '&search_product_category=' . $search_product_category;
// Used with redirection from stats
if ($search_no_date)            $param .= '&search_no_date=' . $search_no_date;
if ($search_year)               $param .= '&search_year=' . $search_year;
if ($search_sale)               $param .= '&search_sale=' . $search_sale;
// Add $param from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_param.tpl.php';

// param url for redirection. We need to keep the page
$param_url = $param;
if ($sortfield)                 $param_url .= '&sortfield=' . urlencode($sortfield);
if ($sortorder)                 $param_url .= '&sortorder=' . urlencode($sortorder);
if ($page)                      $param_url .= '&page=' . urlencode($page);

$arrayfields = array(
    'c.ref' => array('label' => $langs->trans("Contract"), 'checked' => 1, 'position' => 80),
    'p.description' => array('label' => $langs->trans("Service"), 'checked' => 1, 'position' => 80),
    's.nom' => array('label' => $langs->trans("ThirdParty"), 'checked' => 1, 'position' => 100),
    /*'ef.serv_date_start' => array('label' => $langs->trans("ContratplusDateStart"), 'checked' => 1, 'position' => 80),
    'ef.serv_duree' => array('label' => $langs->trans("ContratplusEngage"), 'checked' => 1, 'position' => 80),
    'ef.serv_date_end' => array('label' => $langs->trans("ContratplusDateEnd"), 'checked' => 1, 'position' => 80),*/
    'cd.subprice' => array('label' => $langs->trans("PriceUHT"), 'checked' => 0, 'position' => 100),
    'cd.qty' => array('label' => $langs->trans("Qty"), 'checked' => 0, 'position' => 100),
    'cd.tva_tx' => array('label' => $langs->trans("VAT"), 'checked' => 0, 'position' => 100),
    'cd.total_ht' => array('label' => $langs->trans("TotalHT"), 'checked' => 0, 'position' => 100),
    'cd.total_tva' => array('label' => $langs->trans("TotalVAT"), 'checked' => 0, 'position' => 100),
    'cd.tms' => array('label' => $langs->trans("DateModificationShort"), 'checked' => 0, 'position' => 500),
    'status' => array('label' => $langs->trans("Status"), 'checked' => 1)

);
// Extra fields
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
{
    foreach($extrafields->attribute_label as $key => $val)
    {
        if (! empty($extrafields->attribute_list[$key])) $arrayfields["ef.".$key]=array('label'=>$extrafields->attribute_label[$key], 'checked'=>(($extrafields->attribute_list[$key]<0)?0:1), 'position'=>$extrafields->attribute_pos[$key], 'enabled'=>(abs($extrafields->attribute_list[$key])!=3 && $extrafields->attribute_perms[$key]));
    }
}

$contextpage = 'servicelist';

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array($contextpage));

/*
 * Action
 */
include DOL_DOCUMENT_ROOT . '/core/actions_changeselectedfields.inc.php';

if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") || GETPOST("button_removefilter")) // All test are required to be compatible with all browsers
{
    $search_qty = "";
    $search_price = "";

    $opouvertureprevuemonth = "00";
    $opouvertureprevueday = "00";
    $opouvertureprevueyear = "00";
    $filter_opouvertureprevue = "";
    $filter_dateouvertureprevue = "";

    $options_serv_duree = "";

    $opclotureprevuemonth = "00";
    $opclotureprevueday = "00";
    $opclotureprevueyear = "00";
    $filter_opclotureprevue = "";
    $filter_dateclotureprevue = "";

    $opcloturemonth = "";
    $opclotureday = "";
    $opclotureyear = "";
    $filter_opdateEnd = "";
    $filter_datecloture = "";

    $search_cli_four = '-1';
    $subprice = '';

    $search_product_category = 0;
    $search_name = "";
    $search_contract = "";
    $search_service = "";
    $search_status = -1;

    // Used with redirection from stats
    $search_no_date = "";
    $search_year = "";
    $search_sale = "";

    $mode = '';
    $filter = '';
    $toselect = '';
    $page = 0;
    $param = "";
    $param_url = "";
    $search_array_options = array();
}

if ($action === 'confirm_mass_setdate' && GETPOST('confirm') === 'yes')
{
    $selected = unserialize(GETPOST('selected'));
    $date = GETPOST('serv_date_start');
    if ($date) {
        $datetime = date_create_from_format('d/m/Y', $date);
        $iso_datetime = $datetime->format('Y-m-d');
    } else
        $iso_datetime = $date;
    $duration = GETPOST('serv_duree') < 0 ? 0 : GETPOST('serv_duree');
    $res = massactionSetDateAndDuration($selected, $iso_datetime, $duration);
    if ($res < 0) {
        setEventMessages($langs->trans('SetDateError'), '', 'errors');
    } else {
        setEventMessages($langs->trans('SetDateDone'), '', 'mesgs');
    }

    exit(header("Location: ".$_SERVER['PHP_SELF'].'?'.$param_url));
}

$selected = unserialize(GETPOST('selected'));
$parameters = array('toselect'=>$selected, 'confirm'=>GETPOST('confirm'), 'param_url'=>$param_url);
$reshook = $hookmanager->executeHooks('doMassActions', $parameters, $object, $action);
if (!empty($reshook))
{
    exit(header("Location: ".$_SERVER['PHP_SELF'].'?'.$param_url));
}

/*
 * View
 */

$now = dol_now();

$companystatic = new Societe($db);
$form = new Form($db);
$formother = new FormOther($db);

llxHeader();

if ($massaction == 'mass_setdate')
{
    $text = $langs->trans('SelectDateAndDuration');
    $url = $_SERVER["PHP_SELF"].'?'.$param_url;
    $formquestion = array(
        array('name'=>'selected','type'=>'hidden','value'=>serialize($toselect)),
        array('label'=> $langs->trans('ContratplusDateStart'),'name'=>'serv_date_start','type'=>'date','value'=>dol_now()),
        array('label'=> $langs->trans('ContratplusEngage'), 'name'=>'serv_duree','type'=>'select','values'=>array('1'=>'12', '2'=>'24', '3'=>'36', '4'=>'48', '5'=>'60', '6'=>'autres')),
    );
    print $form->formconfirm($url, $langs->trans("MassSetDate"), $text, "confirm_mass_setdate", $formquestion, '', 1, '250');

    // set date to $toselect
}

// Execute hook on massaction for confirm display
$parameters = array('toselect'=>$toselect);
$reshook = $hookmanager->executeHooks('displayMassActionConfirmMessage', $parameters, $object, $massaction);
if (!empty($reshook))
{
    print $hookmanager->resPrint;
}

$sql = "SELECT c.rowid as cid, c.ref, c.statut as cstatut,";
$sql .= " s.rowid as socid, s.nom as name, s.email, s.client, s.fournisseur,";
$sql .= " cd.rowid,cd.fk_contrat, cd.description, cd.statut, cd.subprice as price, cd.qty,";
$sql .= " p.rowid as pid, p.ref as pref, p.label as label, p.fk_product_type as ptype, p.entity as pentity,";
if (!$user->rights->societe->client->voir && !$socid) $sql .= " sc.fk_soc, sc.fk_user,";
$sql .= " cd.qty,";
$sql .= " cd.total_ht,";
$sql .= " cd.total_tva,";
$sql .= " cd.tva_tx,";
$sql .= " cd.subprice,";
//$sql .= " ca.color as color,";
$sql .= " cd.tms as date_update";

// Add fields from extrafields
foreach ($extrafields->attribute_label as $key => $val) $sql .= ($extrafields->attribute_type[$key] != 'separate' ? ",ef." . $key . ' as options_' . $key : '');

if (!empty($filter_opdateEnd) && $filter_opdateEnd != -1 && $filter_datecloture != '')
    $sql .= ", DATE_ADD(ef.serv_date_start,INTERVAL ef.serv_duree MONTH) as DATE_MIN";
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters);    // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;
$sql .= " FROM " . MAIN_DB_PREFIX . "societe as s,";
if (!$user->rights->societe->client->voir && !$socid) $sql .= " " . MAIN_DB_PREFIX . "societe_commerciaux as sc,";

$sql .= " " . MAIN_DB_PREFIX . "contrat as c";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "contratdet as cd ON c.rowid = cd.fk_contrat";
$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'contrat_extrafields as ce ON c.rowid=ce.fk_object';
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "contratdet_extrafields as ef on cd.rowid = ef.fk_object";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product as p ON cd.fk_product = p.rowid";
$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'categorie_product as cp ON cp.fk_product=cd.fk_product';
$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'categorie as ca ON ca.rowid=cp.fk_categorie';

$sql .= " WHERE c.entity = " . $conf->entity;
$sql .= " AND c.rowid = cd.fk_contrat";
if ($search_product_category > 0) $sql .= " AND cp.fk_categorie = " . $search_product_category;
$sql .= " AND c.fk_soc = s.rowid";
if (!$user->rights->societe->client->voir && !$socid) $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = " . $user->id;
if ($search_product_category > 0) $sql .= " AND cp.fk_categorie = " . $search_product_category;
if ($mode == "0") $sql .= " AND cd.statut = 0";
if ($mode == "4") $sql .= " AND cd.statut = 4";
if ($mode == "5") $sql .= " AND cd.statut = 5";
if ($filter == "expired") $sql .= " AND cd.date_fin_validite < '" . $db->idate($now) . "'";
if ($filter == "notexpired") $sql .= " AND cd.date_fin_validite >= '" . $db->idate($now) . "'";
if ($search_name) $sql .= " AND s.nom LIKE '%" . $db->escape($search_name) . "%'";
if ($search_contract) $sql .= " AND c.ref LIKE '%" . $db->escape($search_contract) . "%'";
if ($search_service) $sql .= " AND (p.ref LIKE '%" . $db->escape($search_service) . "%' OR p.description LIKE '%" . $db->escape($search_service) . "%' OR cd.description LIKE '%" . $db->escape($search_service) . "%')";
if ($socid > 0) $sql .= " AND s.rowid = " . $socid;
if ($search_qty) $sql .= " AND cd.qty >= " . $search_qty;
if ($search_price) $sql .= " AND cd.subprice >= " . $search_price;
if ($options_serv_duree) $sql .= " AND ef.serv_duree = '" . $options_serv_duree . "'";

if ($conf->global->CONTRATPLUS_SUPPLIER_CONTRACT == 1) {
    if ($search_cli_four == '1')
        $sql .= " AND ce.supplier_contract = 1";
    else if ($search_cli_four == '0')
        $sql .= " AND ce.supplier_contract IS NULL";
} else
    $sql .= " AND ce.supplier_contract IS NULL";

if (!empty($filter_opouvertureprevue) && $filter_opouvertureprevue != -1 && $filter_dateouvertureprevue != '') $sql .= " AND ef.serv_date_start " . $filter_opouvertureprevue . " '" . $filter_dateouvertureprevue . "'";
if (!empty($filter_opclotureprevue) && $filter_opclotureprevue != -1 && $filter_dateclotureprevue != '') $sql .= " AND ef.serv_date_end " . $filter_opclotureprevue . " '" . $filter_dateclotureprevue . "'";
if (!empty($filter_opdateEnd) && $filter_opdateEnd != -1 && $filter_datecloture != '') $sql .= " AND cd.date_fin_validite " . $filter_opdateEnd . " DATE_MIN ";
if (!empty($filter_opdateEnd) && $filter_opdateEnd != -1 && $filter_datecloture != '') $sql .= " AND cd.date_cloture " . $filter_opdateEnd . " '" . $db->idate($filter_datecloture) . "'";

$filter_dateouvertureprevue = dol_mktime(0, 0, 0, $opouvertureprevuemonth, $opouvertureprevueday, $opouvertureprevueyear);
$filter_datecloture = dol_mktime(0, 0, 0, $opcloturemonth, $opclotureday, $opclotureyear);

// Used with redirection from stats
if (!empty($search_no_date)) $sql .= " AND ef.serv_date_start IS NULL";
if (!empty($search_year)) $sql .= " AND date_format(ef.serv_date_start, '%Y') = ".$search_year;
if (!empty($search_sale) && !$user->rights->societe->client->voir && !$socid) $sql .= " AND sc.fk_user = ".$search_sale;

$sql .= " GROUP BY cd.rowid, ce.supplier_contract";

// Add fields from extrafields
foreach ($extrafields->attribute_label as $key => $val) $sql .= ($extrafields->attribute_type[$key] != 'separate' ? ", ef." . $key : '');

$totalnboflines = 0;

$result = $db->query($sql);
if ($result) {
    $totalnboflines = $db->num_rows($result);
}

if ($conf->global->CONTRATPLUS_SUPPLIER_CONTRACT == 1) {
    if ($sortfield != "ce.supplier_contract") {
        $sql .= " ORDER BY ce.supplier_contract DESC";
    } else {
        $sql .= " ORDER BY " . $sortfield . " " . $sortorder;
    }
} else
    $sql .= $db->order($sortfield, $sortorder);
$sql .= ", " . $sortfield . " " . $sortorder;
$sql .= $db->plimit($limit + 1, $offset);

dol_syslog("contrat/services.php", LOG_DEBUG);
$resql = $db->query($sql);
if ($resql) {
    $num = $db->num_rows($resql);
    $i = 0;

    // Massactions
    $entries = array();

    if ($user->rights->contrat->creer)
        $entries['mass_setdate'] = $langs->trans('MassSetDate');

    $massactionbutton=$form->selectMassAction('', $entries);

    print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
	if ((float) DOL_VERSION >= 11) {
		print '<input type="hidden" name="token" value="' . newToken() . '">';
	} else {
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	}
    print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
    print '<input type="hidden" name="action" value="list">';
    print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
    print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
    print '<input type="hidden" name="page" value="' . $page . '">';
    print '<input type="hidden" name="contextpage" value="' . $contextpage . '">';
    $title = $langs->trans("Contratplus_ServicePlus");

    if ($mode == "0") $title = $langs->trans("ListOfInactiveServices");    // Must use == "0"
    if ($mode == "4" && $filter == "notexpired") $title = $langs->trans("ListOfRunningServices");
    if ($mode == "4" && $filter == "expired") $title = $langs->trans("ListOfExpiredServices");
    if ($mode == "5") $title = $langs->trans("ListOfClosedServices");


    print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $totalnboflines, 'object_contract.png', 0, '', '', $limit);

    if (!empty($conf->global->CONTRATPLUS_WIZARD_SERVICE) && $conf->global->CONTRATPLUS_WIZARD_ACTIVE == 1) {
        print '<div class="cp-demo-div">';
        print $conf->global->CONTRATPLUS_WIZARD_SERVICE;
        print '</div>';
        print '<div class="clearboth"></div>';
    }

    if ($sall) {
        foreach ($fieldstosearchall as $key => $val) $fieldstosearchall[$key] = $langs->trans($val);
        print '<div class="divsearchfieldfilter">' . $langs->trans("FilterOnInto", $sall) . join(', ', $fieldstosearchall) . '</div>';
    }

    // DEBUG
    if ($conf->global->MAIN_FEATURES_LEVEL == '3') {
        print '<table class="noborder tagtable liste listwithfilterbefore" width="100vw">';
        print '<tr><td><h2>DEBUG</h2></td></tr>';
        print '<tr><td><b>SQL : </b>' . $sql . '</td></tr>';
        print '<tr><td><b>PARAM : </b>' . $param . '</td></tr>';
        print '<tr><td><b>PARAM_URL : </b>' . $param_url . '</td></tr>';
        print '</table>';
    }

    // If the user can view prospects other than his'
    $moreforfilter = '';
    // Filtre Client / Fournisseur
    if ($user->rights->societe->client->voir & $user->rights->fournisseur->lire && $conf->global->CONTRATPLUS_SUPPLIER_CONTRACT == 1) {
        $moreforfilter .= '<div class="divsearchfield">';
        $moreforfilter .= $langs->trans('ServiceAllClientFournisseur');
        $moreforfilter .= ' <select class="flat minwidth100 maxwidth300" id="search_cli_four" name="search_cli_four">
        <option value="-1" ' . ($search_cli_four == "-1" ? "selected" : "") . '>' . $langs->trans('All') . '</option>
        <option value="0" ' . ($search_cli_four == "0" ? "selected" : "") . '>' . $langs->trans('Client') . '</option>
        <option value="1" ' . ($search_cli_four == "1" ? "selected" : "") . '>' . $langs->trans('Fournisseur') . '</option>
        </select>';
        $moreforfilter .= '</div>';
    }

    // If the user can view categories of products
    if ($conf->categorie->enabled && ($user->rights->produit->lire || $user->rights->service->lire)) {
        include_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
        $moreforfilter .= '<div class="divsearchfield">';
        $moreforfilter .= $langs->trans('IncludingProductWithTag') . ': ';
        $cate_arbo = $form->select_all_categories(Categorie::TYPE_PRODUCT, null, 'parent', null, null, 1);
        $moreforfilter .= $form->selectarray('search_product_category', $cate_arbo, $search_product_category, 1, 0, 0, '', 0, 0, 0, 0, '', 1);
        $moreforfilter .= '</div>';
    }


    $parameters = array();
    $reshook = $hookmanager->executeHooks('printFieldPreListTitle', $parameters);    // Note that $action and $object may have been modified by hook
    if (empty($reshook)) $moreforfilter .= $hookmanager->resPrint;
    else $moreforfilter = $hookmanager->resPrint;
    if (!empty($moreforfilter)) {
        print '<div class="liste_titre liste_titre_bydiv centpercent">';
        print $moreforfilter;
        print '</div>';
    }

    $varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
    $selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);
    $selectedfields.=$form->showCheckAddButtons('checkforselect', 1);


    print '<div class="div-table-responsive">';
    print '<table class="tagtable liste' . ($moreforfilter ? " listwithfilterbefore" : "") . '">' . "\n";

    print '<tr class="liste_titre">';
    print_liste_field_titre('');
    print_liste_field_titre('');
    if (!empty($arrayfields['c.ref']['checked'])) print_liste_field_titre($arrayfields['c.ref']['label'], $_SERVER["PHP_SELF"], "c.ref", "", $param, "", $sortfield, $sortorder);
    //print_liste_field_titre($langs->trans("Contract"), $_SERVER["PHP_SELF"], "c.rowid", $param, "", '', $sortfield, $sortorder);
    if (!empty($arrayfields['p.description']['checked'])) print_liste_field_titre($arrayfields['p.description']['label'], $_SERVER["PHP_SELF"], "p.description", "", $param, "", $sortfield, $sortorder);    //print_liste_field_titre($langs->trans("Company"), $_SERVER["PHP_SELF"], "s.nom", $param, "", "", $sortfield, $sortorder);
    if (!empty($arrayfields['s.nom']['checked'])) print_liste_field_titre($arrayfields['s.nom']['label'], $_SERVER["PHP_SELF"], "s.nom", "", $param, "", $sortfield, $sortorder);
    /*if (!empty($arrayfields['ef.serv_date_start']['checked'])) print_liste_field_titre($langs->trans("ContratplusDateStart"), $_SERVER["PHP_SELF"], "ef.serv_date_start", $param, '', ' align="center"', $sortfield, $sortorder);
    if (!empty($arrayfields['ef.serv_duree']['checked'])) print_liste_field_titre($langs->trans("ContratplusEngage"), $_SERVER["PHP_SELF"], "ef.serv_duree", $param, '', ' align="center"', $sortfield, $sortorder);
    if (!empty($arrayfields['ef.serv_date_end']['checked'])) print_liste_field_titre($langs->trans("ContratplusDateEnd"), $_SERVER["PHP_SELF"], "ef.serv_date_end", $param, '', ' align="center"', $sortfield, $sortorder);*/
    if (!empty($arrayfields['cd.subprice']['checked'])) print_liste_field_titre($arrayfields['cd.subprice']['label'], $_SERVER["PHP_SELF"], "cd.subprice", "", $param, 'align="center" class="nowrap"', $sortfield, $sortorder);
    if (!empty($arrayfields['cd.qty']['checked'])) print_liste_field_titre($arrayfields['cd.qty']['label'], $_SERVER["PHP_SELF"], "cd.qty", "", $param, 'align="center" class="nowrap"', $sortfield, $sortorder);
    if (!empty($arrayfields['cd.tva_tx']['checked'])) print_liste_field_titre($arrayfields['cd.tva_tx']['label'], $_SERVER["PHP_SELF"], "cd.tva_tx", "", $param, 'align="center" class="nowrap"', $sortfield, $sortorder);
    if (!empty($arrayfields['cd.total_ht']['checked'])) print_liste_field_titre($arrayfields['cd.total_ht']['label'], $_SERVER["PHP_SELF"], "cd.total_ht", "", $param, 'align="center" class="nowrap"', $sortfield, $sortorder);
    if (!empty($arrayfields['cd.total_tva']['checked'])) print_liste_field_titre($arrayfields['cd.total_tva']['label'], $_SERVER["PHP_SELF"], "cd.total_tva", "", $param, 'align="center" class="nowrap"', $sortfield, $sortorder);
    // Extra fields
    include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_title.tpl.php';
    // Hook fields
    $parameters = array('arrayfields' => $arrayfields, 'param' => $param, 'sortfield' => $sortfield, 'sortorder' => $sortorder);
    $reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters);    // Note that $action and $object may have been modified by hook
    print $hookmanager->resPrint;
    if (!empty($arrayfields['cd.datec']['checked'])) print_liste_field_titre($arrayfields['cd.datec']['label'], $_SERVER["PHP_SELF"], "cd.datec", "", $param, 'align="center" class="nowrap"', $sortfield, $sortorder);
    if (!empty($arrayfields['cd.tms']['checked'])) print_liste_field_titre($arrayfields['cd.tms']['label'], $_SERVER["PHP_SELF"], "cd.tms", "", $param, 'align="center" class="nowrap"', $sortfield, $sortorder);
    if (!empty($arrayfields['status']['checked'])) print_liste_field_titre($arrayfields['status']['label'], $_SERVER["PHP_SELF"], "cd.statut", "", $param, 'align="center"', $sortfield, $sortorder);
    print_liste_field_titre('');
    print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"], "", '', '', 'align="center"', $sortfield, $sortorder, 'maxwidthsearch ');


    print "</tr>\n";


    print '<tr class="liste_titre">';
    print '<td class="liste_titre"></td>';
    print '<td class="liste_titre"></td>';


    if (!empty($arrayfields['c.ref']['checked'])) {
        print '<td class="liste_titre">';
        print '<input type="hidden" name="filter" value="' . $filter . '">';
        print '<input type="hidden" name="mode" value="' . $mode . '">';
        print '<input type="text" class="flat" size="8" name="search_contract" value="' . dol_escape_htmltag($search_contract) . '">';
        print '</td>';
    }

    //Service label
    if (!empty($arrayfields['p.description']['checked'])) {
        print '<td class="liste_titre">';
        print '<input type="text" class="flat maxwidth100" name="search_service" value="' . dol_escape_htmltag($search_service) . '">';
        print '</td>';
    }

    // Third party
    if (!empty($arrayfields['s.nom']['checked'])) {
        print '<td class="liste_titre">';
        print '<input type="text" class="flat maxwidth100" name="search_name" value="' . dol_escape_htmltag($search_name) . '">';
        print '</td>';
    }

    if (!empty($arrayfields['cd.subprice']['checked'])) {
        // Service Price
        print '<td class="liste_titre" align="center">';
        print '<input type="text" class="flat" size="6" name="search_price" value="' . dol_escape_htmltag($search_price) . '">';
        print '</td>';
    }

    if (!empty($arrayfields['cd.qty']['checked'])) {
        // Service Qty
        print '<td class="liste_titre" align="center">';
        print '<input type="text" class="flat" size="6" name="search_qty" value="' . dol_escape_htmltag($search_qty) . '">';
        print '</td>';
    }

    if (!empty($arrayfields['cd.tva_tx']['checked'])) print '<td class="liste_titre"></td>';
    if (!empty($arrayfields['cd.total_ht']['checked'])) print '<td class="liste_titre"></td>';
    if (!empty($arrayfields['cd.total_tva']['checked'])) print '<td class="liste_titre"></td>';

    // Extra fields
    /*if (!empty($arrayfields['ef.serv_date_start']['checked'])) {
        //Date debut
        print '<td class="liste_titre" align="center" style=" width: 200px;">';
        $arrayofoperators = array('<' => '<', '>' => '>');
        print $form->selectarray('filter_opouvertureprevue', $arrayofoperators, $filter_opouvertureprevue, 1);
        print ' ';
        $filter_dateouvertureprevue = dol_mktime(0, 0, 0, $opouvertureprevuemonth, $opouvertureprevueday, $opouvertureprevueyear);
        // Wrong dol_mktime means that everything is set to 0
        if ($filter_dateouvertureprevue < 0 || ($opouvertureprevuemonth == '00' && $opouvertureprevueday == '00' && $opouvertureprevueyear == '00'))
            $filter_dateouvertureprevue = '';
        print $form->select_date($filter_dateouvertureprevue, 'dateouvertureprevue', 0, 0, 1, '', 1, 0, 1);
        print '</td>';
    }

    if (!empty($arrayfields['ef.serv_duree']['checked'])) {
        // Service duree
        print '<td class="liste_titre" align="center" style=" width: 220px;">';
        print $extrafields->showInputField('serv_duree', $options_serv_duree);

        print '&nbsp;</td>';
    }

    if (!empty($arrayfields['ef.serv_date_end']['checked'])) {
        // Date cloture
        print '<td class="liste_titre" align="center" style=" width: 200px;">';
        $arrayofoperators = array('<' => '<', '>' => '>');
        print $form->selectarray('filter_opclotureprevue', $arrayofoperators, $filter_opclotureprevue, 1);
        print ' ';
        $filter_dateclotureprevue = dol_mktime(0, 0, 0, $opclotureprevuemonth, $opclotureprevueday, $opclotureprevueyear);
        // Wrong dol_mktime means that everything is set to 0
        if ($filter_dateclotureprevue < 0 || ($opclotureprevuemonth == '00' && $opclotureprevueday == '00' && $opclotureprevueyear == '00'))
            $filter_dateclotureprevue = '';
        print $form->select_date($filter_dateclotureprevue, 'dateclotureprevue', 0, 0, 1, '', 1, 0, 1);
        print '</td>';
    }*/
    include './extrafields_list_services_search_input.tpl.php';

    if (!empty($arrayfields['cd.tms']['checked'])) print '<td class="liste_titre"></td>';

    if (!empty($arrayfields['status']['checked'])) {
        // Service Status
        print '<td class="liste_titre" align="center">';
        $arrayofstatus = array(
            '0' => $langs->trans("ServiceStatusInitial"),
            '4' => $langs->trans("ServiceStatusRunning"),
            '4&filter=notexpired' => $langs->trans("ServiceStatusNotLate"),
            '4&filter=expired' => $langs->trans("ServiceStatusLate"),
            '5' => $langs->trans("ServiceStatusClosed")
        );
        print $form->selectarray('search_status', $arrayofstatus, (strstr($search_status, ',') ? -1 : $search_status), 1, 0, '', 0, 0, 0, '', 'maxwidth100onsmartphone');
        print '</td>';
    }
    print '<td class="liste_titre"></td>';


    print '<td class="liste_titre" align="right">';
    $searchpitco = $form->showFilterAndCheckAddButtons(0);
    print $searchpitco;
    print '</td>';
    print "</tr>\n";

    $var = true;
    while ($i < min($num, $limit)) {
        $obj = $db->fetch_object($resql);

        $contractstatic = new Contrat($db);
        $staticcontratligne = new ContratLigne($db);
        $productstatic = new Product($db);
        $extrafieldscontrat = new ExtraFields($db);
        $extrafieldsline = new ExtraFields($db);

        $contractstatic->id = $obj->cid;
        $contractstatic->ref = $obj->ref ? $obj->ref : $obj->cid;
        $staticcontratligne->id = $obj->rowid;

        $staticcontratligne->fetch($obj->rowid);
        $extralabelscontrat = $extrafieldscontrat->fetch_name_optionals_label('contrat');
        $extralabelslines = $extrafieldsline->fetch_name_optionals_label('contratdet');

        $contractstatic->fetch_optionals($contractstatic->id, $extralabelscontrat);
        $staticcontratligne->fetch_optionals($staticcontratligne->rowid, $extralabelslines);

        $var = !$var;

        $supplier = isSupplierContract($db, $contractstatic->id);


        print "<tr class=\"row-selectable\" " . $bc[$var];
        if ($supplier) print ' style="font-style: italic ; background-color: #' . $obj->color . ' !important; background: #' . $obj->color . '"';
        print ">";

        //Color
        if (!$supplier) print '<td style=" background-color: #' . $obj->color . '">';
        else print '<td>';
        print '&nbsp;</td>';

        if ($supplier) {
            print '<td>' . img_object($langs->trans('SupplierContract'), "sending") . '</td>';
        } else {
            print '<td>' . img_object($langs->trans('CustomerContract'), "user") . '</td>';
        }

        if (!empty($arrayfields['c.ref']['checked'])) {
            // Ref
            print '<td class="nowrap"><a href="'.dol_buildpath('/contratplus/card.php', 1).'?id=' . $obj->fk_contrat . '">';
            print img_object($langs->trans("ShowContract"), "contract") . ' ' . (isset($obj->ref) ? $obj->ref : $obj->cid) . '</a>';
            if ($obj->nb_late) print img_warning($langs->trans("Late"));
            print '</td>';
        }

        if (!empty($arrayfields['p.description']['checked'])) {
            // Service
            print '<td>';
            if ($obj->pid) {
                $productstatic->id = $obj->pid;
                $productstatic->type = $obj->ptype;
                $productstatic->ref = $obj->pref;
                $productstatic->entity = $obj->pentity;
                print $productstatic->getNomUrl(1, '', 50);
                print $obj->label ? ' - ' . dol_trunc($obj->label, 50) : '';
                if (!empty($obj->description) && !empty($conf->global->PRODUCT_DESC_IN_LIST)) print '<br>' . dol_nl2br($obj->description);
            } else {
                if ($obj->type == 0) print img_object($obj->description, 'product') . ' ' . dol_trunc($obj->description, 50);
                if ($obj->type == 1) print img_object($obj->description, 'service') . ' ' . dol_trunc($obj->description, 50);
            }
            print '</td>';
        }

        if (!empty($arrayfields['s.nom']['checked'])) {
            // Third party
            print '<td>';
            $companystatic->id = $obj->socid;
            $companystatic->name = $obj->name;
            $companystatic->client = 1;
            print $companystatic->getNomUrl(1, 'customer', 35);
            print '</td>';
        }

        if (!empty($arrayfields['cd.subprice']['checked'])) {
            // Service price
            print '<td align="center">';

            if (isset($obj->price)) {
                print price($obj->price) . ' €';
                if (isSupplierContract($db, $contractstatic->id))
                    $stotalprice += intval($obj->price);
                else
                    $ctotalprice += intval($obj->price);
            } else
                print '&nbsp';

            print '</td>';
        }

        if (!empty($arrayfields['cd.qty']['checked'])) {
            // Service qty
            print '<td align="center">' . $obj->qty . '</td>';
            if (isSupplierContract($db, $contractstatic->id))
                $sqty += $obj->qty;
            else
                $cqty += $obj->qty;
        }

        if (!empty($arrayfields['cd.tva_tx']['checked'])) {
            print '<td align="center">';
            print price2num($obj->tva_tx) . '%';
            print '</td>';
        }

        if (!empty($arrayfields['cd.total_ht']['checked'])) {
            // Service total
            $totalline = intval($obj->qty) * intval($obj->price);
            if (isSupplierContract($db, $contractstatic->id))
                $stotallines += $totalline;
            else
                $ctotallines += $totalline;

            print '<td align="center">' . price($totalline) . ' €</td>';
        }

        if (!empty($arrayfields['cd.total_tva']['checked'])) {
            print '<td align="right">';
            if (isSupplierContract($db, $contractstatic->id)) {
                $sTotalTVA = price($obj->total_tva) + intval($obj->qty) * intval($obj->price);
                $sTotalTVAS += $sTotalTVA;
            } else {
                $cTotalTVA = price($obj->total_tva) + intval($obj->qty) * intval($obj->price);
                $cTotalTVAS += $cTotalTVA;
            }

            $totalTVA = price($obj->total_tva) + intval($obj->qty) * intval($obj->price);
            $totalTVAS += $totalTVA;
            print $totalTVA . ' €';
            print '</td>';
        }
        // Extra fields
        /*
         if (!empty($arrayfields['ef.serv_date_start']['checked'])) {
            // ContratplusDateStart

            print '<td align="center">';

            if ($obj->options_serv_date_start != "0000-00-00")
                print dol_print_date($staticcontratligne->array_options['options_serv_date_start']);

            $obj->options_serv_date_start == '';
            print '</td>';
        }

        if (!empty($arrayfields['ef.serv_duree']['checked'])) {
            // ContratplusEngage
            print '<td align="center">';
            print $extrafieldsline->attribute_param['serv_duree']['options'][intval($staticcontratligne->array_options['options_serv_duree'])];
            print '</td>';
        }

        if (!empty($arrayfields['ef.serv_date_end']['checked'])) {
            print '<td align="center">';

            if ($obj->options_serv_date_end != "0000-00-00")
                print dol_print_date($staticcontratligne->array_options['options_serv_date_end']);

            $obj->options_serv_date_end == '';
            print '</td>';
        }
         */
        include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_print_fields.tpl.php';

        if (!empty($arrayfields['cd.tms']['checked'])) {
            print '<td align="center">';
            print dol_print_date($db->jdate($obj->date_update), 'dayhour', 'tzuser');
            print '</td>';
        }

        if (!empty($arrayfields['status']['checked'])) {
            print '<td align="center" class="nowrap">';
            if ($obj->cstatut == 0)    // If contract is draft, we say line is also draft
            {
                //print $contractstatic->LibStatut(0, 5, ($obj->date_fin_validite && $db->jdate($obj->date_fin_validite) < $now));
                print $staticcontratligne->getLibStatut(4);
            } else {
                // print $staticcontratligne->LibStatut($obj->statut, 5, ($obj->date_fin_validite && $db->jdate($obj->date_fin_validite) < $now)?1:0);
                print $staticcontratligne->getLibStatut(4);
            }
            print '</td>';
        }

        print '<td class="nowrap" colspan="2" align="center">';
        print '<input type="checkbox" class="checkforselect" name="toselect[]" id="cb'.$obj->rowid.'" value="'.$obj->rowid.'" ';
        print '></td>';
        print "</tr>\n";
        $i++;
    }

    // Subtotal supplier hide if option disable
    if ($conf->global->CONTRATPLUS_SUPPLIER_CONTRACT != 0) {
        print '<tr class="liste_total" style="font-style: italic;">';

        if (!empty($arrayfields['c.ref']['checked'])) {
            print '<td class="subtotal" colspan="3" align="right">' . $langs->trans("SubtotalSupplier") . ' : </td>';
        }

        if (!empty($arrayfields['p.description']['checked'])) {
            print '<td>&nbsp;</td>';
        }

        if (!empty($arrayfields['s.nom']['checked'])) {
            print '<td>&nbsp;</td>';
        }

        /*if (!empty($arrayfields['ef.serv_date_start']['checked'])) {
            print '<td>&nbsp;</td>';
        }

        if (!empty($arrayfields['ef.serv_duree']['checked'])) {
            print '<td>&nbsp;</td>';
        }

        if (!empty($arrayfields['ef.serv_date_end']['checked'])) {
            print '<td>&nbsp;</td>';
        }*/

        // Unit price without vat
        if (!empty($arrayfields['cd.subprice']['checked'])) {
            //print '<td class="subtotal" align="center">' . price($stotalprice) . ' €</td>';
            print '<td></td>';
        }

        if (!empty($arrayfields['cd.qty']['checked'])) {
            print '<td class="subtotal" align="center">' . $sqty . '</td>';
        }

        if (!empty($arrayfields['cd.tva_tx']['checked'])) {
            print '<td>&nbsp;</td>';
        }

        if (!empty($arrayfields['cd.total_ht']['checked'])) {
            print '<td class="subtotal" align="center">' . price($stotallines) . ' €</td>';
        }

        if (!empty($arrayfields['cd.total_tva']['checked'])) {
            print '<td class="subtotal" align="center">' . price($sTotalTVAS) . ' €</td>';
        }

        // Extra fields
        foreach($extralabels as $key => $val) {
            if ($arrayfields["ef.".$key]['checked']) {
                print '<td>&nbsp;</td>';
            }
        }

        if (!empty($arrayfields['cd.tms']['checked'])) {
            print '<td>&nbsp;</td>';
        }

        if (!empty($arrayfields['status']['checked'])) {
            print '<td>&nbsp;</td>';
        }

        print '<td colspan="2">&nbsp;</td>';
    }

    // Subtotal customer
    print '<tr class="liste_total">';


    if (!empty($arrayfields['c.ref']['checked'])) {
        print '<td class="subtotal" colspan="3" align="right">' . $langs->trans("SubtotalCustomer") . ' : </td>';
    }

    if (!empty($arrayfields['p.description']['checked'])) {
        print '<td>&nbsp;</td>';
    }

    if (!empty($arrayfields['s.nom']['checked'])) {
        print '<td>&nbsp;</td>';
    }

    /*if (!empty($arrayfields['ef.serv_date_start']['checked'])) {
        print '<td>&nbsp;</td>';
    }

    if (!empty($arrayfields['ef.serv_duree']['checked'])) {
        print '<td>&nbsp;</td>';
    }

    if (!empty($arrayfields['ef.serv_date_end']['checked'])) {
        print '<td>&nbsp;</td>';
    }*/

    // Unit price without vat
    if (!empty($arrayfields['cd.subprice']['checked'])) {
        //print '<td class="subtotal" align="center">' . price($ctotalprice) . ' €</td>';
        print '<td></td>';
    }

    if (!empty($arrayfields['cd.qty']['checked'])) {
        print '<td class="subtotal" align="center">' . $cqty . '</td>';
    }

    if (!empty($arrayfields['cd.tva_tx']['checked'])) {
        print '<td>&nbsp;</td>';
    }

    if (!empty($arrayfields['cd.total_ht']['checked'])) {
        print '<td class="subtotal" align="center">' . price($ctotallines) . ' €</td>';
    }

    if (!empty($arrayfields['cd.total_tva']['checked'])) {
        print '<td class="subtotal" align="center">' . price($cTotalTVAS) . ' €</td>';
    }

    // Extra fields
    foreach($extralabels as $key => $val) {
        if ($arrayfields["ef.".$key]['checked']) {
            print '<td>&nbsp;</td>';
        }
    }

    if (!empty($arrayfields['cd.tms']['checked'])) {
        print '<td>&nbsp;</td>';
    }

    if (!empty($arrayfields['status']['checked'])) {
        print '<td>&nbsp;</td>';
    }


    print '<td colspan="2"></td>';

    print '</tr>';

    $db->free($resql);

    print '</table></form>';

    // Cron display
    $cron = new CronJob($db);

    $id = getCronIdByLabel('ContratPlusCron');
    $cron->fetch($id);
    if ($conf->global->CONTRATPLUS_CRON_DATE != 0 && $cron->status == 1 && $conf->global->CONTRATPLUS_CRON_DATE >= dol_now() - 1 * 60) {
        dol_htmloutput_mesg($langs->trans('NextTimeCron') . ' : ' . date('d/m/Y H:i', $conf->global->CONTRATPLUS_CRON_DATE), '', 'warning');
    }
} else {
    dol_print_error($db);
}


llxFooter();

$db->close();
