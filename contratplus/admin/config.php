<?php
/* Copyright (C) 2017-2018	Eric GROULT			    <eric@code42.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 *   	\file       event/index.php
 *		\ingroup    event
 *		\brief      Index page of module event
 */

$res=0;
$msg = '';
$error = 0;
if (! $res && file_exists("../../main.inc.php")) $res=include_once "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=include_once "../../../main.inc.php"; // for custom directory

if (! $res) die("Include of main fails");

dol_include_once('/contratplus/core/function.php');
dol_include_once('/contratplus/core/automatefunction.php');
dol_include_once('/contratplus/core/populate.php');
dol_include_once('/contratplus/class/customcontrat.class.php');

require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT."/contrat/class/contrat.class.php";
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT."/cron/class/cronjob.class.php";

/**
 * Activate services of a contract
 *
 * @param   CustomContrat     $contract       A Contrat object
 * @return  int                         < 0 on error, > 0 on success
 */
function activateServices($contract)
{
    global $user;

    $contract->fetchLines();
    $i = 0;
    while ($i < count($contract->lines))
    {
        $res = $contract->activeLine($user, $contract->lines[$i]->id, date('Y-m-d H:i:s', $contract->lines[0]->date_ouverture_prevue),
            date('Y-m-d H:i:s', $contract->lines[$i]->date_fin_validite));
        if ($res < 0)
            return -1;

        $i++;
    }
    return 0;
}

/**
 * Create services for a contract
 *
 * @param   CustomContrat     $contract       A Contrat object
 * @return  int                         < 0 on error, > 0 on success
 */
function createServices($contract)
{
    $tz = new DateTimeZone('Europe/Paris');
    $date_start = new DateTime('2018-04-07 17:00:00', $tz);
    $date_end = new DateTime('2018-04-08 17:00:00', $tz);
    $res = $contract->addline(
        'Service de test code 42',  //desc
        100.00,                     //puht
        1,                          //qty
        20.00,                      //txtva
        0,                          //txlocaltax1
        0,                          //txlocaltax2
        0,                          //fk_product
        0,                          //remise_percent
        $date_start->format('Y-m-d H:i:s'),
        $date_end->format('Y-m-d H:i:s')
    );
    if ($res > 0)
    {
        $res = $contract->addline(
            'Service de test 2 code 42',  //desc
            10.00,                     //puht
            1,                          //qty
            20.00,                      //txtva
            0,                          //txlocaltax1
            0,                          //txlocaltax2
            0,                          //fk_product
            0,                          //remise_percent
            $date_start->format('Y-m-d H:i:s'),
            $date_end->format('Y-m-d H:i:s')
        );
    }

    return $res;
}

/**
 * Create and validate a contract
 *
 * @param   User        $user       The object given by dolibarr global (global $user)
 * @param   Database    $db         The object given by dolibarr global (global $db)
 * @return  CustomContrat                 null on error, Contrat object on success
 */
function createValidateContract($user, $db)
{
    $contract = new CustomContrat($db);

    $contract->socid = 189; //id code42 neocenter database
    $contract->date_contrat	= date('Y-m-d H:i:s'); //date d'aujourd'hui
    $contract->commercial_suivi_id = 1; //id admin
    $contract->commercial_signature_id = 1;
    $contract->note_public = "Contrat de test code42";
    $contract->note_private = "Contrat de test code42";

    if ($contract->create($user) > 0 && $contract->validate($user) > 0)
        return $contract;

    return null;
}

/**
 * Create a contract for testing and redirect to index page if all is ok
 *
 * @return int                                      < 0 on error, > 0 on success
 */
