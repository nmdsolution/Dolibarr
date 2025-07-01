<?php
/* Copyright (C) 2005-2014  Laurent Destailleur	    <eldy@users.sourceforge.net>
 * Copyright (C) 2017-2018	Eric GROULT			    <eric@code42.fr>
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
 *  \file       htdocs/core/triggers/interface_90_all_Demo.class.php
 *  \ingroup    core
 *  \brief      Fichier de demo de personalisation des actions du workflow
 *  \remarks    Son propre fichier d'actions peut etre cree par recopie de celui-ci:
 *              - Le nom du fichier doit etre: interface_99_modMymodule_Mytrigger.class.php
 *				                           ou: interface_99_all_Mytrigger.class.php
 *              - Le fichier doit rester stocke dans core/triggers
 *              - Le nom de la classe doit etre InterfaceMytrigger
 *              - Le nom de la propriete name doit etre Mytrigger
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';
require_once DOL_DOCUMENT_ROOT."/contrat/class/contrat.class.php";
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

/**
 *  Class of triggers for demo module
 */
class InterfaceWorkflow extends DolibarrTriggers
{

	public $family = 'demo';
	public $picto = 'technic';
	public $description = "Triggers of this module are empty functions. They have no effect. They are provided for tutorial purpose only.";
	public $version = self::VERSION_DOLIBARR;

