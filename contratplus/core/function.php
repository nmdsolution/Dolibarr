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

require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';

/**
 * Get option the user configured for contratPlus 0, 1 or 2
 *
 * @return  int                          option configured on success
 */
function getOption()
{
    global $conf;

    return $conf->global->CONTRATPLUS_OPTION;
}

/**
 * Check if user have authorisation in function of the option
 *
 * @param   int             $option         option of contrat plus
 * @param   User            $user           user to check authorisation
 * @return  bool                            true if user have, false if he doesn't
 */
function isAuthorize($option, $user)
{
    $rights = false;

    switch ($option)
    {
        case 0:
            if ($user->rights->contrat->lire && $user->rights->contrat->creer
                && $user->rights->contrat->activer && $user->rights->contrat->desactiver)
                $rights = true;

            break;
        case 1:
            if ($user->rights->contrat->lire && $user->rights->contrat->creer
                && $user->rights->contrat->activer && $user->rights->contrat->desactiver
                && $user->rights->facture->lire && $user->rights->facture->creer)
                $rights = true;

            break;
        case 2:
            if ($user->rights->contrat->lire && $user->rights->contrat->creer
                && $user->rights->contrat->activer && $user->rights->contrat->desactiver
                && $user->rights->facture->lire && $user->rights->facture->creer
                && $user->rights->facture->invoice_advance->validate)
                $rights = true;

            break;
        default:
            $rights = true;
            break;
    }

    return $rights;
}

/**
 * Check if contract $id is a supplier contract
 *
 * @param   Database        $db             The object given by dolibarr global (global $db)
 * @param   int             $id             Id of the contract
 * @return  bool                            false if contract is not a supplier contract, true if it is
 */
function isSupplierContract($db, $id)
{
    $sql = "SELECT ce.supplier_contract FROM ".MAIN_DB_PREFIX."contrat_extrafields AS ce WHERE ce.fk_object = " . $id;
    $resql = $db->query($sql);
    $res = $resql->fetch_assoc();
    return $res['supplier_contract'] == null ? false : true;
}

/**
 * Check if contract $id has a private note
 *
 * @param   Database        $db             The object given by dolibarr global (global $db)
 * @param   int             $id             Id of the contract
 * @return  string                          The note on success, false on failure
 */
function contractHasPrivateNote($db, $id)
{
	$sql = "SELECT c.note_private FROM ".MAIN_DB_PREFIX."contrat AS c WHERE c.rowid = ".$id;
	$resql = $db->query($sql);
	$res = $resql->fetch_assoc();
	return $res['note_private'] == null ? false : $res['note_private'];
}

function hasUnclosedCommand($db, $id)
{
	if (isSupplierContract($db, $id)) {
		// For suppliers
		//$ret = hasUnclosedCommandOnThirdParty($db, $id);
		$ret = false;
	} else {
		// For customers
		$ret = hasUnclosedCommandOnCustomer($db, $id);
	}
	return $ret;
}
/**
 * Check if the client's contract has a non closed command
 *
 * @param   Database        $db             The object given by dolibarr global (global $db)
 * @param   int             $id             Id of the contract
 * @return  string                          false if it has not, true if it has
 */
function hasUnclosedCommandOnCustomer($db, $id)
{
	$ret = false;
	$sql = "SELECT c.fk_soc FROM";
	$sql.= " (SELECT fk_soc FROM llx_contrat WHERE rowid = ".$id.") AS c";
	$sql.= " LEFT JOIN llx_commande AS com ON c.fk_soc = com.fk_soc";
	$resql = $db->query($sql);
	$res = $resql->fetch_assoc();
	$sql = "SELECT fk_statut, facture FROM ".MAIN_DB_PREFIX."commande WHERE fk_soc = ".$res['fk_soc'];
	$resql = $db->query($sql);
	foreach ($resql as $key => $val) {
		if ($val['fk_statut'] != '3' || $val['facture'] != '1' && $val) {
			$ret = true;
		}
	}
	return $ret;
}


/**
 * Check if the third partie's contract has a non closed command
 *
 * @param   Database        $db             The object given by dolibarr global (global $db)
 * @param   int             $id             Id of the contract
 * @return  string                          false if it has not, true if it has
 */
