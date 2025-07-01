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

$res = 0;
if (! $res && file_exists("../../main.inc.php")) $res=include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=include "../../../main.inc.php"; // for custom directory

include_once DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php';
include_once DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php';
require_once DOL_DOCUMENT_ROOT."/cron/class/cronjob.class.php";

dol_include_once('/contratplus/core/function.php');
dol_include_once('/contratplus/core/automatefunction.php');
dol_include_once('/contratplus/core/formconfig.php');

require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

global $db, $conf, $langs, $form, $user;

$model = GETPOST('email_template');
$error = 0;

/***************************************************
 * ACTION
 *
 * Put here all code to build page
 ****************************************************/

if (GETPOST('action') == 'settemplate')
{
    $obj = getEmailTemplate($db, $model);

    if ($obj) {
        $res = dolibarr_set_const($db, 'CONTRATPLUS_EMAIL_TEMPLATE', $obj->label, '', 0, '', $conf->entity);
        if (!$res > 0)
            $error++;
        $res = dolibarr_set_const($db, 'CONTRATPLUS_EMAIL_TEMPLATE_TOPIC', $obj->topic, '', 0, '', $conf->entity);
        if (!$res > 0)
            $error++;
        $res = dolibarr_set_const($db, 'CONTRATPLUS_EMAIL_TEMPLATE_CONTENT', $obj->content, '', 0, '', $conf->entity);
        if (!$res > 0)
            $error++;

        // check contrat plus mail configuration
        if ($conf->global->CONTRATPLUS_IS_MAIL_CONFIGURED == 0)
            dolibarr_set_const($db, 'CONTRATPLUS_IS_MAIL_CONFIGURED', 1, '', 0, '', $conf->entity);
    }
    else
    {
        $res = dolibarr_set_const($db, 'CONTRATPLUS_EMAIL_TEMPLATE', '', '', 0, '', $conf->entity);
        if (!$res > 0)
            $error++;
        $res = dolibarr_set_const($db, 'CONTRATPLUS_EMAIL_TEMPLATE_TOPIC', '', '', 0, '', $conf->entity);
        if (!$res > 0)
            $error++;
        $res = dolibarr_set_const($db, 'CONTRATPLUS_EMAIL_TEMPLATE_CONTENT', '', '', 0, '', $conf->entity);
        if (!$res > 0)
            $error++;
    }
    exit(header("Location: ".$_SERVER['PHP_SELF']));
}

if (GETPOST('action') == 'updatevar')
{
    $res = dolibarr_set_const($db, 'CONTRATPLUS_EMAIL_TEMPLATE', '', '', 0, '', $conf->entity);
    if (!$res > 0)
        $error++;
    $res = dolibarr_set_const($db, 'CONTRATPLUS_EMAIL_TEMPLATE_TOPIC', GETPOST('topic'), '', 0, '', $conf->entity);
    if (!$res > 0)
        $error++;
    $content = GETPOST('content', 'none', 0, '', '', true);
    $res = dolibarr_set_const($db, 'CONTRATPLUS_EMAIL_TEMPLATE_CONTENT', $content, '', 0, '', $conf->entity);
    if (!$res > 0)
        $error++;

    // check contrat plus mail configuration
    if ($conf->global->CONTRATPLUS_IS_MAIL_CONFIGURED == 0)
        dolibarr_set_const($db, 'CONTRATPLUS_IS_MAIL_CONFIGURED', 1, '', 0, '', $conf->entity);

    exit(header("Location: ".$_SERVER['PHP_SELF']));
}

if (GETPOST('action') == 'testmail' && GETPOST('mode') == 'send')
{
    if (GETPOST('cancel') == '')
    {
        $mail = new CMailFile('', '', '', '');
        $fromname = GETPOST('fromname');
        $frommail = GETPOST('frommail');
        $to = GETPOST('sendto');
        $topic = GETPOST('subject');
        $content = GETPOST('message');

        // Send mail
        $mailfile = new CMailFile($topic, $to, $fromname . "<" . $frommail . ">", $content,
            '', '', '', '', '', 0, 1);
        $res = $mailfile->sendfile();
        if ($res)
            setEventMessages($langs->trans('MailSentTo') . ' ' . $to, '', 'mesgs');
        else
            setEventMessages($langs->trans('MailNotSent'), '', 'errors');
    }

    exit(header("Location: ".$_SERVER['PHP_SELF']));
}