	/**
     * Function called when a Dolibarrr business event is done.
	 * All functions "runTrigger" are triggered if file is inside directory htdocs/core/triggers or htdocs/module/code/triggers (and declared)
     *
     * @param string		$action		Event action code
     * @param Object		$object     Object concerned. Some context information may also be provided into array property object->context.
     * @param User		    $user       Object user
     * @param Translate 	$langs      Object langs
     * @param conf		    $conf       Object conf
     * @return int         				<0 if KO, 0 if no triggered ran, >0 if OK
     */
    public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
    {
		// Put here code you want to execute when a Dolibarr business events occurs.
        // Data and type of action are stored into $object and $action

	    switch ($action) {
		    // Users
		    case 'USER_CREATE':
		    case 'USER_MODIFY':
		    case 'USER_NEW_PASSWORD':
		    case 'USER_ENABLEDISABLE':
		    case 'USER_DELETE':
		    case 'USER_SETINGROUP':
		    case 'USER_REMOVEFROMGROUP':

		    case 'USER_LOGIN':
		    case 'USER_LOGIN_FAILED':
		    case 'USER_LOGOUT':

				// Actions
		    case 'ACTION_MODIFY':
		    case 'ACTION_CREATE':
		    case 'ACTION_DELETE':

				// Groups
		    case 'GROUP_CREATE':
		    case 'GROUP_MODIFY':
		    case 'GROUP_DELETE':

				// Companies
		    case 'COMPANY_CREATE':
		    case 'COMPANY_MODIFY':
		    case 'COMPANY_DELETE':

				// Contacts
		    case 'CONTACT_CREATE':
		    case 'CONTACT_MODIFY':
		    case 'CONTACT_DELETE':
		    case 'CONTACT_ENABLEDISABLE':

				// Products
		    case 'PRODUCT_CREATE':
		    case 'PRODUCT_MODIFY':
		    case 'PRODUCT_DELETE':
		    case 'PRODUCT_PRICE_MODIFY':
		    case 'PRODUCT_SET_MULTILANGS':
		    case 'PRODUCT_DEL_MULTILANGS':

				//Stock mouvement
		    case 'STOCK_MOVEMENT':

				//MYECMDIR
		    case 'MYECMDIR_DELETE':
		    case 'MYECMDIR_CREATE':
		    case 'MYECMDIR_MODIFY':

				// Customer orders
		    case 'ORDER_CREATE':
		    case 'ORDER_CLONE':
		    case 'ORDER_VALIDATE':
		    case 'ORDER_DELETE':
		    case 'ORDER_CANCEL':
		    case 'ORDER_SENTBYMAIL':
		    case 'ORDER_CLASSIFY_BILLED':
		    case 'ORDER_SETDRAFT':
		    case 'LINEORDER_INSERT':
		    case 'LINEORDER_UPDATE':
		    case 'LINEORDER_DELETE':

				// Supplier orders
		    case 'ORDER_SUPPLIER_CREATE':
		    case 'ORDER_SUPPLIER_CLONE':
		    case 'ORDER_SUPPLIER_VALIDATE':
		    case 'ORDER_SUPPLIER_DELETE':
		    case 'ORDER_SUPPLIER_APPROVE':
		    case 'ORDER_SUPPLIER_REFUSE':
		    case 'ORDER_SUPPLIER_CANCEL':
		    case 'ORDER_SUPPLIER_SENTBYMAIL':
            case 'ORDER_SUPPLIER_DISPATCH':
		    case 'LINEORDER_SUPPLIER_DISPATCH':
		    case 'LINEORDER_SUPPLIER_CREATE':
		    case 'LINEORDER_SUPPLIER_UPDATE':

				// Proposals
		    case 'PROPAL_CREATE':
		    case 'PROPAL_CLONE':
		    case 'PROPAL_MODIFY':
		    case 'PROPAL_VALIDATE':
		    case 'PROPAL_SENTBYMAIL':
		    case 'PROPAL_CLOSE_SIGNED':
		    case 'PROPAL_CLOSE_REFUSED':
		    case 'PROPAL_DELETE':
		    case 'LINEPROPAL_INSERT':
		    case 'LINEPROPAL_UPDATE':
		    case 'LINEPROPAL_DELETE':

				// SupplierProposal
		    case 'SUPPLIER_PROPOSAL_CREATE':
		    case 'SUPPLIER_PROPOSAL_CLONE':
		    case 'SUPPLIER_PROPOSAL_MODIFY':
		    case 'SUPPLIER_PROPOSAL_VALIDATE':
		    case 'SUPPLIER_PROPOSAL_SENTBYMAIL':
		    case 'SUPPLIER_PROPOSAL_CLOSE_SIGNED':
		    case 'SUPPLIER_PROPOSAL_CLOSE_REFUSED':
		    case 'SUPPLIER_PROPOSAL_DELETE':
		    case 'LINESUPPLIER_PROPOSAL_INSERT':
		    case 'LINESUPPLIER_PROPOSAL_UPDATE':
		    case 'LINESUPPLIER_PROPOSAL_DELETE':

				// Contracts
		    case 'CONTRACT_CREATE':
		    case 'CONTRACT_ACTIVATE':
		    case 'CONTRACT_CANCEL':
		    case 'CONTRACT_CLOSE':
		    case 'CONTRACT_DELETE':
		    case 'LINECONTRACT_INSERT':
                $this->insertEndDate($object);
                break;
		    case 'LINECONTRACT_UPDATE':
		        $this->updateEndDate($object);
                break;
		    case 'LINECONTRACT_DELETE':

				// Bills
		    case 'BILL_CREATE':
		    case 'BILL_CLONE':
		    case 'BILL_MODIFY':
		    case 'BILL_VALIDATE':
		    case 'BILL_UNVALIDATE':
		    case 'BILL_SENTBYMAIL':
		    case 'BILL_CANCEL':
		    case 'BILL_DELETE':
		    case 'BILL_PAYED':
		    case 'LINEBILL_INSERT':
		    case 'LINEBILL_UPDATE':
		    case 'LINEBILL_DELETE':

				//Supplier Bill
		    case 'BILL_SUPPLIER_CREATE':
		    case 'BILL_SUPPLIER_UPDATE':
		    case 'BILL_SUPPLIER_DELETE':
		    case 'BILL_SUPPLIER_PAYED':
		    case 'BILL_SUPPLIER_UNPAYED':
		    case 'BILL_SUPPLIER_VALIDATE':
		    case 'BILL_SUPPLIER_UNVALIDATE':
		    case 'LINEBILL_SUPPLIER_CREATE':
		    case 'LINEBILL_SUPPLIER_UPDATE':
		    case 'LINEBILL_SUPPLIER_DELETE':

				// Payments
		    case 'PAYMENT_CUSTOMER_CREATE':
		    case 'PAYMENT_SUPPLIER_CREATE':
		    case 'PAYMENT_ADD_TO_BANK':
		    case 'PAYMENT_DELETE':

				// Online
		    case 'PAYMENT_PAYBOX_OK':
		    case 'PAYMENT_PAYPAL_OK':

				// Donation
		    case 'DON_CREATE':
		    case 'DON_UPDATE':
		    case 'DON_DELETE':

				// Interventions
		    case 'FICHINTER_CREATE':
		    case 'FICHINTER_MODIFY':
		    case 'FICHINTER_VALIDATE':
		    case 'FICHINTER_DELETE':
		    case 'LINEFICHINTER_CREATE':
		    case 'LINEFICHINTER_UPDATE':
		    case 'LINEFICHINTER_DELETE':

				// Members
		    case 'MEMBER_CREATE':
		    case 'MEMBER_VALIDATE':
		    case 'MEMBER_SUBSCRIPTION':
		    case 'MEMBER_MODIFY':
		    case 'MEMBER_NEW_PASSWORD':
		    case 'MEMBER_RESILIATE':
		    case 'MEMBER_DELETE':

				// Categories
		    case 'CATEGORY_CREATE':
		    case 'CATEGORY_MODIFY':
		    case 'CATEGORY_DELETE':
		    case 'CATEGORY_SET_MULTILANGS':

				// Projects
		    case 'PROJECT_CREATE':
		    case 'PROJECT_MODIFY':
		    case 'PROJECT_DELETE':

				// Project tasks
		    case 'TASK_CREATE':
		    case 'TASK_MODIFY':
		    case 'TASK_DELETE':

				// Task time spent
		    case 'TASK_TIMESPENT_CREATE':
		    case 'TASK_TIMESPENT_MODIFY':
		    case 'TASK_TIMESPENT_DELETE':

				// Shipping
		    case 'SHIPPING_CREATE':
		    case 'SHIPPING_MODIFY':
		    case 'SHIPPING_VALIDATE':
		    case 'SHIPPING_SENTBYMAIL':
		    case 'SHIPPING_BILLED':
		    case 'SHIPPING_CLOSED':
		    case 'SHIPPING_REOPEN':
		    case 'SHIPPING_DELETE':
		        dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
			    break;
	    }

        return 0;
	}

