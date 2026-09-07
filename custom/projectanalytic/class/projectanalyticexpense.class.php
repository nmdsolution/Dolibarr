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
 * \file        custom/projectanalytic/class/projectanalyticexpense.class.php
 * \ingroup     projectanalytic
 * \brief       CRUD class for ProjectAnalyticExpense
 *
 * A ProjectAnalyticExpense records a cost allocated to a project for
 * management/analytic reporting only (it feeds the "Profit" balance shown
 * on the project Overview tab). It is intentionally NOT read by any
 * accountancy code (purchases journal, bookkeeping export, FEC...), so it
 * can never create a duplicate accounting entry for a cost that was
 * already booked globally on a regular supplier invoice.
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

class ProjectAnalyticExpense extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'projectanalytic';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'projectanalyticexpense';

	/**
	 * @var string Name of table without prefix where object is stored.
	 */
	public $table_element = 'projectanalytic_expense';

	/**
	 * @var int Does this object support multicompany module ? 1 = test with field entity.
	 */
	public $ismultientitymanaged = 1;

	/**
	 * @var int Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 0;

	/**
	 * @var string Icon
	 */
	public $picto = 'fa-money-bill-alt';

	// BEGIN PROPERTIES
	public $fields = array(
		'rowid'                   => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>-2, 'noteditable'=>1, 'notnull'=>1, 'index'=>1, 'position'=>1, 'comment'=>'Id'),
		'entity'                  => array('type'=>'integer', 'label'=>'Entity', 'enabled'=>1, 'visible'=>0, 'notnull'=>1, 'default'=>1, 'index'=>1, 'position'=>5),
		'fk_projet'               => array('type'=>'integer:Project:projet/class/project.class.php:1', 'label'=>'Project', 'picto'=>'project', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'index'=>1, 'position'=>10, 'css'=>'maxwidth500 widthcentpercentminusxx'),
		'label'                   => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'position'=>20, 'searchall'=>1, 'css'=>'minwidth300', 'cssview'=>'wordbreak'),
		'datep'                   => array('type'=>'date', 'label'=>'Date', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'position'=>30),
		'total_ht'                => array('type'=>'price', 'label'=>'AmountHT', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'default'=>0, 'position'=>40, 'isameasure'=>1),
		'total_ttc'               => array('type'=>'price', 'label'=>'AmountTTC', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'default'=>0, 'position'=>41, 'isameasure'=>1),
		'fk_soc'                  => array('type'=>'integer:Societe:societe/class/societe.class.php:1', 'picto'=>'company', 'label'=>'RealSupplier', 'enabled'=>1, 'visible'=>-1, 'position'=>50, 'notnull'=>-1, 'index'=>1, 'help'=>'ProjectAnalyticExpenseRealSupplierHelp'),
		'fk_facture_fourn_source' => array('type'=>'integer:FactureFournisseur:fourn/class/fournisseur.facture.class.php:1', 'picto'=>'bill', 'label'=>'ProjectAnalyticExpenseSourceInvoice', 'enabled'=>1, 'visible'=>-1, 'position'=>51, 'notnull'=>-1, 'index'=>1, 'help'=>'ProjectAnalyticExpenseSourceInvoiceHelp'),
		'note'                    => array('type'=>'text', 'label'=>'Note', 'enabled'=>1, 'visible'=>3, 'position'=>60),
		'fk_user_creat'           => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'picto'=>'user', 'enabled'=>1, 'visible'=>-2, 'notnull'=>1, 'position'=>510, 'foreignkey'=>'user.rowid'),
		'fk_user_modif'           => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'picto'=>'user', 'enabled'=>1, 'visible'=>-2, 'notnull'=>-1, 'position'=>511),
		'date_creation'           => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>1, 'visible'=>-2, 'notnull'=>1, 'position'=>520),
		'tms'                     => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-2, 'notnull'=>0, 'position'=>521),
	);

	public $rowid;
	public $entity;
	public $fk_projet;
	public $label;
	public $datep;
	public $total_ht;
	public $total_ttc;
	public $fk_soc;
	public $fk_facture_fourn_source;
	public $note;
	public $fk_user_creat;
	public $fk_user_modif;
	public $date_creation;
	public $tms;
	// END PROPERTIES

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;

		if (!isModEnabled('multicompany') && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id  Id object
	 * @param string $ref Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		return $this->fetchCommon($id, $ref);
	}

	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string $sortorder  Sort Order
	 * @param  string $sortfield  Sort field
	 * @param  int    $limit      limit
	 * @param  int    $offset     Offset
	 * @param  array  $filter     Filter array. Example array('customsql'=>"fk_projet = 12")
	 * @param  string $filtermode Filter mode (AND or OR)
	 * @return array|int          int <0 if KO, array of objects if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		$records = array();

		$sql = "SELECT ";
		$sql .= $this->getFieldList('t');
		$sql .= " FROM ".$this->db->prefix().$this->table_element." as t";
		if (!empty($this->ismultientitymanaged)) {
			$sql .= " WHERE t.entity IN (".getEntity($this->element).")";
		} else {
			$sql .= " WHERE 1 = 1";
		}

		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif ($key == 't.fk_projet' || $key == 'fk_projet') {
					$sqlwhere[] = 't.fk_projet = '.((int) $value);
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= " AND (".implode(" ".$filtermode." ", $sqlwhere).")";
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		} else {
			$sql .= $this->db->order('datep', 'DESC');
		}
		if (!empty($limit)) {
			$sql .= $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user      User that deletes
	 * @param bool $notrigger false=launch triggers, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 * Return a link to the object card
	 *
	 * @param  int    $withpicto Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 * @param  string $option    On what the link point to ('nolink', ...)
	 * @return string            String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '')
	{
		global $langs;

		$result = '';
		$label = img_picto('', $this->picto).' <u>'.$langs->trans("ProjectAnalyticExpense").'</u>';
		$label .= '<br><b>'.$langs->trans('Label').':</b> '.$this->label;

		$url = dol_buildpath('/projectanalytic/card.php', 1).'?id='.$this->id;

		$linkstart = '<a href="'.$url.'" title="'.dol_escape_htmltag($label, 1).'" class="classfortooltip">';
		$linkend = '</a>';

		$result .= $linkstart;
		if ($withpicto) {
			$result .= img_object(($label), $this->picto, 'class="paddingright"');
		}
		if ($withpicto != 2) {
			$result .= 'DA-'.$this->id;
		}
		$result .= $linkend;

		return $result;
	}
}