function hasUnclosedCommandOnThirdParty($db, $id)
{
	$ret = false;
	$sql = "SELECT c.fk_soc FROM";
	$sql.= " (SELECT fk_soc FROM llx_contrat WHERE rowid = ".$id.") AS c";
	$sql.= " LEFT JOIN llx_commande_fournisseur AS cf ON c.fk_soc = cf.fk_soc";
	$resql = $db->query($sql);
	$res = $resql->fetch_assoc();
	$sql = "SELECT fk_statut, facture FROM ".MAIN_DB_PREFIX."commande_fournisseur WHERE fk_soc = ".$res['fk_soc'];
	$resql = $db->query($sql);
	foreach ($resql as $key => $val) {
		if ($val['fk_statut'] != '3' || $val['facture'] != '1' && $val) {
			$ret = true;
		}
	}
	return $ret;
}

/**
 * Calculate the due date for the invoice payment
 *
 * @param   CommonObject    $object     The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
 * @param   Database        $db         The object given by dolibarr global (global $db)
 * @return  string
 */
function getDateLim($object, $db)
{
    if ($object->cond_reglement_id == "") $object->cond_reglement_id = 1;

    $sql = "SELECT ".MAIN_DB_PREFIX."c_payment_term.nbjour FROM ".MAIN_DB_PREFIX."c_payment_term WHERE ".MAIN_DB_PREFIX."c_payment_term.rowid = ".$object->cond_reglement_id;
    dol_syslog($sql, LOG_DEBUG);
    $res = $db->query($sql);
    $nb = $res->fetch_assoc();

    return $nb != null ? date(strtotime('Y-m-d', $object->date.' + '.$nb['nbjour'].' days')) : dol_now();
}

/**
 *      Get default contact of a society for billing mode
 *
 *      @param      Database        $db         The object given by dolibarr global (global $db)
 *      @param      int             $id         id of the society
 *      @return     int                         -1 on error, id of the contact on success
 */
function getDefaultContactOf($db, $id)
{
    $sql = "SELECT sc.fk_socpeople FROM ".MAIN_DB_PREFIX."societe_contact AS sc INNER JOIN ".MAIN_DB_PREFIX."c_type_contact
            AS t ON t.rowid = sc.fk_c_type_contact WHERE sc.element_id = " .
        $id . " AND t.code = 'BILLING'";
    $resql = $db->query($sql);
    if ($resql)
    {
        $res = $resql->fetch_assoc();
        return empty($res['fk_socpeople']) ? -1 : intval($res['fk_socpeople']);
    }
    else
        return -1;
}

/**
 *      Check if Contact Default module is installed
 *
 *      @return     bool                        true if module is installed, false if it's not
 */
function isContactDefaultInstalled()
{
    global $conf;

    return (! empty($conf->global->MAIN_MODULE_CONTACTDEFAULT)) ? true : false;
}

/**
 *      Check if thirdparty have a mail address or a default contact
 *
 *      @param      Database        $db         The object given by dolibarr global (global $db)
 *      @param      int             $id         id of the society
 *      @return     bool
 */
function haveContactMail($db, $id)
{
    if (isContactDefaultInstalled())
    {
        if (getDefaultContactOf($db, $id) >= 0)
            return true;
    }

    $object = new Societe($db);
    $object->fetch($id);
    if ($object->email)
        return true;

    return false;
}

/**
 *      Generate Header for config tab
 *
 *      @return     array                       Header to send to dol_fiche_head()
 */
function generateHeader()
{
    global $langs, $user;

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath('/contratplus/admin/config.php', 1);
    $head[$h][1] ='<i class="fa fa-wrench" aria-hidden="true"></i> '.$langs->trans("SetupContratPlus");
    $head[$h][2] = 'setup';
    $h++;

    $head[$h][0] = dol_buildpath('/contratplus/admin/configwizard.php', 1);
    $head[$h][1] = $langs->trans("WizardSetup");
    $head[$h][2] = 'wizardsetup';
    $h++;

    $head[$h][0] = dol_buildpath('/contratplus/admin/configpdf.php', 1);
    $head[$h][1] = $langs->trans("PdfSetup");
    $head[$h][2] = 'pdfsetup';
    $h++;

    $head[$h][0] = dol_buildpath('/contratplus/admin/configextra.php', 1);
    $head[$h][1] = $langs->trans("ExtraSetup");
    $head[$h][2] = 'extrasetup';
    $h++;



    if (DOL_VERSION >= 6 && $user->rights->contratplus->renew->auto == 1)
    {
        $head[$h][0] = dol_buildpath('/contratplus/admin/configcron.php', 1);
        $head[$h][1] = $langs->trans("CronSetup");
        $head[$h][2] = 'cronsetup';
        $h++;
    }

    $head[$h][0] = dol_buildpath('/contratplus/admin/configmail.php', 1);
    $head[$h][1] = $langs->trans("MailSetup");
    $head[$h][2] = 'mailsetup';
    $h++;

    $head[$h][0] = dol_buildpath('/contratplus/admin/mailhistory.php', 1);
    $head[$h][1] = $langs->trans("MailHistory");
    $head[$h][2] = 'mailhistory';

    return $head;
}