    /**
     * Return a serv_duree from an index
     *
     * @param   int     $index      Index of a serv_duree
     * @return  string              null on error, value of the index on success
     */
    private function getServDureeFromIndex($index)
    {
        global $db;

        $extrafield = new ExtraFields($db);

        $extrafield->fetch_name_optionals_label('contratdet');

        foreach ($extrafield->attribute_label as $key => $val)
        {
            if ($key == 'serv_duree')
                return $extrafield->attribute_param[$key]['options'][$index];
        }
        return null;
    }

    /**
     * Redirect to the view page of facture.php or card.php after a call to the trigger
     *
     * @return  void
     */
    private function redirectAfterModif()
    {
        if (GETPOST('id') != null)
            header("Location: ".$_SERVER['PHP_SELF']."?id=".GETPOST('id'));
        else if (GETPOST('facid') != null)
            header("Location: ".$_SERVER['PHP_SELF']."?facid=".GETPOST('facid'));
        else
        {
            dol_syslog("Code 42: " . GETPOST('action') . " => " . $_SERVER["PHP_SELF"], LOG_INFO);
            //header("Location: ".$_SERVER['PHP_SELF']);
        }
    }

    /**
     * Update the end date of subscription according to the duration on DB
     *
     * @param   CommonObject        $object     The object send by runTrigger()
     * @return  void
     */
    private function updateEndDate($object)
    {
        global $db;

        // Recupération de la date de debut
		// Attention, elle peut être au format Y-m-d ou bien timestamp, faire le test sur les deux.
        $date = date_create_from_format('Y-m-d', $object->array_options['options_serv_date_start']);
        if(!$date){
			$date = date_create_from_format('U', $object->array_options['options_serv_date_start']);
		}
        //dol_syslog('Code 42 : option_serv_date_duree = '.$object->array_options['options_serv_date_start'], LOG_DEBUG);
        //dol_syslog("Code 42 : Created date is " . $date->format('Y-m-d H:i:s'), LOG_DEBUG);

        if ($date) {
            $duree = $this->getServDureeFromIndex($object->array_options['options_serv_duree']);
            if (is_numeric($duree)) {
                $old = clone $date;
                $date->modify('+' . $duree . ' month');
                $sql = 'UPDATE llx_contratdet_extrafields SET serv_date_end = "' . $date->format('Y-m-d H:i:s') . '" WHERE llx_contratdet_extrafields.fk_object = ' . $object->id;
                $db->query($sql);
                /*$sql = 'UPDATE llx_contratdet SET date_fin_validite = "' . $date->format('Y-m-d H:i:s') . '" WHERE llx_contratdet.rowid = ' . $object->id;
                $db->query($sql);*/
                //dol_syslog("Code 42: Add " . $duree . " month to the date", LOG_DEBUG);
                //dol_syslog("Code 42: Start date is " . $old->format('Y-m-d H:i:s') . " and new date is " . $date->format('Y-m-d H:i:s'), LOG_DEBUG);
            }
            $this->redirectAfterModif();
        }
    }

    /**
     * Insert the end date of subscription according to the duration
     *
     * @param   CommonObject        $object     The object send by runTrigger()
     * @return  void
     */
    private function insertEndDate($object)
    {
        global $db;
		//dol_syslog("Code 42 : Trigger-Contraplus - insertEndDate() " . $object->element, LOG_INFO);

        if ($object->element == 'contrat') {
            $contract = new Contrat($db);

            if ($contract->fetch($object->id) > 0)
            {
				//dol_syslog("Code 42 : Fetched contrat " . $object->id, LOG_DEBUG);
                if ($contract->fetch_lines() > 0)
                {
                    foreach ($contract->lines as $key => $line)
                    {
						//dol_syslog("Code 42 : Going tu update line " . $line->id, LOG_DEBUG);
                        $this->updateEndDate($line);
                    }
                }
            }
            else
                dol_syslog("Code 42: Can't fetch contract with id " . $object->id, LOG_INFO);
        }
    }
}
