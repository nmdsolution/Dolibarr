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

require_once DOL_DOCUMENT_ROOT . '/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/discount.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.facture.class.php';
require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.product.class.php';
require_once DOL_DOCUMENT_ROOT . '/contrat/class/contrat.class.php';
require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
include_once DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php';
include_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

dol_include_once('/contratplus/core/function.php');
dol_include_once('/contratplus/core/agendafunction.php');
dol_include_once('/contratplus/class/logMail.class.php');
dol_include_once('/contratplus/class/customcontrat.class.php');
dol_include_once('/contratplus/lib/contratplus.lib.php');

/**
 *  Class used to Renew
 */
class Renew
{
    public $conf;
    public $user;
    public $db;
    public $log;
    public $contracts = array();
    public $invoices = array();

    /**
     *     __construct
     *
     * @param   Database        $db         The object given by dolibarr global (global $db)
     * @param   Conf            $conf       The object given by dolibarr global (global $conf)
     * @param   User            $user       The object given by dolibarr global (global $user)
     * @return    void
     */
    public function __construct($db, $conf, $user)
    {
        $this->db = $db;
        $this->conf = $conf;
        $this->user = $user;
        $this->log = new logMail();
    }

    /**
     *      Return the list of contract to renew
     *
     *      @return     array                               List of contract
     */
    public function getContracts()
    {
        return $this->contracts;
    }

    /**
     *      Add a contract to the contract list
     *
     *      @param      Contrat         $contract           Contract to add
     *      @return     int                                 < 0 on error, > 0 on success
     */
    public function addContract($contract)
    {
        if (!isset($contract))
            return -1;

        if (empty($contract->lines))
            $contract->fetchLines();

        if (empty($contract->thirparty))
            $contract->fetch_thirdparty();

        $this->contracts[] =  $contract;
        return 1;
    }


    /**
     *      Add a contract to the contract list with its id
     *
     *      @param      int         $id                     Id of contract to add
     *      @return     int                                 < 0 on error, > 0 on success
     */
    public function addContractById($id)
    {
        $contract = new CustomContrat($this->db);

        if ($contract->fetch($id) <= 0)
            return -1;

        if (empty($contract->lines))
            $contract->fetchLines();

        if (empty($contract->thirparty))
            $contract->fetch_thirdparty();

        if (empty($contract->array_options))
            $contract->fetch_optionals();

        $this->contracts[] = $contract;
        return 1;
    }

    /**
     *      Add multiple contracts to the contract list with their ids
     *
     *      @param      array         $ids                  Id of multiples contracts to add
     *      @return     int                                 < 0 on error, > 0 on success
     */
    public function addContractByIds($ids)
    {
        if (!isset($ids))
            return -1;

        $err = 0;
        foreach ($ids as $key => $id)
        {
            if ($this->addContractById($id) < 0)
                $err++;
        }
        return $err > 0 ? $err * -1 : 1;
    }

    /**
     *      Reactivate all services of a contract
     *
     *      @param      Contrat     $contract               Contract to reactivate
     *      @return     int                                 < 0 on error, number of services reactivated on success
     */
    public function reactivate($contract)
    {
        if (!isset($contract))
            return -1;

        $reactivated = 0;
        foreach ($contract->lines as $key => $line)
        {
            if ($line->statut < 5)
            {
                $reactivated++;
                $res = $contract->close_line($this->user, $line->id, dol_now());
                if ($res < 0)
                    return $res;

                $res = $contract->active_line($this->user, $line->id, $this->conf->global->CONTRATPLUS_DATE_START,
                    $this->conf->global->CONTRATPLUS_DATE_END, $comment = $this->conf->global->CONTRATPLUS_BILL_NOTE);
                if ($res < 0)
                    return $res;
            }
        }
        return $reactivated;
    }

    /**
     *      Reactivate all services of each contract in $this->contracts array
     *
     *      @return     int                                 < 0 on error, > 0 on success
     */
    public function reactivateAll()
    {
        $err = 0;

        foreach ($this->contracts as $key => $contract)
        {
            if ($this->reactivate($contract) < 0)
                $err++;
        }
        return $err > 0 ? $err * -1 : 1;
    }

    /**
     *      Check if ref_supplier exist on db
     *
     *      @param      int         $ref                ref of the ref_supplier
     *      @return     bool                            true if ref exist, false if ref doesn't exist
     */
    private function refExist($ref)
    {
        $sql = 'SELECT * FROM `llx_facture_fourn` WHERE ref_supplier = ' . $ref;
        $resql = $this->db->query($sql);
        return $this->db->num_rows($resql) > 0 ? true : false;
    }

    /**
     *      Generate a reference number for an invoice (timestamp now() + id)
     *
     *      @param      int         $id                 id of the contract
     *      @return     int                             new reference number
     */
    private function generateRef($id)
    {
        return intval(dol_now()) + $id;
    }