/**
 *      Generate Header for documentation tab
 *
 *      @return     array                       Header to send to dol_fiche_head()
 */
function generateDocumentationHeader()
{
    global $langs;

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath('/contratplus/documentation.php', 1);
    $head[$h][1] = $langs->trans("Documentation");
    $head[$h][2] = 'documentation';
    $h++;

    $head[$h][0] = dol_buildpath('/contratplus/changelog.php', 1);
    $head[$h][1] = $langs->trans("Changelog");
    $head[$h][2] = 'changelog';

    return $head;
}

/**
 * Return the text to insert in $form->formconfirm() for renewal all confirmation
 *
 * @param   array           $selected   An array of ids selected for renewal
 * @param   Database        $db         The object given by dolibarr global (global $db)
 * @param   Lang            $langs      The object given by dolibarr global (global $langs)
 * @param   Conf            $conf       The object given by dolibarr global (global $conf)
 * @return  string
 */
function getRenewalAllConfirmation($selected, $db, $langs, $conf)
{
    $text = $langs->trans("RenewallAllConfirmation");
    foreach ($selected as $key => $id)
    {
        $contract = new CustomContrat($db);
        $societe = new Societe($db);


        $contract->fetch($id);
        $societe->fetch($contract->socid);

        $text .= '    - Contrat ' . $contract->ref . ' entreprise ' . $societe->name . ': Pour la date du ' .
            date('d/m/Y', $conf->global->CONTRATPLUS_DATE_END) . ';<br/> ';
    }

    return $text;
}

/**
 * Return the id of all contracts in Db
 *
 * @param   Database        $db         The object given by dolibarr global (global $db)
 * @return  array                       Ids of all contract
 */
function getContractIds($db)
{
    $sql = "SELECT ".MAIN_DB_PREFIX."contrat.rowid FROM ".MAIN_DB_PREFIX."contrat";
    $res = $db->query($sql);

    foreach ($res as $key => $contract)
        $ids[] = $contract['rowid'];

    return $ids;
}

/**
 * Retrun the id of all contracts selected in Db
 *
 * @param   Database        $db         The object given by dolibarr global (global $db)
 * @return  array                       Ids of all contract selected
 */
function getContractIdsSelected($db)
{
    $sql = "SELECT ".MAIN_DB_PREFIX."contratplus.value AS 'rowid' FROM ".MAIN_DB_PREFIX."contratplus WHERE ".MAIN_DB_PREFIX."contratplus.name = 'RENEWAL_ID'";
    $res = $db->query($sql);

    foreach ($res as $key => $contract)
        $ids[] = $contract['rowid'];

    return $ids;
}

/**
 * Clear RENEWAL_ID on the table llx_contratplus
 *
 * @param   Database        $db         The object given by dolibarr global (global $db)
 * @return  int                         < 0 on error, > 0 on success
 */
function clearIdContratPlusTable($db)
{
    $sql = "DELETE FROM ".MAIN_DB_PREFIX."contratplus WHERE ".MAIN_DB_PREFIX."contratplus.name='RENEWAL_ID'";
    if ($db->query($sql) == null)
        return -1;
    return 1;
}

/**
 * Return location of config / configmail file
 *
 * @param   bool            $mail       true for configmail.php, false for config.php
 * @return  string                      location of the config file
 */
function getConfigLocation($mail = false)
{
    if (!$mail)
    {
        $url = dol_buildpath('/contratplus/admin/config.php', 1);
    }
    else
    {
        $url = dol_buildpath('/contratplus/admin/configmail.php', 1);
    }

    return $url;
}

