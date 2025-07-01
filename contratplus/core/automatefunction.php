<?php
/**
 * Created by PhpStorm.
 * User: fabien
 * Date: 06/06/2018
 * Time: 09:26
 */

require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/contrat/class/contrat.class.php';

/**
 * Get checkbox to select all checkbox with class="checkforaction"
 *
 * @return  string
 */
function showCheckboxAllButton()
{
    global $conf;

    $out = '';
    $cssclass = 'checkforaction';
    if (!empty($conf->use_javascript_ajax)) $out .= '<input type="checkbox" id="checkallactions" name="checkallactions" class="checkallactions">';
    $out .= '<script type="text/javascript">
                    $(document).ready(function() {
                        $("#checkallactions").click(function() {
                            if($(this).is(\':checked\')){
                                console.log("We check all");
                                $(".' . $cssclass . '").prop(\'checked\', true);
                            }
                            else
                            {
                                console.log("We uncheck all");
                                $(".' . $cssclass . '").prop(\'checked\', false);
                            }' . "\n";
    $out .= '         });
                    });
                  </script>';

    return $out;
}

/**
 * Check if a contract is selected for automated renew
 *
 * @param   Database        $db             The object given by dolibarr global (global $db)
 * @param   int             $id                 Id of contract to check
 * @return  bool
 */
function isAutomated($db, $id)
{
	$sql = 'SELECT cef.contratplus_automated FROM '.MAIN_DB_PREFIX.'contrat_extrafields as cef WHERE cef.fk_object = '.$id;
	$resql = $db->query($sql);
	$res = $resql->fetch_assoc();
	return $res['contratplus_automated'] == null ? false : true;
}

/**
 * Check if a contract is selected for automated renew (with id)
 *
 * @param   int             $id                 Id of contract to check
 * @return  bool
 */
function isAutomatedId($id)
{
    global $db;

    $sql = "SELECT ce.contratplus_automated AS id FROM llx_contrat_extrafields AS ce WHERE ce.fk_object = " . $id;
    $resql = $db->query($sql);
    $res = $resql->fetch_assoc();
    return $res['id'] == null ? false : true;
}

/**
 * Get ids of Contract in $ids that exist in extrafields
 *
 * @param   array           $ids            Ids of contract to search
 * @return  array                           Ids of contract in $ids that exist in extrafields
 */
function getExistingInExtrafields($ids)
{
    global $db;

    $exist = array();
    $sql = 'SELECT ce.fk_object as id FROM ' . MAIN_DB_PREFIX . 'contrat_extrafields as ce where ce.fk_object in (';
    foreach ($ids as $id)
    {
        $sql .= $id . ', ';
    }
    // remove last coma / space (', ')
    $sql = substr($sql, 0, -2) . ')';
    $resql = $db->query($sql);
    if ($resql)
    {
        foreach ($resql as $key => $res)
        {
            $exist[] = $res['id'];
        }
    }

    return $exist;
}

/**
 * Get ids of Contract in $ids that don't exist in extrafields
 *
 * @param   array           $ids            Ids of contract to search
 * @param   array           $exist          Ids of contract in $ids that exist in extrafields
 * @return  array                           Ids of contract in $ids tha don't exist in extrafields
 */
function getNotExistingInExtrafields($ids, $exist)
{
    $notexist = array();
    foreach ($ids as $id)
    {
        if (!in_array($id, $exist))
            $notexist[] = $id;
    }

    return $notexist;
}

/**
 * Set contract in $notexist as automated
 *
 * @param   array           $notexist       Ids of nonexisting contract to automate
 * @param   timestamp       $now            Current timestamp used for extrafield creation
 * @return  void
 */
function setNotExistingAutomated($notexist, $now)
{
    global $db;

    $sql = "INSERT INTO " . MAIN_DB_PREFIX . "contrat_extrafields (tms, fk_object, import_key, supplier_contract, contratplus_bank, contratplus_automated) VALUES ";
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
function setExistingAutomated($exist)
{
    global $db;

    $sql = 'UPDATE ' . MAIN_DB_PREFIX . 'contrat_extrafields set contratplus_automated = true where fk_object in (';
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
function setAutomated($ids, $now)
{
    $exist = getExistingInExtrafields($ids);
    $notexist = getNotExistingInExtrafields($ids, $exist);
    setExistingAutomated($exist);
    setNotExistingAutomated($notexist, $now);
}

/**
 * Set ids as unselected for automated renew
 *
 * @param   array           $ids            Ids of contract to automate
 * @return  void
 */
function setUnautomated($ids)
{
    global $db;

    $sql = 'UPDATE ' . MAIN_DB_PREFIX . 'contrat_extrafields set contratplus_automated = null where fk_object in (';
    foreach ($ids as $id)
    {
        $sql .= $id . ', ';
    }
    // remove last coma / space (', ')
    $sql = substr($sql, 0, -2) . ')';
    dol_syslog($sql, LOG_DEBUG);
    $db->query($sql);
}

/**
 * Get id of a cronjob from its label
 *
 * @param   string          $label          Label of the cronjob
 * @return  int                             id of cronjob or -1 if not found
 */
function getCronIdByLabel($label)
{
    global $db;

    $sql = 'SELECT rowid FROM ' . MAIN_DB_PREFIX . 'cronjob WHERE label="' . $label . '"';
    $resql = $db->query($sql);
    if (!$resql)
        return -1;

    $res = $resql->fetch_assoc();
    return intval($res['rowid']);
}

/**
 * Return the text to insert in $form->formconfirm() for automate confirmation
 *
 * @param   array           $ids            Ids of contract selected for activate / deactivate
 * @return  string
 */
function getAutomateConfirmation($ids)
{
    global $db, $langs;

    $automated = array();
    $notautomated = array();

    foreach ($ids as $id)
    {
        if (isAutomatedId(intval($id)))
            $automated[] = $id;
        else
            $notautomated[] = $id;
    }
    $text = $langs->trans('AutomateContractConfirmation') . '<div>';

    $text .= '<h5>' . $langs->trans("ContractToActivate") . '</h5>';

    if ($notautomated)
    {
        foreach ($notautomated as $id)
        {
            $contract = new Contrat($db);
            $societe = new Societe($db);

            $contract->fetch($id);
            $societe->fetch($contract->socid);
            $text .= '    - ' . $langs->trans('Contract') . ' ' . $contract->ref . ' ' . $langs->trans('tiers') . ' ' . $societe->name . ';<br/> ';
        }
    }
    else
        $text .= $langs->trans('NoContractToActivate');

    $text .= '<h5>' . $langs->trans("ContractToDeactivate") . '</h5>';
    if ($automated)
    {
        foreach ($automated as $id)
        {
            $contract = new Contrat($db);
            $societe = new Societe($db);

            $contract->fetch($id);
            $societe->fetch($contract->socid);
            $text .= '    - ' . $langs->trans('Contract') . ' ' . $contract->ref . ' ' . $langs->trans('tiers') . ' ' . $societe->name . ';<br/> ';
        }
    }
    else
        $text .= $langs->trans('NoContractToDeactivate');

    $text .= '</div>';

    return $text;
}
