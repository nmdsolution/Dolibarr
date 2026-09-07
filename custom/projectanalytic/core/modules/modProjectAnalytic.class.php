<?php
/* Copyright (C) 2026
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \defgroup   projectanalytic     Module ProjectAnalytic
 * \brief      ProjectAnalytic module descriptor.
 *
 * \file       custom/projectanalytic/core/modules/modProjectAnalytic.class.php
 * \ingroup    projectanalytic
 * \brief      Description and activation file for module ProjectAnalytic
 *
 * This module lets you record costs allocated to a project for
 * management/analytic reporting only ("Dépenses analytiques de projet").
 * These amounts show up in the project Overview tab "Profit" balance but
 * are NEVER read by the Accountancy module (purchases journal, bookkeeping
 * export, FEC): they cannot create a duplicate accounting entry for a cost
 * that was already booked globally on a regular, non-project-linked
 * supplier invoice (e.g. bulk stock purchases later partly consumed by a
 * specific project).
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module ProjectAnalytic
 */
class modProjectAnalytic extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $conf;
		$this->db = $db;

		// Id for module (must be unique.) TODO Change it if it collides with another module already installed
		// on this instance (Home > Configuration > Modules > List, or table llx_const 'MAIN_MODULE_xxx').
		$this->numero = 500000;

		$this->rights_class = 'projectanalytic';

		$this->family = "projects";

		$this->module_position = '91';

		$this->name = preg_replace('/^mod/i', '', get_class($this));

		$this->description = "ProjectAnalyticDescription";
		$this->descriptionlong = "ProjectAnalyticDescriptionLong";

		$this->editor_name = 'Custom';
		$this->editor_url = '';

		$this->version = '1.0';

		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

		$this->picto = 'fa-money-bill-alt';

		$this->module_parts = array(
			'triggers' => 0,
			'login' => 0,
			'substitutions' => 0,
			'menus' => 0,
			'tpl' => 0,
			'barcode' => 0,
			'models' => 0,
			'printing' => 0,
			'theme' => 0,
			'css' => array(),
			'js' => array(),
			// Context of the project Overview tab (projet/element.php), see
			// $hookmanager->initHooks(array('projectOverview')) there.
			'hooks' => array('projectOverview'),
			'moduleforexternal' => 0,
		);

		$this->dirs = array();

		$this->config_page_url = array();

		$this->hidden = false;
		$this->depends = array('modProjet');
		$this->requiredby = array();
		$this->conflictwith = array();

		$this->langfiles = array("projectanalytic@projectanalytic");

		$this->phpmin = array(7, 0);
		$this->need_dolibarr_version = array(15, 0);

		$this->warnings_activation = array();
		$this->warnings_activation_ext = array();

		$this->const = array();

		if (!isset($conf->projectanalytic) || !isset($conf->projectanalytic->enabled)) {
			$conf->projectanalytic = new stdClass();
			$conf->projectanalytic->enabled = 0;
		}

		$this->tabs = array();

		$this->dictionaries = array();

		$this->boxes = array();

		$this->cronjobs = array();

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		$this->rights[$r][0] = $this->numero.sprintf("%02d", $r + 1);
		$this->rights[$r][1] = 'Read project analytic expenses';
		$this->rights[$r][4] = 'projectexpense';
		$this->rights[$r][5] = 'read';
		$r++;
		$this->rights[$r][0] = $this->numero.sprintf("%02d", $r + 1);
		$this->rights[$r][1] = 'Create/Update project analytic expenses';
		$this->rights[$r][4] = 'projectexpense';
		$this->rights[$r][5] = 'write';
		$r++;
		$this->rights[$r][0] = $this->numero.sprintf("%02d", $r + 1);
		$this->rights[$r][1] = 'Delete project analytic expenses';
		$this->rights[$r][4] = 'projectexpense';
		$this->rights[$r][5] = 'delete';
		$r++;

		// Main menu entries to add
		$this->menu = array();
		$r = 0;
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=project',
			'type' => 'left',
			'titre' => 'ProjectAnalyticExpenses',
			'mainmenu' => 'project',
			'leftmenu' => 'projectanalytic',
			'url' => '/projectanalytic/list.php',
			'langs' => 'projectanalytic@projectanalytic',
			'position' => 200,
			'enabled' => '$conf->projectanalytic->enabled',
			'perms' => '$user->rights->projectanalytic->projectexpense->read',
			'target' => '',
			'user' => 0,
		);
	}

	/**
	 * Function called when module is enabled.
	 *
	 * @param string $options Options when enabling module ('', 'noboxes')
	 * @return int             1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		$result = $this->_load_tables('/projectanalytic/sql/');
		if ($result < 0) {
			return -1;
		}

		return $this->_init(array(), $options);
	}

	/**
	 * Function called when module is disabled.
	 *
	 * @param string $options Options when disabling module ('', 'noboxes')
	 * @return int             1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		return $this->_remove(array(), $options);
	}
}
