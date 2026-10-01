<?php
/**
 * \defgroup   stockalert     Module StockAlert
 * \file       custom/stockalert/core/modules/modStockAlert.class.php
 * \ingroup    stockalert
 * \brief      Description and activation file for module StockAlert
 *
 * Stock alerts, supplier back-orders (reliquats), late deliveries,
 * home widget and PDF summary.
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module StockAlert
 */
class modStockAlert extends DolibarrModules
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

		$this->numero = 500001;
		$this->rights_class = 'stockalert';
		$this->family = "products";
		$this->module_position = '92';
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		$this->description = "StockAlertDescription";
		$this->descriptionlong = "StockAlertDescriptionLong";
		$this->editor_name = 'Custom';
		$this->editor_url = '';
		$this->version = '1.0';
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->picto = 'stock';

		// Hooks restricting the native Stock limit / Desired stock fields to stock managers
		$this->module_parts = array(
			'hooks' => array('productcard', 'stockproductcard'),
		);
		$this->dirs = array();
		$this->config_page_url = array();
		$this->hidden = false;
		$this->depends = array('modProduct', 'modStock', 'modFournisseur');
		$this->requiredby = array();
		$this->conflictwith = array();
		$this->langfiles = array("stockalert@stockalert");
		$this->phpmin = array(7, 0);
		$this->need_dolibarr_version = array(15, 0);
		$this->warnings_activation = array();
		$this->warnings_activation_ext = array();
		$this->const = array();

		if (!isset($conf->stockalert) || !isset($conf->stockalert->enabled)) {
			$conf->stockalert = new stdClass();
			$conf->stockalert->enabled = 0;
		}

		$this->tabs = array();
		$this->dictionaries = array();

		$this->boxes = array(
			0 => array('file' => 'box_stock_alertes.php@stockalert', 'note' => '', 'enabledbydefaulton' => 'Home'),
		);

		$this->cronjobs = array();

		$this->rights = array();
		$r = 0;
		$this->rights[$r][0] = $this->numero.sprintf("%02d", $r + 1);
		$this->rights[$r][1] = 'Read stock alerts report';
		$this->rights[$r][4] = 'report';
		$this->rights[$r][5] = 'read';
		$r++;
		$this->rights[$r][0] = $this->numero.sprintf("%02d", $r + 1);
		$this->rights[$r][1] = 'Set stock alert thresholds (Stock manager)';
		$this->rights[$r][4] = 'seuil';
		$this->rights[$r][5] = 'write';
		$r++;

		$this->menu = array();
		$r = 0;
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=products',
			'type' => 'left',
			'titre' => 'StockAlertReport',
			'mainmenu' => 'products',
			'leftmenu' => 'stockalert',
			'url' => '/stockalert/index.php',
			'langs' => 'stockalert@stockalert',
			'position' => 300,
			'enabled' => '$conf->stockalert->enabled',
			'perms' => '$user->rights->stockalert->report->read',
			'target' => '',
			'user' => 0,
		);
	}

	/**
	 * Function called when module is enabled.
	 * The thresholds are the native product fields "Stock limit" and "Desired stock";
	 * editing them is restricted to the "Stock manager" right by the hooks.
	 *
	 * @param string $options Options when enabling module ('', 'noboxes')
	 * @return int             1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
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
