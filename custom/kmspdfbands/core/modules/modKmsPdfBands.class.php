<?php
/**
 * \defgroup   kmspdfbands     Module KmsPdfBands
 * \file       custom/kmspdfbands/core/modules/modKmsPdfBands.class.php
 * \ingroup    kmspdfbands
 * \brief      Description and activation file for module KmsPdfBands
 *
 * Switches on the KMS header band (top right) and footer band (bottom, full width)
 * on all PDF documents (invoices, proposals, orders, shipments, supplier documents...).
 * The images and drawing functions are in core/lib/pdf.lib.php and theme/common/kms/.
 * Activation sets MAIN_PDF_KMS_BANDS and the top/bottom PDF margins so that the
 * document content never overlaps the bands. Disabling restores the standard output.
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module KmsPdfBands
 */
class modKmsPdfBands extends DolibarrModules
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

		$this->numero = 500003;
		$this->rights_class = 'kmspdfbands';
		$this->family = "other";
		$this->module_position = '94';
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		$this->description = "KMS header and footer bands on PDF documents";
		$this->descriptionlong = "Prints the KMS top band and bottom band on every PDF document and reserves the room for them in the page margins.";
		$this->editor_name = 'Custom';
		$this->editor_url = '';
		$this->version = '1.0';
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->picto = 'pdf';

		$this->module_parts = array();
		$this->dirs = array();
		$this->config_page_url = array();
		$this->hidden = false;
		$this->depends = array();
		$this->requiredby = array();
		$this->conflictwith = array();
		$this->langfiles = array();
		$this->phpmin = array(7, 0);
		$this->need_dolibarr_version = array(15, 0);
		$this->warnings_activation = array();
		$this->warnings_activation_ext = array();
		$this->const = array();

		if (!isset($conf->kmspdfbands) || !isset($conf->kmspdfbands->enabled)) {
			$conf->kmspdfbands = new stdClass();
			$conf->kmspdfbands->enabled = 0;
		}

		$this->tabs = array();
		$this->dictionaries = array();
		$this->boxes = array();
		$this->cronjobs = array();
		$this->rights = array();
		$this->menu = array();
	}

	/**
	 * Function called when module is enabled.
	 *
	 * @param string $options Options when enabling module ('', 'noboxes')
	 * @return int             1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $conf;
		require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

		// Top band is 24 mm high and bottom band 23.1 mm high: keep the content out of them
		dolibarr_set_const($this->db, 'MAIN_PDF_KMS_BANDS', '1', 'chaine', 0, 'KMS header/footer bands on PDF', $conf->entity);
		dolibarr_set_const($this->db, 'MAIN_PDF_MARGIN_TOP', '28', 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($this->db, 'MAIN_PDF_MARGIN_BOTTOM', '27', 'chaine', 0, '', $conf->entity);

		return $this->_init(array(), $options);
	}

	/**
	 * Function called when module is disabled: standard output is restored.
	 *
	 * @param string $options Options when disabling module ('', 'noboxes')
	 * @return int             1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		global $conf;
		require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

		dolibarr_del_const($this->db, 'MAIN_PDF_KMS_BANDS', $conf->entity);
		dolibarr_del_const($this->db, 'MAIN_PDF_MARGIN_TOP', $conf->entity);
		dolibarr_del_const($this->db, 'MAIN_PDF_MARGIN_BOTTOM', $conf->entity);

		return $this->_remove(array(), $options);
	}
}
