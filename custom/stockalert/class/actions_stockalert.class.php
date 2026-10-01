<?php
/**
 * \file       custom/stockalert/class/actions_stockalert.class.php
 * \ingroup    stockalert
 * \brief      Hooks restricting the native "Stock limit" and "Desired stock" product fields
 *             to users having the "Stock manager" right (stockalert > seuil > write).
 *             Dolibarr core files are left untouched.
 */

/**
 * Class ActionsStockalert
 */
class ActionsStockalert
{
	/** @var DoliDB */
	public $db;

	/** @var string */
	public $error = '';

	/** @var array */
	public $errors = array();

	/** @var array */
	public $results = array();

	/** @var string */
	public $resprints;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Hook doActions on product card (productcard) and product stock tab (stockproductcard).
	 * Blocks the changes of the thresholds when the user is not a stock manager.
	 *
	 * @param  array        $parameters  Hook parameters
	 * @param  CommonObject $object      Product
	 * @param  string       $action      Current action (can be modified)
	 * @param  HookManager  $hookmanager Hook manager
	 * @return int                       0 = continue standard processing
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $user, $langs;

		if (!empty($user->rights->stockalert->seuil->write)) {
			return 0;
		}

		$contexts = explode(':', $parameters['context']);

		// Product card: keep current values of the two fields on create/update
		if (in_array('productcard', $contexts) && in_array($action, array('add', 'update'))) {
			$current = new Product($this->db);
			$seuil = 0;
			$desired = 0;
			if ($action == 'update' && $current->fetch(GETPOST('id', 'int')) > 0) {
				$seuil = $current->seuil_stock_alerte;
				$desired = $current->desiredstock;
			}
			$_POST['seuil_stock_alerte'] = $seuil;
			$_POST['desiredstock'] = $desired;
			return 0;
		}

		// Stock tab: inline edit of the fields and per-warehouse limits
		if (in_array('stockproductcard', $contexts)
			&& in_array($action, array('setseuil_stock_alerte', 'setdesiredstock', 'addlimitstockwarehouse', 'delete_productstockwarehouse'))) {
			$langs->load('stockalert@stockalert');
			setEventMessages($langs->trans('StockAlertNotAllowed'), null, 'errors');
			$action = 'view';
		}

		return 0;
	}
}