/**
 * Get last mail sent by renew all
 *
 * @param   string          $path       path of mail.log file
 * @return  array                       last mail in mail.log sent by renew all
 */
function getLastBlock($path)
{
    if (!file_exists($path))
        return null;

    $file = array_reverse(file($path));
    $lastblock = array();
    $inblock = false;
    foreach($file as $f)
    {
        //delimiteur
        if (strpos($f, '*****') !== false) {
            $inblock = !$inblock;
            if (!$inblock)
                break;
        }
        else if ($inblock)
            $lastblock[] = $f;
    }

    return $lastblock;
}

/**
 * Get $nb last mail sent by renew simple
 *
 * @param   string          $path       path of mail.log file
 * @param   int             $nb         number of mail you want to retrieve (default: 5)
 * @return  array                       last mail in mail.log sent by renew simple
 */
function getLastMail($path, $nb = 5)
{
    if (!file_exists($path))
        return array();

    $file = array_reverse(file($path));
    $lastmail = array();
    $inblock = false;
    foreach($file as $f)
    {
        //delimiteur
        if (strpos($f, '*****') !== false) {
            $inblock = !$inblock;
        }
        else if (!$inblock)
            $lastmail[] = $f;

        if (count($lastmail) == $nb)
            break;
    }

    return $lastmail;
}

/* Close function */

/**
 * Check if a contract is closed
 *
 * @param   int             $id                 Id of contract to check
 * @return  bool
 */
function isCtpClosed($id)
{
    global $db;

    $sql = "SELECT ce.contratplus_closed AS id FROM ".MAIN_DB_PREFIX."contrat_extrafields AS ce WHERE ce.fk_object = " . $id;
    $resql = $db->query($sql);
    $res = $resql->fetch_assoc();
    return $res['id'] == null ? false : true;
}

/**
 * Set contract in $notexist as automated
 *
 * @param   array           $notexist       Ids of nonexisting contract to automate
 * @param   timestamp       $now            Current timestamp used for extrafield creation
 * @return  void
 */
function setNotExistingClosed($notexist, $now)
{
    global $db;

    $sql = "INSERT INTO " . MAIN_DB_PREFIX . "contrat_extrafields (tms, fk_object, import_key, supplier_contract, contratplus_bank, contratplus_closed) VALUES ";
    foreach ($notexist as $id)
    {
        $sql .= "(CURRENT_TIMESTAMP, '" . $id ."', NULL, NULL, NULL, '1'), ";
    }
    // remove last coma / space (', ')
    $sql = substr($sql, 0, -2) . ';';
    dol_syslog($sql, LOG_DEBUG);
    $db->query($sql);
}

/**
 * Set contract in $exist as automated
 *
 * @param   array           $exist      Ids of existing contract to automate
 * @return  void
 */
function setExistingClosed($exist)
{
    global $db;

    $sql = 'UPDATE ' . MAIN_DB_PREFIX . 'contrat_extrafields set contratplus_closed = true where fk_object in (';
    foreach ($exist as $id)
    {
        $sql .= $id . ', ';
    }
    // remove last coma / space (', ')
    $sql = substr($sql, 0, -2) . ')';
    dol_syslog($sql, LOG_DEBUG);
    $db->query($sql);
}

/**
 * Set ids as selected for automated renew
 *
 * @param   array           $ids            Ids of contract to automate
 * @param   Timestamp       $now            Current timestamp used for extrafield creation
 * @return  void
 */
function setClosed($ids, $now)
{
    $exist = getExistingInExtrafields($ids);
    $notexist = getNotExistingInExtrafields($ids, $exist);
    setExistingClosed($exist);
    setNotExistingClosed($notexist, $now);
}

/**
 * Set ids as unselected for automated renew
 *
 * @param   array           $ids            Ids of contract to automate
 * @return  void
 */
function setOpened($ids)
{
    global $db;

    $sql = 'UPDATE ' . MAIN_DB_PREFIX . 'contrat_extrafields set contratplus_closed = null where fk_object in (';
    foreach ($ids as $id)
    {
        $sql .= $id . ', ';
    }
    // remove last coma / space (', ')
    $sql = substr($sql, 0, -2) . ')';
    dol_syslog($sql, LOG_DEBUG);
    $db->query($sql);
}
