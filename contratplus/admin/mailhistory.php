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

/***************************************************
 * INCLUDE
 ***************************************************/
$res = 0;
if (! $res && file_exists("../../main.inc.php")) $res=include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=include "../../../main.inc.php"; // for custom directory

dol_include_once('/contratplus/core/function.php');
dol_include_once('/contratplus/core/automatefunction.php');
dol_include_once('/contratplus/class/mailHistory.class.php');

require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.facture.class.php';
require_once DOL_DOCUMENT_ROOT."/cron/class/cronjob.class.php";

$page = GETPOST('page', 'int');
if (!$page)
    $page = 0 ;
$limit = GETPOST('limit') ? GETPOST('limit', 'int') : $conf->liste_limit;
$offset = $limit * $page ;

/***************************************************
 * VIEW
 *
 * Put here all code to build page
 ****************************************************/

global $db, $conf;

$morejs = array("/contratplus/js/contratPlus.js");
llxHeader('', $langs->trans("ContratPlusSetup"), '', '', '', '', $morejs, '', 0, 0);

$head = generateHeader();

dol_fiche_head($head, 'mailhistory', "Contrat Plus", 0, 'contratplus@contratplus');

// Cron display
$cron = new CronJob($db);

$id = getCronIdByLabel('ContratPlusCron');
$cron->fetch($id);

if ($conf->global->CONTRATPLUS_CRON_DATE != 0 && $cron->status == 1 && $conf->global->CONTRATPLUS_CRON_DATE >= dol_now() - 1*60)
{
    dol_htmloutput_mesg($langs->trans('NextTimeCron') . ' : ' . date('d/m/Y H:i', $conf->global->CONTRATPLUS_CRON_DATE), '', 'warning');
}

//get file path
$path = DOL_DATA_ROOT . '/contratplus/mail.log';
if (!file_exists($path))
{
    $path = DOL_DOCUMENT_ROOT . '/contratplus/docs/mail.log';
    if (!file_exists($path))
        $path = DOL_DOCUMENT_ROOT . '/custom/contratplus/docs/mail.log';
}

/**
 * Recap envoi massif
 */

$lastblock = getLastBlock($path);
$count_lastblock = ($lastblock != null) ? count($lastblock) : 0;

if ($limit > 0 && $limit != $conf->liste_limit) $param.='&limit='.$limit;

print '<form method="POST" action="'. $_SERVER["PHP_SELF"] .'">';

print_barre_liste($langs->trans('LastMassSending'), $page, $_SERVER["PHP_SELF"], $param, '', '', '', $count_lastblock, $count_lastblock, 'title_generic', 0, '', '', $limit);

print '</form>';

print '<table class="noborder tagtable liste">';

print '<tr class="liste_titre">';
print_liste_field_titre('');
print_liste_field_titre($langs->trans("Invoice"));
print_liste_field_titre($langs->trans("FromMail"));
print_liste_field_titre($langs->trans("ToMail"));
print_liste_field_titre($langs->trans("Object"));
print_liste_field_titre($langs->trans("Date"));
print_liste_field_titre($langs->trans("Hour"));
print '</tr>';

$var = true;
$bc = array('class="pair"', 'class="impair"');

for ($i = $offset; $i < $limit + $offset; $i++)
{
    $line = $lastblock[$i];
    if (!$line)
        break;

    $var = !$var;

    // fetch invoice
    $mail = new MailHistory($line);
    if ($mail->supplier)
        $invoice = new FactureFournisseur($db);
    else
        $invoice = new Facture($db);

    $invoice->fetch($mail->id);

    // fetch user sender
    $usermail = new User($db);
    $usermail->fetch($mail->mailfrom);

    print '<tr ' . $bc[$var] . '>';

    if ($mail->supplier)
        print '<td>' . img_object($langs->trans('SupplierInvoice'), "sending") . '</td>';
    else
        print '<td>' . img_object($langs->trans('CustomerInvoice'), "user") . '</td>';

    print '<td>' . $invoice->getNomUrl(1) . '</td>';

    // mail from
    print '<td>' . $usermail->getNomUrl(1). '</td>';

    // mail to
    print '<td>' . $mail->mailto . '</td>';

    // topic
    print '<td>' . $mail->topic .'</td>';

    // date
    print '<td>' . dol_print_date(strtotime($mail->date)) . '</td>';

    //hour
    print '<td>' . dol_print_date(strtotime($mail->date), "%H:%M") . '</td>';

    print '</tr>';
}

print '</table>';

/**
 * Recap derniers envois
 */
print '<br />' . load_fiche_titre($langs->trans('LastSimpleSending'), '', 'title_generic');

print '<table class="noborder tagtable liste">';

print '<tr class="liste_titre">';
print_liste_field_titre('');
print_liste_field_titre($langs->trans("Invoice"));
print_liste_field_titre($langs->trans("FromMail"));
print_liste_field_titre($langs->trans("ToMail"));
print_liste_field_titre($langs->trans("Object"));
print_liste_field_titre($langs->trans("Date"));
print_liste_field_titre($langs->trans("Hour"));
print '</tr>';

$var = true;
$bc = array('class="pair"', 'class="impair"');

$lastmail = getLastMail($path);

foreach ($lastmail as $line)
{
    $var = !$var;
    $mail = new MailHistory($line);

    // fetch invoice
    if ($mail->supplier)
        $invoice = new FactureFournisseur($db);
    else
        $invoice = new Facture($db);

    $invoice->fetch($mail->id);

    // fetch user sender
    $usermail = new User($db);
    $usermail->fetch($mail->mailfrom);

    // print line
    print '<tr ' . $bc[$var] . '>';

    // picto
    if ($mail->supplier)
        print '<td>' . img_object($langs->trans('SupplierInvoice'), "sending") . '</td>';
    else
        print '<td>' . img_object($langs->trans('CustomerInvoice'), "user") . '</td>';

    // invoice name
    print '<td>' . $invoice->getNomUrl(1) . '</td>';

    // mail from
    print '<td>' . $usermail->getNomUrl(1). '</td>';

    // mail to
    print '<td>' . $mail->mailto . '</td>';

    // topic mail
    print '<td>' . $mail->topic .'</td>';

    // date
    print '<td>' . dol_print_date(strtotime($mail->date)) . '</td>';

    // hour
    print '<td>' . dol_print_date(strtotime($mail->date), "%H:%M") . '</td>';

    print '</tr>';
}

print '</table>';