/***************************************************
 * VIEW
 *
 * Put here all code to build page
 ****************************************************/

$morejs = array("/contratplus/js/contratPlus.js");
llxHeader('', $langs->trans("ContratPlusSetup"), '', '', '', '', $morejs, '', 0, 0);

$head = generateHeader();

dol_fiche_head($head, 'mailsetup', "Contrat Plus", 0, 'contratplus@contratplus');

// Cron display
$cron = new CronJob($db);

$id = getCronIdByLabel('ContratPlusCron');
$cron->fetch($id);

if ($conf->global->CONTRATPLUS_CRON_DATE != 0 && $cron->status == 1 && $conf->global->CONTRATPLUS_CRON_DATE >= dol_now() - 1*60)
{
    dol_htmloutput_mesg($langs->trans('NextTimeCron') . ' : ' . date('d/m/Y H:i', $conf->global->CONTRATPLUS_CRON_DATE), '', 'warning');
}

print '<form name="set_template">';
print formEmailTemplate($db, $conf->global->CONTRATPLUS_EMAIL_TEMPLATE);
print '</form>';

print '<form name="update_template">';
print '<table class="border" width="100%">';
print '<tr class="impair"><td>'.$langs->trans('Object');
print '</td><td>';
print '<input type="text" size="70" name="topic" value="' . $conf->global->CONTRATPLUS_EMAIL_TEMPLATE_TOPIC . '">';
print '</td></tr>';
print '<tr class="pair"><td>'.$langs->trans('Content');
print '</td><td>';
$doleditor = new DolEditor('content', $conf->global->CONTRATPLUS_EMAIL_TEMPLATE_CONTENT,
    '', 300, '', '', true, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, 4, 80);
$doleditor->Create();
print '</td></tr>';

//print substit variable
print '<tr><td colspan="8">';
print $langs->trans('SubstitVariable') . ' : ';
//get all substit variable
$formmail = new FormMail($db);
$formmail->setSubstitFromObject(null, $langs);
$substit = $formmail->substit;
$first = true;

print '<ul class="column-5">';
foreach ($substit as $key => $sub)
{
    print '<li>' . $key . '</li>';
}
print '</ul>';

print '</td></tr>';

print '<input type="hidden" name="action" value="updatevar">';
print '</table>';
print '<div align="center"><p><input type="submit" value="'.$langs->trans("Modify").'" class="button"></p></div>';
print '</form>';

print '<div class="tabsAction">';
print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=testmail&mode=init">'.$langs->trans("DoTestSendHTML").'</a>';
print '</div>';

if (GETPOST('action') == 'testmail' && GETPOST('mode') == 'init')
{
    // Predifined mail for test
    $testmail = new FormMail($db);
    $testmail->fromname = (isset($conf->global->MAIN_MAIL_EMAIL_FROM) ? $conf->global->MAIN_MAIL_EMAIL_FROM : $user->mail);
    $testmail->frommail = (isset($conf->global->MAIN_MAIL_EMAIL_FROM) ? $conf->global->MAIN_MAIL_EMAIL_FROM : $user->mail);
    $testmail->trackid = 'testmail';
    $testmail->withfromreadonly=0;
    $testmail->withsubstit=0;
    $testmail->withfrom=1;
    $testmail->withto=(! empty($_POST['sendto']) ? $_POST['sendto'] : ($user->email?$user->email : 1));
    $testmail->withtocc = '';
    $testmail->withtopic=(isset($_POST['subject']) ? $_POST['subject'] : $langs->trans("Test"));
    $testmail->withtopicreadonly=0;
    $testmail->withbody=(isset($_POST['message'])?$_POST['message']:$langs->transnoentities("PredefinedMailTestHtml"));
    $testmail->withbodyreadonly=0;
    $testmail->withcancel=1;
    $testmail->withfckeditor = 1;
    // Tableau des parametres complementaires du post
    $testmail->param["action"]="testmail";
    $testmail->param["mode"]="send";
    $testmail->param["returnurl"]=$_SERVER["PHP_SELF"];

    print load_fiche_titre($langs->trans('ContratPlusTestMail'), '', 'title_generic');
    print $testmail->get_form();
}

llxFooter();
