<?php

require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';

// comm/action/card.php

/**
 *      Check if Agenda module is installed
 *
 *      @return     bool                        true if module is installed, false if it's not
 */
function isAgendaInstalled()
{
    global $conf;

    return (! empty($conf->global->MAIN_MODULE_AGENDA)) ? true : false;
}

/**
 *      Create event in agenda
 *
 *      @param      int         $socid          Id of linked society
 *      @param      string      $label          Label of event
 *      @param      string      $description    Description of event
 *      @return     int                         Id of created event, < 0 if KO
 */
function createMailEvent($socid, $label, $description)
{
    global $db, $user;

    $object = new ActionComm($db);
    $object->type_code = 50;
    $object->priority = 0;
    $object->fulldayevent = 0;
    $object->location = '';
    $object->label = $label;
    $object->percentage = -1;
    $object->datep = dol_now();
    $object->datef = dol_now();

    $object->note = $description;

    $object->socid = $socid;
    $object->fetch_thirdparty();
    $object->societe = $object->thirdparty;
    $object->userownerid = $user->id;

    return $object->create($user);
}