function createTest()
{
    global $user, $db;

    $contract = createValidateContract($user, $db);
    if ($contract == null) {
        echo 'Can\'t create or validate the contract';
        return -1;
    }
    if (createServices($contract) < 0) {
        echo 'Can\'t create services';
        $contract->delete($user);
        return -1;
    }
    if (activateServices($contract) < 0) {
        echo 'Can\'t activate services';
        $contract->delete($user);
        return -1;
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    return 1;
}

$page_name = "contratplusSetup";
$langs->load("contratplus@contratplus");

$form = new Form($db);

$usehm=(!empty($conf->global->MAIN_USE_HOURMIN_IN_DATE_RANGE) ? $conf->global->MAIN_USE_HOURMIN_IN_DATE_RANGE : 0);

$supplier = (!empty($conf->global->CONTRATPLUS_SUPPLIER_CONTRACT) ? $conf->global->CONTRATPLUS_SUPPLIER_CONTRACT : 0);

/*
 * Actions
*/

if (GETPOST('action', 'alpha') == 'setvar') {
    // check contrat plus configuration
    if ($conf->global->CONTRATPLUS_IS_CONFIGURED == 0)
        dolibarr_set_const($db, 'CONTRATPLUS_IS_CONFIGURED', 1, '', 0, '', $conf->entity);

	// ContratPlusDateStart
	$contratplus_date_start=dol_mktime(0, 0, 0, GETPOST('CONTRATPLUS_DATE_STARTmonth'), GETPOST('CONTRATPLUS_DATE_STARTday'), GETPOST('CONTRATPLUS_DATE_STARTyear'));
	$res = dolibarr_set_const($db, 'CONTRATPLUS_DATE_START', $contratplus_date_start, '', 0, '', $conf->entity);
	if (! $res > 0) $error++;

	// ContratPlusDateEnd
	$contratplus_date_end=dol_mktime(0, 0, 0, GETPOST('CONTRATPLUS_DATE_ENDmonth'), GETPOST('CONTRATPLUS_DATE_ENDday'), GETPOST('CONTRATPLUS_DATE_ENDyear'));
    if ($contratplus_date_start > $contratplus_date_end) {
        $error++;
        $msg = $langs->trans("ErrorDate");
    }
    else
    {
        $res = dolibarr_set_const($db, 'CONTRATPLUS_DATE_END', $contratplus_date_end, '', 0, '', $conf->entity);
        if (! $res > 0) $error++;
    }

	// ContratPlusBillDate
	$contratplus_bill_date=dol_mktime(0, 0, 0, GETPOST('CONTRATPLUS_BILL_DATEmonth'), GETPOST('CONTRATPLUS_BILL_DATEday'), GETPOST('CONTRATPLUS_BILL_DATEyear'));
	$res = dolibarr_set_const($db, 'CONTRATPLUS_BILL_DATE', $contratplus_bill_date, '', 0, '', $conf->entity);
	if (! $res > 0) $error++;

	// ContratPlusDateBillNote
	$contratplus_bill_note=GETPOST('CONTRATPLUS_BILL_NOTE', 'alpha');
	$res = dolibarr_set_const($db, 'CONTRATPLUS_BILL_NOTE', $contratplus_bill_note, '', 0, '', $conf->entity);
	if (! $res > 0) $error++;

	// ContratPlusDateBillNotePrivate
	$contratplus_bill_note_private=GETPOST('CONTRATPLUS_BILL_NOTE_PRIVATE', 'alpha');
	$res = dolibarr_set_const($db, 'CONTRATPLUS_BILL_NOTE_PRIVATE', $contratplus_bill_note_private, '', 0, '', $conf->entity);
	if (! $res > 0) $error++;

	// ContratplusCompteDefaut
	$contratplus_compte_defaut=GETPOST('CONTRATPLUS_COMPTE_DEFAUT', 'alpha');
	$res = dolibarr_set_const($db, 'CONTRATPLUS_COMPTE_DEFAUT', $contratplus_compte_defaut, '', 0, '', $conf->entity);
	if (! $res > 0) $error++;

	// ContratplusCompteDefaut
	$contratplus_bypass=GETPOST('CONTRATPLUS_BYPASS', 'int');
	$res = dolibarr_set_const($db, 'CONTRATPLUS_BYPASS', $contratplus_bypass, '', 0, '', $conf->entity);
	if (! $res > 0) $error++;

	// ContratplusCondPaiementDefaut
	$contratplus_cond_paiement=GETPOST('CONTRATPLUS_COND_PAIEMENT_DEFAUT', 'int');
	$res = dolibarr_set_const($db, 'CONTRATPLUS_COND_PAIEMENT_DEFAUT', $contratplus_cond_paiement, '', 0, '', $conf->entity);
	if (! $res > 0) $error++;

	// ContratplusTypePaiementDefaut
	$contratplus_type_paiement=GETPOST('CONTRATPLUS_TYPE_PAIEMENT_DEFAUT', 'int');
	$res = dolibarr_set_const($db, 'CONTRATPLUS_TYPE_PAIEMENT_DEFAUT', $contratplus_type_paiement, '', 0, '', $conf->entity);
	if (! $res > 0) $error++;


	// ContratPlusCustomerInvoiceModelDefault
	$contratplus_model_customer = GETPOST('multimodelcustomer');
    $res = dolibarr_set_const($db, 'CONTRATPLUS_MODEL_CUSTOMER', $contratplus_model_customer, '', 0, '', $conf->entity);
    if (! $res > 0) $error++;

    // ContratPlusSupplierInvoiceModelDefault
    $contratplus_model_supplier = GETPOST('multimodelsupplier');
    $res = dolibarr_set_const($db, 'CONTRATPLUS_MODEL_SUPPLIER', $contratplus_model_supplier, '', 0, '', $conf->entity);
    if (! $res > 0) $error++;

    $contratplus_bypass = GETPOST('contratplus_bypass');
    $res = dolibarr_set_const($db, 'CONTRATPLUS_BYPASS', $contratplus_bypass, '', 0, '', $conf->entity);
    if (! $res > 0) $error++;

	//TEST ERROR
	if (! $error && $msg == '') {
        setEventMessages($langs->trans("SetupSaved"), '', 'mesgs');
	}
	else if ($msg == '') {
        setEventMessages($langs->trans("Error"), '', 'errors');
	}
	else {
        setEventMessages($msg, '', $error == 0 ? 'mesgs' : 'errors');
    }
}

if (GETPOST('action') == 'update') {
    $value_select = GETPOST('select');

    if (intval($value_select) > 4)
        $value_select = '0';

    $res = dolibarr_set_const($db, 'CONTRATPLUS_OPTION', $value_select, '', 0, '', $conf->entity);
    if (! $res > 0)
        setEventMessages($langs->trans("Error"), '', 'errors');
    else
        setEventMessages($langs->trans("SetupSaved"), '', 'mesgs');
}

if (GETPOST('action') == 'setsupplier') {
    $status = GETPOST('status');
    $res = dolibarr_set_const($db, 'CONTRATPLUS_SUPPLIER_CONTRACT', $status, '', 0, '', $conf->entity);
    if (!$res > 0) {
        setEventMessages($langs->trans("Error"), '', 'errors');
    } else {
        if ((float) DOL_VERSION < 9.0) {
            $extrafield = new Extrafields($db);
            $res_update = $extrafield->update('supplier_contract', $langs->trans('ModSupplierContract'), 'boolean', 0, 'contrat', 0, 0, 0, '', 1, '$conf->global->CONTRATPLUS_SUPPLIER_CONTRACT', $status);
            if (!$res_update > 0) setEventMessages($langs->trans("Error"), '', 'errors');
            else setEventMessages($langs->trans("SetupSaved"), '', 'mesgs');
        } else setEventMessages($langs->trans("SetupSaved"), '', 'mesgs');
    }
}

if (GETPOST('action') == 'setsepa') {
    $status = GETPOST('status');
    $res = dolibarr_set_const($db, 'CONTRATPLUS_SEPA_LINK', $status);
    if (!$res > 0) {
        setEventMessages($langs->trans("Error"), '', 'errors');
    }
}

// Check if test mode is activated
if ($conf->global->MAIN_FEATURES_LEVEL=='3' && GETPOST('action') == 'create_test')
    createTest();

if ($conf->global->MAIN_FEATURES_LEVEL=='3' && GETPOST('action') == 'populate_date_end')
    populateDb();

if ($conf->global->MAIN_FEATURES_LEVEL=='3' && GETPOST('action') == 'clean_date_end')
    cleanDateEnd();

if (GETPOST('action'))
    exit(header("Location: ".$_SERVER['PHP_SELF']));

/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/
$morejs = array("/contratplus/js/contratPlus.js");
llxHeader('', $langs->trans("ContratPlusSetup"), '', '', '', '', $morejs, '', 0, 0);

$head = generateHeader();

dol_fiche_head($head, 'setup', "Contrat Plus", 0, 'contratplus@contratplus');

// Cron display
$cron = new CronJob($db);

$id = getCronIdByLabel('ContratPlusCron');
$cron->fetch($id);

if ($conf->global->CONTRATPLUS_CRON_DATE != 0 && $cron->status == 1 && $conf->global->CONTRATPLUS_CRON_DATE >= dol_now() - 1*60)
{
    dol_htmloutput_mesg($langs->trans('NextTimeCron') . ' : ' . date('d/m/Y H:i', $conf->global->CONTRATPLUS_CRON_DATE), '', 'warning');
}

/* Global configuration */
print load_fiche_titre($langs->trans('ContratPlusInfo'), '', 'title_setup');
print '<form name="ContratPlusInfo">';
print '<table class="border" width="100%">';
print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("AdminManageRuleInvitation").'</td>';
print '</tr>';

// Invoice date start
print '<tr class="impair"><td>'.$langs->trans('ContratPlusDateStart');
print '</td><td>';
$form->select_date($conf->global->CONTRATPLUS_DATE_START, 'CONTRATPLUS_DATE_START', $usehm, $usehm, '0', 'ContratPlusInfo', '1', '');
print '</td></tr>';

// Invoice date end
print '<tr class="pair"><td>'.$langs->trans('ContratPlusDateEnd');
print '</td><td>';
$form->select_date($conf->global->CONTRATPLUS_DATE_END, 'CONTRATPLUS_DATE_END', $usehm, $usehm, '0', 'ContratPlusInfo', '1', '');
print '</td></tr>';

// Invoice creation date
print '<tr class="impair"><td>'.$langs->trans('ContratPlusBillDate');
print '</td><td>';
$form->select_date($conf->global->CONTRATPLUS_BILL_DATE, 'CONTRATPLUS_BILL_DATE', $usehm, $usehm, '0', 'ContratPlusInfo', '1', '');
print '</td></tr>';

// Public note
print '<tr class="pair"><td>'.$langs->trans('ContratPlusBillNote');
print '</td><td>';
$doleditor = new DolEditor('CONTRATPLUS_BILL_NOTE', $conf->global->CONTRATPLUS_BILL_NOTE,
    '', 160, '', '', true, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, 4, 80);
$doleditor->Create();
print '</td></tr>';

// Private note
print '<tr class="pair"><td>'.$langs->trans('ContratPlusBillNotePrivate');
print '</td><td>';
$doleditor = new DolEditor('CONTRATPLUS_BILL_NOTE_PRIVATE', $conf->global->CONTRATPLUS_BILL_NOTE_PRIVATE,
    '', 160, '', '', true, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, 4, 80);
$doleditor->Create();
print '</td></tr>';

// Bank account
print '<tr class="impair"><td>'.$langs->trans('ContratplusCompteDefaut');
print '</td><td>';
$form->select_comptes($conf->global->CONTRATPLUS_COMPTE_DEFAUT, 'CONTRATPLUS_COMPTE_DEFAUT', 0, '', 1);
print '</td></tr>';

// Default payment confition
print '<tr class="impair"><td>'.$langs->trans('ContratplusCondPaiementDefaut');
print '</td><td>';
$form->select_conditions_paiements($conf->global->CONTRATPLUS_COND_PAIEMENT_DEFAUT, 'CONTRATPLUS_COND_PAIEMENT_DEFAUT', 0, '', 1);
print '</td></tr>';

// Default payment method
print '<tr class="impair"><td>'.$langs->trans('ContratplusMethodPaiementDefaut');
print '</td><td>';
$form->select_types_paiements($conf->global->CONTRATPLUS_TYPE_PAIEMENT_DEFAUT, 'CONTRATPLUS_TYPE_PAIEMENT_DEFAUT', 0, '', 1);
print '</td></tr>';

// Customer PDF model
print '<tr><td>' . $langs->trans('ModelPDFCustomer') . '</td>';
print '<td colspan="2">';
include_once DOL_DOCUMENT_ROOT . '/core/modules/facture/modules_facture.php';
$liste = ModelePDFFactures::liste_modeles($db);
$model = isset($conf->global->CONTRATPLUS_MODEL_CUSTOMER) ? $conf->global->CONTRATPLUS_MODEL_CUSTOMER : $conf->global->FACTURE_ADDON_PDF;
print $form->selectarray('multimodelcustomer', $liste, $model);
print "</td></tr>";

// Supplier PDF model
if ($conf->global->CONTRATPLUS_SUPPLIER_CONTRACT)
{
    print '<tr><td>' . $langs->trans('ModelPDFSupplier') . '</td>';
    print '<td colspan="2">';
    include_once DOL_DOCUMENT_ROOT.'/core/modules/supplier_invoice/modules_facturefournisseur.php';
    $liste = ModelePDFSuppliersInvoices::liste_modeles($db);
    $model = isset($conf->global->CONTRATPLUS_MODEL_SUPPLIER) ?
        $conf->global->CONTRATPLUS_MODEL_SUPPLIER : $conf->global->INVOICE_SUPPLIER_ADDON_PDF;
    print $form->selectarray('multimodelsupplier', $liste, $model);
    print "</td></tr>";
}


// Masquage group
if ($conf->global->CONTRATPLUS_BYPASS == 1) {
    $checkedYes='checked="checked"';
    $checkedNo='';
} else {
    $checkedYes='';
    $checkedNo='checked="checked"';
}

// Bypass
print '<tr><td colspan="">'.$langs->trans('contratplus_bypass');
print '</td>';
print '<td>';
print '<input type="radio" id="contratplus_bypass_confirm" name="contratplus_bypass" value="1" '.$checkedYes.'/> <label for="contratplus_bypass_confirm">'.$langs->trans('Yes').'</label>';
print '<br/>';
print '<input type="radio" id="contratplus_bypass_cancel" name="contratplus_bypass" '.$checkedNo.' value="0"/> <label for="contratplus_bypass_cancel">'.$langs->trans('No').'</label>';
print '</td>';
print '</tr>';

print '<input type="hidden" name="action" value="setvar" />';
print '<tr class="pair"><td colspan="3" align="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td>';
print '</table>';
print '</form>';

/* Option configuration */
print '<br />'.load_fiche_titre("Mode renouvellement simple");

print '<form name="update_option">';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>MODE : Choisir un mode de renouvellement</td>';
print '<td>Description</td></tr>';

for ($i = 1; $i < 5; $i++)
{
	($i % 2 == 0) ? print '<tr class="pair"><td>&nbsp;' : print '<tr class="impair"><td>&nbsp;';
	$name = 'Mode '.$i;

	if ($i == $conf->global->CONTRATPLUS_OPTION)
		print '<input type="radio" name="select" value="'.$i.'"" checked="checked"> '.$name.' <br/>';
	else
    {
        $input = '<input type="radio" name="select" value="'.$i.'""';
        /*if ($i == 3)
            $input .= ' disabled';*/
        $input .= '> '.$name.'<br/>';


        print  $input;
    }

    print '</td>';

	print '<td>'.$langs->trans('CPMode'.$i).'</td>';
}
print '</tr>';
print '<tr><td colspan="3">';
print '<input type="hidden" name="action" value="update">';
print '<div align="center"><p><input type="submit" value="'.$langs->trans("Save").'" class="button"></p></div>';
print '</td>';
print '</table>';
print '</form><br />';


/* START Supplier invoice */
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans('SupplierOption').'</td>';
print '</tr>';
print '<tr class="pair">';
print '<td width="90%">' . $langs->trans('ActivateSupplierInvoiceCreation') . '</td>';

if ($supplier != 0)
{
    print '<td><a href="'.$_SERVER['PHP_SELF'].'?action=setsupplier&status=0">';
    print img_picto($langs->trans("Activated"), 'switch_on');
    print '</a></td>';
}
else
{
    print '<td><a href="'.$_SERVER['PHP_SELF'].'?action=setsupplier&status=1">';
    print img_picto($langs->trans("Disabled"), 'switch_off');
    print '</a></td>';
}
print '</tr>';
print '</table><br />';
/* END Supplier invoice */

/* Sepa option */
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans('SepaOption').'</td>';
print '</tr>';
print '<tr class="pair">';
print '<td width="90%">' . $langs->trans('ActivateSepaLink') . '</td>';

if ((int) $conf->global->CONTRATPLUS_SEPA_LINK === 1)
{
    print '<td><a href="'.$_SERVER['PHP_SELF'].'?action=setsepa&status=0">';
    print img_picto($langs->trans("Activated"), 'switch_on');
    print '</a></td>';
}
else
{
    if ($conf->prelevement->enabled) {
        print '<td><a href="'.$_SERVER['PHP_SELF'].'?action=setsepa&status=1">';
        print img_picto($langs->trans("Disabled"), 'switch_off');
        print '</a></td>';
    } else {
        print '<td>';
        print img_picto($langs->trans("DebitModuleNotActivated"), 'switch_off');
        print '</td>';
    }
}
print '</tr>';
print '</table><br />';

// Debug
if ($conf->global->MAIN_FEATURES_LEVEL=='3')
{
    print load_fiche_titre($langs->trans('Debug'), '', 'title_setup');
    print '<table class="noborder" width="100%">';

    print '<tr class="impair"><td>'.$langs->trans('SQL UDP');
    print '</td><td>';
    $sqlupd;
    print '</td></tr>';

    print '<tr class="pair"><td>'.$langs->trans('SQL UPDBYP');
    print '</td><td>';
    $sqlupdbyp;
    print '</td></tr>';

    print '<tr class="impair"><td>'.$langs->trans('SQL SELECT');
    print '</td><td>';
    $sql;
    print '</td></tr>';

    print '<tr class="pair"><td>'.$langs->trans('select');
    print '</td><td>';
    GETPOST('select');
    print '</td></tr>';

    print '<tr class="impair"><td>'.$langs->trans('contratplus_date_start');
    print '</td><td>';
    $contratplus_date_start;
    print '</td></tr>';

    print '<tr class="pair"><td>'.$langs->trans('contratplus_date_end');
    print '</td><td>';
    $contratplus_date_end;
    print '</td></tr>';

    print '<tr class="impair"><td>'.$langs->trans('contratplus_bill_date');
    print '</td><td>';
    $contratplus_bill_date;
    print '</td></tr>';

    print '<tr><td colspan="2">';
    print '<div align="center"><p>' .
        '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?action=create_test">' . $langs->trans("CreateTest") . '</a>' .
        '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?action=populate_date_end">' . $langs->trans("PopulateDateEnd") . '</a>' .
        '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?action=clean_date_end">' . $langs->trans("CleanDateEnd") . '</a>' .
        '</p></div>';
    print '<div align="center"><p></p></div>';
    print '</td>';

    print '</table>';
}

dol_fiche_end();

llxFooter();
