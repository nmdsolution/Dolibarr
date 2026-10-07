<?php
/**
 * \defgroup   supplierinvfields     Module SupplierInvFields
 * \file       custom/supplierinvfields/core/modules/modSupplierInvFields.class.php
 * \ingroup    supplierinvfields
 * \brief      Description and activation file for module SupplierInvFields
 *
 * Adds a "Client" (customer thirdparty) and a "Tag" (customer tags/categories) field
 * on supplier invoices. They are standard extrafields of supplier invoices, so they are
 * available on creation/edition, on the card and as column and search filters in the list.
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module SupplierInvFields
 */
class modSupplierInvFields extends DolibarrModules
{
	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $conf;
		$this->db = $db;

		$this->numero = 500002;
		$this->rights_class = 'supplierinvfields';
		$this->family = "financial";
		$this->module_position = '93';
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		$this->description = "Client and Tag fields on supplier invoices";
		$this->descriptionlong = "Adds a Client and a Tag field on supplier invoices (creation, card and list filters).";
		$this->editor_name = 'Custom';
		$this->editor_url = '';
		$this->version = '1.0';
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->picto = 'supplier_invoice';

		$this->module_parts = array();
		$this->dirs = array();
		$this->config_page_url = array();
		$this->hidden = false;
		$this->depends = array('modFournisseur', 'modCategorie');
		$this->requiredby = array();
		$this->conflictwith = array();
		$this->langfiles = array();
		$this->phpmin = array(7, 0);
		$this->need_dolibarr_version = array(15, 0);
		$this->warnings_activation = array();
		$this->warnings_activation_ext = array();
		$this->const = array();

		if (!isset($conf->supplierinvfields) || !isset($conf->supplierinvfields->enabled)) {
			$conf->supplierinvfields = new stdClass();
			$conf->supplierinvfields->enabled = 0;
		}

		$this->tabs = array();
		$this->dictionaries = array();
		$this->boxes = array();
		$this->cronjobs = array();
		$this->rights = array();
		$this->menu = array();
	}

	/**
	 * Function called when module is enabled. Creates the supplier invoice extrafields.
	 *
	 * @param string $options Options when enabling module ('', 'noboxes')
	 * @return int             1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);
		$extrafields->fetch_name_optionals_label('facture_fourn');

		// Client: link to a thirdparty (optional)
		if (empty($extrafields->attributes['facture_fourn']['type']['client'])) {
			$param = array('options' => array('Societe:societe/class/societe.class.php' => null));
			$extrafields->addExtraField('client', 'Client', 'link', 100, '', 'facture_fourn', 0, 0, '', $param, 1, '', '1');
		}

		// Tag: one or several existing customer tags (categories of type customer = 2)
		if (empty($extrafields->attributes['facture_fourn']['type']['tag'])) {
			$param = array('options' => array('categorie:label:rowid::type=2' => null));
			$extrafields->addExtraField('tag', 'Tag', 'chkbxlst', 101, '', 'facture_fourn', 0, 0, '', $param, 1, '', '1');
		}

		return $this->_init(array(), $options);
	}

	/**
	 * Function called when module is disabled (extrafields and their data are kept).
	 *
	 * @param string $options Options when disabling module ('', 'noboxes')
	 * @return int             1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		return $this->_remove(array(), $options);
	}
}
