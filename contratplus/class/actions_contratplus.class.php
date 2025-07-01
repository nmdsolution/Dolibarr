<?php
/* Copyright (C) 2017-2019	Eric GROULT			    <eric@code42.fr>
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

dol_include_once('/compta/facture/class/facture.class.php');
dol_include_once('/compta/facture/class/facture-rec.class.php');
dol_include_once('/compta/paiement/class/paiement.class.php');
dol_include_once('/contratplus/class/renew.class.php');

dol_include_once('/contratplus/core/function.php');
dol_include_once('/contratplus/core/automatefunction.php');

class Actionscontratplus
{
    public $db;
    public $error;
    public $errors=array();

    /**
     * Constuctor
     *
     * @param   Database        $db         The object given by dolibarr global (global $db)
     * @return no
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * ContractCard is Dolibarr hook context of contrat/card.php for doActions hook
     *
     * @param   CommonObject    $object     The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   string          $action     Current action (if set). Generally create or edit or null
     * @param   string          $option     Current option chosen by user
     * @param   Database        $db         The object given by dolibarr global (global $db)
     * @param   User            $user       The object given by dolibarr global (global $user)
     * @return  No
     */
    public function actionContextContractCard($object, $action, $option, $db, $user)
    {
        global $conf, $langs;

        // check bypass
        if ($action == "renewal")
        {
            if ($conf->global->CONTRATPLUS_BYPASS == 1)
                exit(header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $object->id . "&action=confirm_renewal"));
        }

        if ($action == "confirm_renewal")
        {
            $renew = new Renew($db, $conf, $user);
            $renew->renewSimple(intval($option), $object->id);
        }

        if ($action == "close_contract" && $user->rights->contrat->desactiver)
        {
            // Close all lines
            $object->closeAll($user);
            // Set status to 2
            $object->statut = 2;
            if ($object->update($user) < 0)
                setEventMessages($langs->trans('ErrorCloseContract'), '', 'errors');
            else
                setEventMessages($langs->trans('ContractClosed'), '', 'mesgs');

            exit(header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $object->id));
        }

        if ($action == "open_contract" && $user->rights->contrat->activer)
        {
            // Close all lines
            $object->activateAll($user);
            // Set status to 1
            $object->statut = 1;
            if ($object->update($user) < 0)
                setEventMessages($langs->trans('ErrorOpenContract'), '', 'errors');
            else
                setEventMessages($langs->trans('ContractOpened'), '', 'mesgs');
            exit(header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $object->id));
        }
    }

    /**
     * Overloading the doActions function : replacing the parent's function with the one below
     *
     * @param   array()         $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   string          $action        Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     */
    public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $user, $db, $conf;

		// find chosen option
		$option = getOption();

        if (in_array('contractcard', explode(':', $parameters['context'])))
		{
		    $this->actionContextContractCard($object, $action, $option, $db, $user);
		}
		return 0;
	}

    /**
     * ContractCard is Dolibarr hook context of contrat/card.php for addMoreActionsButtons hook
     *
     * @param   CommonObject    $object     The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   string          $action     Current action (if set). Generally create or edit or null
     * @param   Lang            $langs      The object given by dolibarr global (global $langs)
     * @param   Form            $form       The object given by dolibarr global (global $form)
     * @param   Conf            $conf       The object given by dolibarr global (global $conf)
     * @return  No
     */
    public function buttonContextContractCard($object, $action, $langs, $form, $conf)
    {
        global $user, $db;

        if ($action == "renewal")
        {
            // Bypass not activated
            if ($conf->global->CONTRATPLUS_BYPASS == 0) {
                $langs->trans("Contratname", $object->ref);
                $text = $langs->trans("RenewalConfirmation");
                $check = 0;
                $i = 0;
                while ($i < count($object->lines))
                {
                    if ($object->lines[$i]->statut < 5)
                    {
                        if($object->lines[$i]->ref) {
                            $new_text = $langs->trans('contratPlusDetailService', $object->lines[$i]->ref, date("Y-m-d", $conf->global->CONTRATPLUS_DATE_END));
                        } else {
                            $new_text = $langs->trans('contratPlusDetailService', dol_trunc($object->lines[$i]->description, 40), date("Y-m-d", $conf->global->CONTRATPLUS_DATE_END));
                        }
                        $text = $text.$new_text;
                        $check++;
                    }
                    $i++;
                }

                if ($check == 0)
                    $text .= $langs->trans("test");

                print $form->formconfirm($_SERVER['PHP_SELF']."?id=".$object->id, $langs->trans("ConfirmRenewal"), $text, "confirm_renewal", '', 0, 1);
            }
        }

        if ($object->statut == 1 && $object->element == "contrat")
        {
            //Check if mode 4 and not mailable
            if ($conf->global->CONTRATPLUS_OPTION >= 4)
            {
                $mailable = haveContactMail($db, $object->socid);

                if (!$mailable)
                    setEventMessages($langs->trans('NoMailContact'), '', 'errors');
                else if (isSupplierContract($db, $object->id))
                {
                    $mailable = false;
                    setEventMessages($langs->trans('NoMailForSupplier'), '', 'errors');
                }
            }
            else
                $mailable = true;

            // check contrat plus configuration
            $mailConfigured = true;
            if ($conf->global->CONTRATPLUS_IS_MAIL_CONFIGURED == 0 && getOption() >= 4)
                $mailConfigured = false;

            if ($conf->global->CONTRATPLUS_IS_CONFIGURED == 0)
                setEventMessages('<a href="' . getConfigLocation() . '">' . $langs->trans('ContratPlusNotConfigured') . '</a>', '', 'errors');
            else if (!$mailConfigured)
                setEventMessages('<a href="' . getConfigLocation(true) . '">' . $langs->trans('ContratPlusMailNotConfigured') . '</a>', '', 'errors');

            // check authorisation with supplier contract
            $supplier_ok = true;
            if ($conf->global->CONTRATPLUS_SUPPLIER_CONTRACT == 1 && $object->array_options['options_supplier_contract'] == '1')
            {
                $supplier_ok = false;
                if ($user->rights->fournisseur->facture->lire
                    && $user->rights->fournisseur->facture->creer
                    && $user->rights->fournisseur->facture->creer)
                    $supplier_ok = true;
            }

            // Check acl
            if (isAuthorize(getOption(), $user) && $supplier_ok && $user->rights->contratplus->renew->simple && $mailable
                && $conf->global->CONTRATPLUS_IS_CONFIGURED == 1 && $mailConfigured)
                print '<div class="inline-block divButAction"><a class="butAction" href="' .
                    $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=renewal">' .
                    $langs->trans("Renewal").'</a></div>';
            else
                print '<div class="inline-block divButAction"><a class="butActionRefused" href="#">' .
                    $langs->trans("Renewal").'</a></div>';

            // Button close
            if ($object->statut != 2 && $user->rights->contrat->desactiver)
                print '<div class="inline-block divButAction"><a class="butAction" href="' .
                    $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=close_contract">' .
                    $langs->trans("CloseContract").'</a></div>';
        }

        if ($object->element == "contrat") {
            if ($object->statut == 2 && $user->rights->contrat->activer)
                print '<div class="inline-block divButAction"><a class="butAction" href="' .
                    $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=open_contract">' .
                    $langs->trans("OpenContract").'</a></div>';
        }
    }

    /**
     * GlobalCard is Dolibarr hook context of all card.php for addMoreActionsButtons hook
     *
     * @param   CommonObject    $object     The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   Lang            $langs      The object given by dolibarr global (global $langs)
     * @param   Conf            $conf       The object given by dolibarr global (global $conf)
     * @return  void
     */
    public function buttonContextGlobalCard($object, $langs, $conf)
    {
        global $user;

        if ($conf->global->CONTRATPLUS_SUPPLIER_CONTRACT)
        {
            if ($object->next_prev_filter == 'te.fournisseur = 1')
            {
                // Check acl
                if (isAuthorize(getOption(), $user) && $user->rights->fournisseur->facture->lire
                    && $user->rights->fournisseur->facture->creer && $user->rights->contratplus->renew->simple)
                {
                    print '<a class="butAction" href="' .
						dol_buildpath('/contrat/card.php', 1).'?action=create&socid=' . $object->id . '&public_note=' . $object->public_note .
                        '&private_note=' . $object->private_note . '&options_supplier_contract=1">' .
                        $langs->trans("CreateFournContract") . '</a>';
                }
                else
                {
                    print '<a class="butActionRefused" href="#">' .
                        $langs->trans("CreateFournContract") . '</a>';
                }
            }
        }
    }

	/**
	 * Adds more actions buttons.
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...
	 * @param   string          $action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $form, $conf;

		$langs->load("contratplus@contratplus");

		dol_syslog('contratplus - addMoreActionsButtons activation', LOG_DEBUG);

		if (in_array('contractcard', explode(':', $parameters['context'])))
		{
			$this->buttonContextContractCard($object, $action, $langs, $form, $conf);
		}
		if (in_array('suppliercard', explode(':', $parameters['context'])))
        {
            $this->buttonContextGlobalCard($object, $langs, $conf);
        }

		return 0;
	}

    /**
     * Hook called after form creation
     *
     * @param   array()         $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...
     * @param   string          $action        Current action (if set). Generally create or edit or null
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     */
	public function formObjectOptions($parameters, &$object, &$action)
    {
        global $extrafields, $conf;

        if (in_array('contractcard', explode(':', $parameters['context'])))
        {
            if ($conf->global->CONTRATPLUS_SUPPLIER_CONTRACT == 0)
                $extrafields->attribute_hidden['supplier_contract'] = 1;
        }
        return 0;
    }

    /**
     * Hook called during pdf creation creation
     *
     * @param   array()         $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...
     * @param   string          $action        Current action (if set). Generally create or edit or null
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     *
     * @phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
     */
    public function pdf_writelinedesc($parameters, &$object, &$action)
    {
        global $langs;

        $labelproductservice = null;
        $model = GETPOST('model');
        $action = GETPOST('action');

        // Replace only for contratplus pdf model
        if ($action == 'builddoc' && ($model == 'contratplus_customer' || $model == 'contratplus_supplier' ))
        {
            $pdf = $parameters['pdf'];
            $i = $parameters['i'];
            $outputlangs = $parameters['outputlangs'];
            $w = $parameters['w'];
            $h = $parameters['h'];
            $posx = $parameters['posx'];
            $posy = $parameters['posy'];
            $hideref = $parameters['hideref'];
            $hidedesc = $parameters['hidedesc'];
            $issupplierline = $parameters['issupplierline'];
            $special_code = $parameters['special_code'];

            $labelproductservice=pdf_getlinedesc($object, $i, $outputlangs, $hideref, $hidedesc, $issupplierline);

            // Fix bug of some HTML editors that replace links <img src="http://localhostgit/viewimage.php?modulepart=medias&file=image/efd.png" into <img src="http://localhostgit/viewimage.php?modulepart=medias&amp;file=image/efd.png"
            // We make the reverse, so PDF generation has the real URL.
            $labelproductservice = preg_replace('/(<img[^>]*src=")([^"]*)(&amp;)([^"]*")/', '\1\2&\4', $labelproductservice, -1, $nbrep);

            // #202 Replace (Du ... au ...) with Période de facturation (Du ... au ...)
            if (!empty($object->lines[$i]->date_start) || !empty($object->lines[$i]->date_end))
            {
                $format = 'day';

                // Show duration if exists
                if ($object->lines[$i]->date_start && $object->lines[$i]->date_end)
                {
                    $period = '('.$outputlangs->transnoentitiesnoconv('DateFromTo', dol_print_date($object->lines[$i]->date_start, $format, false, $outputlangs), dol_print_date($object->lines[$i]->date_end, $format, false, $outputlangs)).')';
                    $labelproductservice = str_replace($period, $langs->trans('BillPeriod').' '.$period, $labelproductservice);
                }
                if ($object->lines[$i]->date_start && !$object->lines[$i]->date_end)
                {
                    $period = '('.$outputlangs->transnoentitiesnoconv('DateFrom', dol_print_date($object->lines[$i]->date_start, $format, false, $outputlangs)).')';
                    $labelproductservice = str_replace($period, $langs->trans('BillPeriod').' '.$period, $labelproductservice);
                }
                if (!$object->lines[$i]->date_start && $object->lines[$i]->date_end)
                {
                    $period = '('.$outputlangs->transnoentitiesnoconv('DateUntil', dol_print_date($object->lines[$i]->date_end, $format, false, $outputlangs)).')';
                    $labelproductservice = str_replace($period, $langs->trans('BillPeriod').' '.$period, $labelproductservice);
                }
            }

            // Description
            $pdf->writeHTMLCell($w, $h, $posx, $posy, $outputlangs->convToOutputCharset($labelproductservice), 0, 1, false, true, 'J', true);
        }

        return $labelproductservice ? 1 : 0;
    }
}
