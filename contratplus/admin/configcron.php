<?php
/**
 * Created by PhpStorm.
 * User: fabien
 * Date: 06/06/2018
 * Time: 14:36
 */

$res = 0;
if (! $res && file_exists("../../main.inc.php")) $res=include_once "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=include_once "../../../main.inc.php"; // for custom directory
if (! $res) die("Include of main fails");

dol_include_once('/contratplus/core/function.php');
dol_include_once('/contratplus/core/automatefunction.php');
dol_include_once('/core/lib/admin.lib.php');
dol_include_once('/cron/class/cronjob.class.php');

/***************************************************
 * ACTIONS
 ****************************************************/

if (DOL_VERSION < 6) {
    exit(header(header("Location: config.php")));
}

global $conf, $langs, $db, $user;

$cron = new CronJob($db);

$id = getCronIdByLabel('ContratPlusCron');
$cron->fetch($id);
$cron->fetch($id);

if (GETPOST('action') == 'setcronstatus') {
    $cron->status = GETPOST('status');
    $cron->maxrun = 1;
    $ret = $cron->update($user);
    if ($ret > 0)
        setEventMessages($langs->trans('SetupSaved'), '', 'mesgs');
    else
        setEventMessage($langs->trans('Error'), '', 'errors');
}

if (GETPOST('action') == 'setdate') {
    $hour = intval(GETPOST('datehour'));
    $min = ceil(intval(GETPOST('datemin')) / 5) * 5; // rounded to multiple of 5
    // pass to next hour if min > 59
    if ($min > 59)
    {
        $min = 0;
        $hour++;
        if ($hour > 23)
            $hour = 0;
    }

    $date = GETPOST('dateday').'-'.GETPOST('datemonth').'-'.GETPOST('dateyear').' '.$hour.':'.$min;
    dolibarr_set_const($db, 'CONTRATPLUS_CRON_DATE', strtotime($date), 'chaine', 0, '', $conf->entity);
    if ($ret > 0)
        setEventMessages($langs->trans('ReprogramTo') . ' '. GETPOST('date').' '.GETPOST('datehour').':'.GETPOST('datemin'), '', 'mesgs');
    else
        setEventMessage($langs->trans('NotReprogram'), '', 'errors');
}

if (GETPOST('action'))
   exit(header("Location: ".$_SERVER['PHP_SELF']));

/***************************************************
 * VIEW
 *
 * Put here all code to build page
 ****************************************************/

llxHeader();

$head = generateHeader();

dol_fiche_head($head, 'cronsetup', "Contrat Plus", 0, 'contratplus@contratplus');

$langs->load("contratplus@contratplus");

// Cron display
if ($conf->global->CONTRATPLUS_CRON_DATE != 0 && $cron->status == 1 && $conf->global->CONTRATPLUS_CRON_DATE >= dol_now() - 1*60)
{
    dol_htmloutput_mesg($langs->trans('NextTimeCron') . ' : ' . date('d/m/Y H:i', $conf->global->CONTRATPLUS_CRON_DATE), '', 'warning');
}

// Cron display
print load_fiche_titre($langs->trans('CronContratPlus'), '', 'title_setup');

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print_liste_field_titre($langs->trans("Label"), '', '', '', '', 'width="8" align="center"');
print_liste_field_titre($langs->trans("RenewDate"), '', '', '', '', 'width="8" align="center"');
print_liste_field_titre($langs->trans("Frequency"), '', '', '', '', 'width="8" align="center"');
print_liste_field_titre($langs->trans("Status"), '', '', '', '', 'width="8" align="center"');
print '<tr>';
print '<td align="center">'. $cron->label . '</td>';
print '<td align="center">'. date('d/m/Y H:i', $conf->global->CONTRATPLUS_CRON_DATE) . '</td>';
print '<td align="center">'. $cron->frequency . ' / ' .$cron->unitfrequency . '</td>';
if ($cron->status != 0)
{
    print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setcronstatus&status=0">';
    print img_picto($langs->trans("Activated"), 'switch_on');
    print '</a></td>';
}
else
{
    print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setcronstatus&status=1">';
    print img_picto($langs->trans("Disabled"), 'switch_off');
    print '</a></td>';
}
print '</tr>';
print '</table><br />';


// Select all option
print load_fiche_titre($langs->trans('CronOption'), '', 'title_setup');
print '<table class="noborder" width="100%">';

// Choose date
print '<form name="ContratPlusInfo">';
print '<tr class="impair">';
print '<td>'. $langs->trans('ChooseDateForRenew') .'</td>';

print '<td align="right">';
print $form->select_date($conf->global->CONTRATPLUS_CRON_DATE, 'date', 1, 1);
print '<input type="hidden" name="action" value="setdate" />';
print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';
print '</td></tr>';
print '</form>';

print '</table><br />';

llxFooter();