    /**
     *      Get the code, libelle and libelle_facture of a cond reglement
     *
     *      @param      int         $id             Id of cond
     *      @return     array                       null on error, array on success
     */
    private function getCondReglement($id)
    {

    	// If ThirdParty has no default payment condition, use the ContratPlus default one.
    	if($id === null) $id = $this->conf->global->CONTRATPLUS_COND_PAIEMENT_DEFAUT;

        $sql = "SELECT rowid, code, libelle, libelle_facture";
        $sql.= " FROM ".MAIN_DB_PREFIX."c_payment_term";
        $sql.= " WHERE rowid = ".$id;

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $res = $resql->fetch_assoc();
            if ($res){
				return $res;
			}
            else
                return null;
        }
        else
        {
            dol_print_error($this->db);
            return null;
        }
    }

    /**
     *      Get the code of a mode reglement
     *
     *      @param      int         $id             Id of mode
     *      @return     string                      null on error, code on success
     */
    private function getModeReglementCode($id)
    {

		// If ThirdParty has no default payment type, use the ContratPlus default one.
		if($id === null) {
            if (!$this->conf->global->CONTRATPLUS_TYPE_PAIEMENT_DEFAUT)
            {
                echo ("<script LANGUAGE='JavaScript'>
                var url = window.location.protocol.concat('//',window.location.host,'/custom/contratplus/admin/config.php');
                window.alert('Vous n\'avez pas de methode de paiement configuré, veuillez en configurer un pour continuer.');
                window.location.href=url;
                </script>");
            }
		    $id = $this->conf->global->CONTRATPLUS_TYPE_PAIEMENT_DEFAUT;
        }


        $sql = "SELECT id, code";
        $sql.= " FROM ".MAIN_DB_PREFIX."c_paiement";
        $sql.= " WHERE id = ".$id;

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $res = $resql->fetch_assoc();
            if ($res){
				return $res;
			}

            else
                return null;
        }
        else
        {
            dol_print_error($this->db);
            return null;
        }
    }

    /**
     *      Get supplier invoice information for creation based on contract information
     *
     *      @param      Facture     $invoice            Invoice to fetch
     *      @param      Contrat     $contract           Contract used to get information
     *      @return     int                             < 0 on error, > 0 on success
     */
    private function fetchSupplierInvoiceInformation($invoice, $contract)
    {
        if (!isset($invoice, $contract))
            return -1;

        global $langs;

        $cpsubstitution = getContratPlusSubstitution($langs);

        $invoice->origin_id             = $contract->id ;
        $invoice->socid				    = $contract->thirdparty->id ;
        $invoice->date           	    = $this->conf->global->CONTRATPLUS_BILL_DATE ;

        $ref_supplier = $this->generateRef($contract->id);
        while ($this->refExist($ref_supplier))
            $ref_supplier = $this->generateRef($contract->id);

        // Substitute value
        $invoice->ref_supplier          = html_entity_decode(make_substitutions($ref_supplier, $cpsubstitution, $langs));

        $invoice->ref_int			    = $contract->thirparty->ref_int ;
        $invoice->modelpdf			    = $contract->thirdparty->modelpdf != '' ? $contract->thirdparty->modelpdf : 'contratplus_supplier';
        $invoice->cond_reglement_id	    = $contract->thirdparty->cond_reglement_id;
        $cond_reglement                 = $this->getCondReglement($contract->thirdparty->cond_reglement_id);
        $invoice->cond_reglement_code   = $cond_reglement['code'];
        $invoice->cond_reglement        = $cond_reglement['libelle'];
        $invoice->cond_reglement_doc    = $cond_reglement['libelle_facture'];
        $invoice->mode_reglement_id	    = $contract->thirdparty->mode_reglement_id;
        $invoice->mode_reglement_code   = $this->getModeReglementCode($contract->thirdparty->mode_reglement_id);
        $invoice->remise_absolue	    = $contract->remise_absolue ;
        $invoice->remise_percent	    = $contract->remise_percent ;
        $invoice->fk_incoterms 		    = $contract->fk_incoterms ;
        $invoice->location_incoterms    = $contract->fk_incoterms ;
        $invoice->date_lim_reglement    = $invoice->calculate_date_lim_reglement();
        $invoice->fk_account            = $contract->array_options['options_contratplus_bank'] != '' ?
            $contract->array_options['options_contratplus_bank'] :
            $this->conf->global->CONTRATPLUS_COMPTE_DEFAUT ;
        $invoice->note_public		    = isset($contract->thirdparty->note_public) ?
            $contract->thirdparty->note_public . '<br />' : '';
        $invoice->note_public           .= isset($contract->note_public) ? $contract->note_public . '<br />' : '';
        $invoice->note_public           .= isset($this->conf->global->CONTRATPLUS_BILL_NOTE) ?
            $this->conf->global->CONTRATPLUS_BILL_NOTE . '<br />' : '';
        // Substitute value
        $invoice->note_public           = make_substitutions($invoice->note_public, $cpsubstitution, $langs);


        $invoice->note_private		    = isset($contract->thirdparty->note_private) ?
            $contract->thirdparty->note_private . '<br />' : '';
        $invoice->note_private           .= isset($contract->note_private) ? $contract->note_private . '<br />' : '';
        $invoice->note_private           .= isset($this->conf->global->CONTRATPLUS_BILL_NOTE_PRIVATE) ?
            $this->conf->global->CONTRATPLUS_BILL_NOTE_PRIVATE . '<br />' : '';
        // Substitute value
        $invoice->note_private           = make_substitutions($invoice->note_private, $cpsubstitution, $langs);


        return 1;
    }

    /**
     *      Get invoice information for creation based on contract information
     *
     *      @param      Facture     $invoice            Invoice to fetch
     *      @param      Contrat     $contract           Contract used to get information
     *      @return     int                             < 0 on error, > 0 on success
     */
    private function fetchInvoiceInformation($invoice, $contract)
    {
        if (!isset($invoice, $contract))
            return -1;

        global $langs;

        $cpsubstitution = getContratPlusSubstitution($langs);

        $invoice->origin_id             = $contract->id ;
        $invoice->socid				    = $contract->thirdparty->id ;
        $invoice->date           	    = $this->conf->global->CONTRATPLUS_BILL_DATE ;
        $invoice->ref_client		    = $contract->ref_customer ;
        // Substitute value
        $invoice->ref_client            = html_entity_decode(make_substitutions($invoice->ref_client, $cpsubstitution, $langs));

        $invoice->ref_int			    = $contract->thirparty->ref_int ;
        $invoice->modelpdf			    = $contract->thirdparty->modelpdf != '' ? $contract->thirdparty->modelpdf : 'contratplus_customer';
        //$invoice->cond_reglement_id	    = $this->getCondReglement($contract->thirdparty->cond_reglement_id); //$contract->thirdparty->cond_reglement_id;
        $cond_reglement                 = $this->getCondReglement($contract->thirdparty->cond_reglement_id);
		$invoice->cond_reglement_id		= $cond_reglement['rowid'];
        $invoice->cond_reglement_code   = $cond_reglement['code'];
        $invoice->cond_reglement        = $cond_reglement['libelle'];
        $invoice->cond_reglement_doc    = $cond_reglement['libelle_facture'];

        $mode_reglement					= $this->getModeReglementCode($contract->thirdparty->mode_reglement_id);
        $invoice->mode_reglement_id	    = $mode_reglement['id'];
        $invoice->mode_reglement_code   = $mode_reglement['code'];

        $invoice->remise_absolue	    = $contract->remise_absolue ;
        $invoice->remise_percent	    = $contract->remise_percent ;
        $invoice->fk_incoterms 		    = $contract->fk_incoterms ;
        $invoice->location_incoterms    = $contract->fk_incoterms ;
        $invoice->date_lim_reglement    = $invoice->calculate_date_lim_reglement();
        $invoice->fk_account            = $contract->array_options['options_contratplus_bank'] != '' ?
            $contract->array_options['options_contratplus_bank'] :
            $this->conf->global->CONTRATPLUS_COMPTE_DEFAUT ;
        $invoice->note_public		    = isset($contract->thirdparty->note_public) ?
            $contract->thirdparty->note_public . '<br />' : '';
        $invoice->note_public           .= isset($contract->note_public) ? $contract->note_public . '<br />' : '';
        $invoice->note_public           .= isset($this->conf->global->CONTRATPLUS_BILL_NOTE) ?
            $this->conf->global->CONTRATPLUS_BILL_NOTE . '<br />' : '';
        // Substitute value
        $invoice->note_public           = make_substitutions($invoice->note_public, $cpsubstitution, $langs);

        $invoice->note_private		    = isset($contract->thirdparty->note_private) ?
            $contract->thirdparty->note_private . '<br />' : '';
        $invoice->note_private           .= isset($contract->note_private) ? $contract->note_private . '<br />' : '';
        $invoice->note_private           .= isset($this->conf->global->CONTRATPLUS_BILL_NOTE_PRIVATE) ?
            $this->conf->global->CONTRATPLUS_BILL_NOTE_PRIVATE . '<br />' : '';
        // Substitute value
        $invoice->note_private           = make_substitutions($invoice->note_private, $cpsubstitution, $langs);

        return 1;
    }

    /**
     *      Add all services lines for the supplier invoice based on a contract
     *
     *      @param      Facture         $invoice        Invoice to fetch services
     *      @param      Contrat         $contract       Contract used to get information
     *      @return     int                             < 0 on error, > 0 on success
     */
    public function addLinesToSupplierInvoice($invoice, $contract)
    {
        $lines = $contract->lines;
        if (empty($lines) && method_exists($contract, 'fetchLines'))
        {
            $contract->fetchLines();
            $lines = $contract->lines;
        }

        $num = count($lines);
        for ($i = 0; $i < $num; $i++)
        {
            if ($lines[$i]->statut < 5) {
                $desc = ($lines[$i]->desc ? $lines[$i]->desc : $lines[$i]->libelle);
                $product_type = ($lines[$i]->product_type ? $lines[$i]->product_type : 0);

                $date_start = $this->conf->global->CONTRATPLUS_DATE_START;
                $date_end = $this->conf->global->CONTRATPLUS_DATE_END;

                $result = $invoice->addline(
                    $desc,
                    $lines[$i]->subprice,
                    $lines[$i]->tva_tx,
                    $lines[$i]->localtax1_tx,
                    $lines[$i]->localtax2_tx,
                    $lines[$i]->qty,
                    $lines[$i]->fk_product,
                    $lines[$i]->remise_percent,
                    $date_start,
                    $date_end,
                    0,
                    $lines[$i]->info_bits,
                    'HT',
                    $product_type,
                    $lines[$i]->rang,
                    0,
                    $lines[$i]->array_options,
                    $lines[$i]->fk_unit
                );

                if ($result < 0)
                    return -1;
            }
        }

        $contract->fetchLines();

        return 1;
    }

    /**
     *      Add all services lines for the invoice based on a contract
     *
     *      @param      Facture         $invoice        Invoice to fetch services
     *      @param      Contrat         $contract       Contract used to get information
     *      @return     int                             < 0 on error, > 0 on success
     */
    public function addLinesToInvoice($invoice, $contract)
    {
        if (!isset($invoice, $contract))
            return -1;

        $lines = $contract->lines;
        $fk_parent_line = 0;
        $num = count($lines);
        for ($i = 0; $i < $num; $i++)
        {
            if ($lines[$i]->statut < 5) {
                // Don't add lines with qty 0 when coming from a shipment including all order lines
                if($contract->element == 'shipping' && $this->conf->global->SHIPMENT_GETS_ALL_ORDER_PRODUCTS && $lines[$i]->qty == 0) continue;

                $label=(! empty($lines[$i]->label)?$lines[$i]->label:'');
                $desc=(! empty($lines[$i]->desc)?$lines[$i]->desc:$lines[$i]->libelle);
                if ($invoice->situation_counter == 1) $lines[$i]->situation_percent =  0;

                if ($lines[$i]->subprice < 0)
                {
                    // Negative line, we create a discount line
                    $discount = new DiscountAbsolute($this->db);
                    $discount->fk_soc = $invoice->socid;
                    $discount->amount_ht = abs($lines[$i]->total_ht);
                    $discount->amount_tva = abs($lines[$i]->total_tva);
                    $discount->amount_ttc = abs($lines[$i]->total_ttc);
                    $discount->tva_tx = $lines[$i]->tva_tx;
                    $discount->fk_user = $this->user->id;
                    $discount->description = $desc;
                    $discountid = $discount->create($this->user);
                    if ($discountid > 0) {
                        $invoice->insert_discount($discountid); // This include link_to_invoice
                    } else {
                        setEventMessages($discount->error, $discount->errors, 'errors');
                        break;
                    }
                } else {
                    // Positive line
                    $product_type = ($lines[$i]->product_type ? $lines[$i]->product_type : 0);

                    // Reset fk_parent_line for no child products and special product
                    if (($lines[$i]->product_type != 9 && empty($lines[$i]->fk_parent_line)) || $lines[$i]->product_type == 9) {
                        $fk_parent_line = 0;
                    }

                    // Extrafields
                    if (empty($this->conf->global->MAIN_EXTRAFIELDS_DISABLED) && method_exists($lines[$i], 'fetch_optionals')) {
                        $lines[$i]->fetch_optionals($lines[$i]->rowid);
                        $array_options = $lines[$i]->array_options;
                    }

                    // View third's localtaxes for now
                    $localtax1_tx = get_localtax($lines[$i]->tva_tx, 1, $invoice->client);
                    $localtax2_tx = get_localtax($lines[$i]->tva_tx, 2, $invoice->client);

                    $result = $invoice->addline($desc, $lines[$i]->subprice, $lines[$i]->qty, $lines[$i]->tva_tx, $localtax1_tx,
                        $localtax2_tx, $lines[$i]->fk_product, $lines[$i]->remise_percent, $this->conf->global->CONTRATPLUS_DATE_START,
                        $this->conf->global->CONTRATPLUS_DATE_END, 0, $lines[$i]->info_bits, $lines[$i]->fk_remise_except, 'HT', 0, $product_type,
                        $lines[$i]->rang, $lines[$i]->special_code, $invoice->origin, $lines[$i]->rowid, $fk_parent_line, $lines[$i]->fk_fournprice,
                        $lines[$i]->pa_ht, $label, $array_options, $lines[$i]->situation_percent, $lines[$i]->fk_prev_id, $lines[$i]->fk_unit);

                    // Defined the new fk_parent_line
                    if ($result > 0 && $lines[$i]->product_type == 9) {
                        $fk_parent_line = $result;
                    }
                }
            }
        }

        return 1;
    }

    /**
     * Create createDraftFromSupplierContract
     *
     * @param Contrat $contract Contract uset to create the invoice
     * @return int                             < 0 on error, > 0 on success
     */
    public function createDraftFromSupplierContract($contract)
    {
        $invoice = new FactureFournisseur($this->db);

        if (!isset($contract))
            return -1;

        if ($this->fetchSupplierInvoiceInformation($invoice, $contract) < 0)
            return -2;

        // create invoice
        $res = $invoice->create($this->user);
        if ($res < 0)
            return $res;

        // add service line
        if ($this->addLinesToSupplierInvoice($invoice, $contract) < 0)
            return -3;

        // link contract to invoice
        $res = $invoice->add_object_linked('contrat', $invoice->origin_id);
        if ($res < 0)
            return $res;

        $this->invoices[] = $invoice;
        return 1;
    }

    /**
     *      Create a draft invoice from a contract
     *
     *      @param      Contrat         $contract       Contract used to create the invoice
     *      @return     int                             < 0 on error, > 0 on success
     */
    public function createDraftFromContract($contract)
    {
        $invoice = new Facture($this->db);

        if (!isset($contract))
            return -1;

        if ($this->fetchInvoiceInformation($invoice, $contract) < 0)
            return -2;

        // create invoice
        $res = $invoice->create($this->user, 0, getDateLim($invoice, $this->db));
        if ($res < 0)
            return $res;

        // add service line
        if ($this->addLinesToInvoice($invoice, $contract) < 0)
            return -3;

        // link contract to invoice
        $res = $invoice->add_object_linked('contrat', $invoice->origin_id);
        if ($res < 0)
            return $res;

        $this->invoices[] = $invoice;
        return 1;
    }

    /**
     *      Create draft invoice for each contract in $this->contracts array
     *
     *      @return     int                                 < 0 on error, > 0 on success
     */
    public function createDraftInvoices()
    {
        $err = 0;

        foreach ($this->contracts as $key => $contract)
        {
            if ($this->conf->global->CONTRATPLUS_SUPPLIER_CONTRACT == 1 && $contract->array_options['options_supplier_contract'] == '1') {
                if ($this->createDraftFromSupplierContract($contract) < 0)
                    $err++;
            }
            else {
                if ($this->createDraftFromContract($contract) < 0)
                    $err++;
            }
        }

        return $err > 0 ? $err * -1 : 1;
    }

    /**
     *      Generate PDF for the invoice
     *
     *      @param      CommonObject        $invoice        customer invoice or supplier invoice
     *      @return     int                                 < 0 on error, > 0 on success
     */
    private function generatePDF($invoice)
    {
        global $langs, $conf;

        $ref = dol_sanitizeFileName($invoice->ref);

        if ($invoice->element == 'invoice_supplier')
        {
            // supplier invoice PDF model
            $model = isset($conf->global->CONTRATPLUcreateDraftFromContractS_MODEL_SUPPLIER) ?
                $this->conf->global->CONTRATPLUS_MODEL_SUPPLIER :
                $this->conf->global->INVOICE_SUPPLIER_ADDON_PDF;
        }
        else
        {
            // customer invoice PDF model
            $model = isset($conf->global->CONTRATPLUS_MODEL_CUSTOMER) ?
                $this->conf->global->CONTRATPLUS_MODEL_CUSTOMER :
                $this->conf->global->FACTURE_ADDON_PDF;
        }

        // generate PDF
        $res = $invoice->generateDocument($model, $langs);
        if ($res < 0) {
            dol_print_error($this->db, $res);
            return $res;
        }

        // get generated pdf path
        $res = dol_most_recent_file($this->conf->facture->dir_output . '/' . $ref, preg_quote($ref, '/').'[^\-]+');

        return $res;
    }

    /**
     *      Validate an invoice and generate a pdf for this one
     *
     *      @param      Facture         $invoice            Invoice to validate
     *      @param      int             $key                Key of the invoice in $this->invoices array
     *      @return     int                                 < 0 on error, > 0 on success
     */
    public function validateInvoice($invoice, $key)
    {
        if (!isset($invoice))
            return -1;

        $res = $invoice->validate($this->user);
        dol_syslog('error validate:' . $res, LOG_INFO);
        if ($res < 0)
            return $res;

        $invoice->fetch_lines();

        $res = $this->generatePDF($invoice);
        dol_syslog('error pdf:' . $res, LOG_INFO);
        if ($res < 0) {
            return $res;
        } else {
            if ($this->contracts[$key]->array_options['options_contratplus_sepa_activated'] == '1' &&
                $this->contracts[$key]->array_options['options_supplier_contract'] != '1' &&
                $this->conf->global->CONTRATPLUS_SEPA_LINK) { // Contract linked to the invoice is sepa automated and is not supplier contract
                $invoice->demande_prelevement($this->user, $invoice->total_ttc);
            }

            return $res;
        }
    }

    /**
     *      Validate all invoice in $this->invoices array and generate a pdf for each one
     *
     *      @return     int                                 < 0 on error, > 0 on success
     */
    public function validateInvoices()
    {
        $err = 0;

        foreach ($this->invoices as $key => $invoice)
        {
            if ($this->validateInvoice($invoice, $key) < 0)
                $err++;
        }
        dol_syslog('error:' . $err, LOG_INFO);
        return $err > 0 ? $err * -1 : 1;
    }

    /**
     *      Create a draft invoice from a $this->contracts[0]
     *
     *      @param      int          $option                Option configured by user (1, 2 or 3)
     *      @return     int                                 < 0 on error, > 0 on success
     */
    private function renewSimpleCreateInvoice($option)
    {
        if ($option >= 2)
        {
            if ($this->conf->global->CONTRATPLUS_SUPPLIER_CONTRACT && $this->contracts[0]->array_options['options_supplier_contract'] == '1') {
                // supplier invoice
                if ($this->createDraftFromSupplierContract($this->contracts[0]) < 0)
                    return -1;

                if ($option == 2)
                    header("Location: ".dol_buildpath('/fourn/facture/card.php', 1)."?facid=" . $this->invoices[0]->id);
            } else {
                // customer invoice
                if ($this->createDraftFromContract($this->contracts[0]) < 0)
                    return -1;

                if ($option == 2)
                {
                    if (versioncompare(versiondolibarrarray(), array(6,0,0)) >= 0)
                        header("Location: ".dol_buildpath('/compta/facture/card.php', 1)."?facid=" . $this->invoices[0]->id);
                    else
                        header("Location: ".dol_buildpath('/compta/facture.php', 1)."?facid=" . $this->invoices[0]->id);
                }
            }
        }

        return 1;
    }

    /**
     *      Validate invoice of $this->invoices[0]
     *
     *      @param      int          $option                Option configured by user (0, 1 or 2)
     *      @return     int                                 < 0 on error, > 0 on success
     */
    private function renewSimpleValidateInvoice($option)
    {
        if ($option >= 3)
        {
            if ($this->conf->global->CONTRATPLUS_SUPPLIER_CONTRACT && $this->contracts[0]->array_options['options_supplier_contract'] == '1') {
                // supplier invoice
                if ($this->validateInvoice($this->invoices[0], 0) < 0)
                    return -1;

                if ($option == 3)
                {
                    header("Location: ".dol_buildpath('/fourn/facture/card.php', 1)."?facid=" . $this->invoices[0]->id);
                }
            } else {
                // customer invoice
                if ($this->validateInvoice($this->invoices[0], 0) < 0)
                    return -1;

                if ($option == 3)
                {
                    if (versioncompare(versiondolibarrarray(), array(6,0,0)) >= 0)
                        header("Location: ".dol_buildpath('/compta/facture/card.php', 1)."?facid=" . $this->invoices[0]->id);
                    else
                        header("Location: ".dol_buildpath('/compta/facture.php', 1)."?facid=" . $this->invoices[0]->id);
                }
            }
        }

        return 1;
    }

    /**
     *      Replace substitution var in string from object
     *
     *      @param      string          $str            String to substit
     *      @param      commonObject    $object         Object used to substit string
     *      @return     string
     */
    private function substitString($str, $object)
    {
        global $langs;

        $formmail = new FormMail($this->db);
        $formmail->setSubstitFromObject($object, $langs);
        $substit = $formmail->substit;

        return make_substitutions($str, $substit);
    }

    /**
     *      Redirect after simple mailing
     *
     *      @param      Facture         $invoice        Invoice to redirect to
     *      @return     void
     */
    private function redirectAfterMailing($invoice)
    {
        if ($invoice->element == 'invoice_supplier')
        {
            header("Location: ".dol_buildpath('/fourn/facture/card.php', 1)."?facid=" . $invoice->id);
        }
        else {
            if (versioncompare(versiondolibarrarray(), array(6,0,0)) >= 0)
                header("Location: ".dol_buildpath('/compta/facture/card.php', 1)."?facid=" . $invoice->id);
            else
                header("Location: ".dol_buildpath('/compta/facture.php', 1)."?facid=" . $invoice->id);
        }
    }

    /**
     *      Send invoice to contact by mail
     *
     *      @param      string          $to             Contact to send mail
     *      @param      Facture         $invoice        Invoice to send to contact
     *      @return     int                             -1 mail not setup, -2 mail not send, > 0 on success
     */
    private function sendInvoiceByMail($to, $invoice)
    {
        global $user, $langs;

        // check topic and content configuration
        if ($this->conf->global->CONTRATPLUS_EMAIL_TEMPLATE_TOPIC != '' &&
            $this->conf->global->CONTRATPLUS_EMAIL_TEMPLATE_CONTENT != '')
        {
            $topic = $this->substitString($this->conf->global->CONTRATPLUS_EMAIL_TEMPLATE_TOPIC, $invoice);
            $content = $this->substitString($this->conf->global->CONTRATPLUS_EMAIL_TEMPLATE_CONTENT, $invoice);

            if ($invoice->element == 'invoice_supplier')
                $fileparams = dol_most_recent_file($this->conf->fournisseur->facture->dir_output . '/' .
                    get_exdir($invoice->id, 2,  0,  0,  $invoice,   'invoice_supplier') .
                    $invoice->ref, preg_quote($invoice->ref, '/').'([^\-])+');
            else
                $fileparams = dol_most_recent_file($this->conf->facture->dir_output . '/' . $invoice->ref, preg_quote($invoice->ref, '/').'[^\-]+');

            $file = $fileparams['fullname'];
            $name = $fileparams['name'];

            // send mail
            $mailfile = new CMailFile($topic, $to, $user->lastname . " " . $user->name . "<" . $user->email . ">", $content,
                        array($file), array('application/pdf'), array($name), '', '', 0, 1);
            $res = $mailfile->sendfile();
            if ($res) {
                $trigger_name='BILL_SENTBYMAIL';
                $trackid='inv'.$invoice->id;
                $object = $invoice;

                // Initialisation of datas of object to call trigger
                if (is_object($object))
                {
                    if (empty($actiontypecode)) $actiontypecode='AC_OTH_AUTO'; // Event insert into agenda automatically

                    $object->actiontypecode	= $actiontypecode; // Type of event ('AC_OTH', 'AC_OTH_AUTO', 'AC_XXX'...)
                    $object->trackid        = $trackid;
                    $object->fk_element		= $object->id;
                    $object->elementtype	= $object->element;
                    if (is_array($file) && count($file)>0) {
                        $object->attachedfiles	= $file;
                    }
                    $object->email_msgid = $mailfile->msgid;
                    $object->email_from = $user->lastname . " " . $user->name . "<" . $user->email . ">";
                    $object->email_subject = $topic;
                    $object->email_to = $to;
                    $object->email_msgid = $mailfile->msgid;

                    // Call of triggers
                    if (! empty($trigger_name))
                    {
                        include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
                        $interface = new Interfaces($this->db);
                        $result = $interface->run_triggers($trigger_name, $object, $user, $langs, $this->conf);
                        if ($result < 0) {
                            setEventMessages($interface->error, $interface->errors, 'errors');
                        }
                    }
                }
            }

            return $res ? 1 : -2;
        }
        return -1;
    }

    /**
     *      Send invoice by mail if contact default not installed or set
     *
     *      @param      Contrat         $contract           Object of contract related to the invoice
     *      @param      Facture         $invoice            Object of invoice to send by mail
     *      @param      boolean         $all                set true if function called in renew all situation
     *      @return     int                                 < 0 on error, > 0 on success
     */
    private function mailWithoutContactDefault($contract, $invoice, $all = false)
    {
        global $langs, $user;

        // can't mail supplier invoice
        if ($invoice->element == 'invoice_supplier')
        {
            setEventMessages($langs->trans('NoMailForSupplier'), '', 'errors');
            return 1;
        }

        if ($contract->thirdparty->email)
        {
            $res = $this->sendInvoiceByMail($contract->thirdparty->email, $invoice);
            // check error with mailing
            if ($res < 0)
            {
                if ($res == -1)
                {
                    dol_syslog('Code 42: Email information not setup in Contrat Plus', LOG_ERR);
                    $this->log->writeLog('Email information not setup in Contrat Plus', LogMail::ERROR);
                    if (!$all)
                        setEventMessages($langs->trans('MissMailConfig'), '', 'errors');
                }

                if ($res == -2)
                {
                    dol_syslog('Code 42: Email not sent', LOG_ERR);
                    $this->log->writeLog('Email information not sent to ' . $contract->thirdparty->email, LogMail::ERROR);
                    if (!$all)
                        setEventMessages($langs->trans('MailNotSent'), '', 'errors');
                }

                header("Location: ".$_SERVER['PHP_SELF'] . '?id=' . $contract->id);
                return -1;
            }

            // log message
            dol_syslog('Code 42: Email sent to ' . $contract->thirdparty->email, LOG_INFO);
            $topic = $this->substitString($this->conf->global->CONTRATPLUS_EMAIL_TEMPLATE_TOPIC, $invoice);
            $log = $invoice->element == 'invoice_supplier' ? 'Supplier invoice ' : 'Customer invoice ';
            $log .= $invoice->id . " : ";
            $log .= "[" . $topic . "] Email sent to " . $contract->thirdparty->email;
            $log .= " by user " . $user->id;
            $this->log->writeLog($log, LogMail::INFO);

            // create event on agenda
            if (isAgendaInstalled())
                createMailEvent($invoice->socid, $langs->trans('Invoice') . ' ' . $invoice->ref . ' ' . $langs->trans('MailSent'),
                    $langs->trans('MailSentTo') . $contract->thirdparty->email);

            // only set message in renew simple
            if (!$all)
            {
                setEventMessages($langs->trans('MailSentTo') . ' ' . $contract->thirdparty->email, '', 'mesgs');
                $this->redirectAfterMailing($invoice);
            }

            return 1;
        }
        else {
            // can't mail thirparty without mail
            dol_syslog('Code 42: No email set for thirdparty' . $contract->thirdparty->name, LOG_ERR);
            $this->log->writeLog('No email set for thirdparty ' . $contract->thirdparty->name, LogMail::ERROR);
            if (!$all)
                setEventMessages($langs->trans('MailAddressNotSet'), '', 'errors');

            header("Location: ".$_SERVER['PHP_SELF'] . '?id=' . $contract->id);
            return -1;
        }
    }

    /**
     *      Send invoice by mail if contact default is installed
     *
     *      @param      Contrat         $contract           Object of contract related to the invoice
     *      @param      Facture         $invoice            Object of invoice to send by mail
     *      @param      boolean         $all                set true if function called in renew all situation
     *      @return     int                                 < 0 on error, > 0 on success
     */
    private function mailWithContactDefault($contract, $invoice, $all = false)
    {
        global $langs, $user;

        if ($invoice->element == 'invoice_supplier')
        {
            setEventMessages($langs->trans('NoMailForSupplier'), '', 'errors');
            return 1;
        }

        $id = getDefaultContactOf($this->db, intval($contract->thirdparty->id));
        if ($id < 0) {
            return $this->mailWithoutContactDefault($contract, $invoice, $all);
        }

        $contact = new Contact($this->db);
        $contact->fetch($id);
        $res = $this->sendInvoiceByMail($contact->email, $invoice);
        if ($res < 0)
        {
            if ($res == -1)
            {
                dol_syslog('Code 42: Email information not setup in Contrat Plus', LOG_ERR);
                $this->log->writeLog('Email information not setup in Contrat Plus', LogMail::ERROR);
                if (!$all)
                    setEventMessages($langs->trans('MissMailConfig'), '', 'errors');
            }

            if ($res == -2)
            {
                dol_syslog('Code 42: Email not sent', LOG_ERR);
                $this->log->writeLog('Email information not sent to ' . $contact->email, LogMail::ERROR);
                if (!$all)
                    setEventMessages($langs->trans('MailNotSent'), '', 'errors');
            }

            header("Location: ".$_SERVER['PHP_SELF'] . '?id=' . $contract->id);
            return -1;
        }

        // log message
        dol_syslog('Code 42: Email sent to ' . $contact->email, LOG_INFO);
        $topic = $this->substitString($this->conf->global->CONTRATPLUS_EMAIL_TEMPLATE_TOPIC, $invoice);
        $log = $invoice->element == 'invoice_supplier' ? 'Supplier invoice ' : 'Customer invoice ';
        $log .= $invoice->id . " : ";
        $log .= "[" . $topic . "] Email sent to " . $contact->email;
        $log .= " by user " . $user->id;
        $this->log->writeLog($log, LogMail::INFO);

        // create event on agenda
        if (isAgendaInstalled())
            createMailEvent($invoice->socid, $langs->trans('Invoice') . ' ' . $invoice->ref . ' ' . $langs->trans('MailSent'),
                $langs->trans('MailSentTo') . $contact->email);

        if (!$all)
        {
            setEventMessages($langs->trans('MailSentTo') . ' ' . $contact->email, '', 'mesgs');
            $this->redirectAfterMailing($invoice);
        }

        return 1;
    }

    /**
     *      Send invoice by mail after a renew simple
     *
     *      @param      int             $option         Option configured by user (0, 1, 2 or 3)
     *      @return     int                             < 0 on error, > 0 on success
     */
    private function renewSimpleMail($option)
    {
        // Mail only in option 4 & mail need to be configured
        if ($option == 4 && $this->conf->global->CONTRATPLUS_IS_MAIL_CONFIGURED == 1)
        {
            if (isContactDefaultInstalled())
                return $this->mailWithContactDefault($this->contracts[0], $this->invoices[0]);
            else
                return $this->mailWithoutContactDefault($this->contracts[0], $this->invoices[0]);
        }

        return 1;
    }

    /**
     *      Send each invoice by mail after a renew all
     *
     *      @param      int             $option         Option configured by user (1, 2, 3 or 4)
     *      @return     int                             < 0 on error, > 0 on success
     */
    private function renewAllMail($option)
    {
        $err = 0;

        // Mail only in option 4 & mail need to be configured
        if ($option == 4 && $this->conf->global->CONTRATPLUS_IS_MAIL_CONFIGURED == 1)
        {
            $this->log->writeDelimiter(' Mailing All Start ');
            foreach ($this->invoices as $key => $invoice) {
                if (isContactDefaultInstalled())
                {
                    if ($this->mailWithContactDefault($this->contracts[$key], $invoice, true) < 0)
                        $err++;
                }
                else
                {
                    if ($this->mailWithoutContactDefault($this->contracts[$key], $invoice, true) < 0)
                        $err++;
                }
            }
            $this->log->writeDelimiter(' Mailing All End ');
        }

        return $err > 0 ? $err * -1 : 1;
    }

    /**
     *      Renew contract of $id according to $option
     *
     *      @param      int             $option         Option configured by user (1, 2, 3 or 4)
     *      @param      array           $id             Id of contract to renew
     *      @return     int                             < 0 on error (-2 if user don't have rights), > 0 on success
     */
    public function renewSimple($option, $id)
    {
        // Check acl
        if ($this->user->rights->contratplus->renew->simple && $this->conf->global->CONTRATPLUS_IS_CONFIGURED == 1)
        {
            // Add contract to the object
            if ($this->addContractById($id) < 0)
                return -1;

            // Reactivate contract
            $res = $this->reactivate($this->contracts[0]);
            if ($res < 0)
                return -1;

            // Option 1 : redirect
            if ($option == 1 || $res == 0)
            {
                header("Location: ".$_SERVER['PHP_SELF'] . '?id=' . $this->contracts[0]->id);
                return 1;
            }

            // Create draft invoice
            if ($this->renewSimpleCreateInvoice($option) < 0)
                return -1;

            // Validate draft invoice
            if ($this->renewSimpleValidateInvoice($option) < 0)
                return -1;

            // Send invoice by mail
            if ($this->renewSimpleMail($option) < 0)
                return -1;

            return 1;
        }
        else
            return -2;
    }

    /**
     *      Renew all contract of $ids according to $option
     *
     *      @param      int         $option         Option configured by user (1, 2 or 3)
     *      @param      array       $ids            Ids of contract to renew
     *      @return     int                         < 0 on error (-2 if user don't have rights), > 0 on success
     */
    public function renewAll($option, $ids)
    {
        // Check acl
        if ($this->user->rights->contratplus->renew->all && $this->conf->global->CONTRATPLUS_IS_CONFIGURED == 1)
        {
            // Add contract to the object
            if ($this->addContractByIds($ids) < 0)
                return -1;

            // Reactivate contract
            if ($this->reactivateAll() < 0)
                return -1;

            // Option 1 : redirect
            if ($option == 1)
            {
                header("Location: ".$_SERVER['PHP_SELF']);
                return 1;
            }

            // Create draft invoice
            if ($this->createDraftInvoices() < 0)
                return -1;

            // Option 2 : redirect
            if ($option == 2)
            {
                header("Location: ".dol_buildpath('/compta/facture/list.php', 1));
                return 1;
            }

            // Validate draft invoice
            if ($this->validateInvoices() < 0)
                return -1;

            // Option 3 : redirect
            if ($option == 3)
            {
                header("Location: ".dol_buildpath('/compta/facture/list.php', 1));
                return 1;
            }

            // Send invoice by mail
            if ($this->renewAllMail($option) < 0)
                return -1;

            header("Location: ".dol_buildpath('/compta/facture/list.php', 1));
            return 1;
        }
        else
            return -2;
    }

    /**
     *      Renew all contract of $ids according to $option using cron
     *
     *      @param      int         $option         Option configured by user (1, 2 or 3)
     *      @param      array       $ids            Ids of contract to renew
     *      @return     void
     */
    public function renewAllCron($option, $ids)
    {
        $res = $this->addContractByIds($ids);

        if ($res >= 0)
            $res = $this->reactivateAll();
        dol_syslog('res1:' . $res, LOG_INFO);

        if ($res >= 0 && $option >= 2)
            $res = $this->createDraftInvoices();
        dol_syslog('res2:' . $res, LOG_INFO);

        if ($res >= 0 && $option >= 3)
            $res = $this->validateInvoices();
        dol_syslog('res3:' . $res, LOG_INFO);

        if ($res >= 0 && $option >= 4)
            $this->renewAllMail($option);
        dol_syslog('res4:' . $res, LOG_INFO);
    }
}
