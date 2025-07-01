<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 * \file        class/c42location.class.php
 * \ingroup     mydevice
 * \brief       This file is a CRUD class file for C42Location (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for C42Location
 */
class C42Location extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'c42location';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'c42location';

	/**
	 * @var int  Does c42location support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does c42location support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for c42location. Must be the part after the 'object_' into object_c42location.png
	 */
	public $picto = 'c42location@h2g2';


	const STATUS_VALIDATED = 1;


	/**
	 *  'type' if the field format ('integer', 'integer:Class:pathtoclass', 'varchar(x)', 'double(24,8)', 'text', 'html', 'datetime', 'timestamp', 'float')
	 *  'label' the translation key.
	 *  'enabled' is a condition when the field must be managed.
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'default' is a default value for creation (can still be replaced by the global setup of default values)
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'position' is the sort order of field.
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'css' is the CSS style to use on field. For example: 'maxwidth200'
	 *  'help' is a string visible as a tooltip on field
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>-1, 'position'=>1, 'notnull'=>1, 'index'=>1, 'comment'=>"Id",),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>1, 'visible'=>-2, 'position'=>500, 'notnull'=>1,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-2, 'position'=>501, 'notnull'=>-1,),
		'fk_user_creat' => array('type'=>'integer', 'label'=>'UserAuthor', 'enabled'=>1, 'visible'=>-2, 'position'=>510, 'notnull'=>1, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer', 'label'=>'UserModif', 'enabled'=>1, 'visible'=>-2, 'position'=>511, 'notnull'=>-1,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>1, 'visible'=>-2, 'position'=>1000, 'notnull'=>-1,),
		'longitude' => array('type'=>'double', 'label'=>'Longitude', 'enabled'=>1, 'visible'=>1, 'position'=>50, 'notnull'=>-1,),
		'latitude' => array('type'=>'double', 'label'=>'Latitude', 'enabled'=>1, 'visible'=>1, 'position'=>50, 'notnull'=>-1,),
		'road_nb' => array('type'=>'integer', 'label'=>'RoadNb', 'enabled'=>1, 'visible'=>1, 'position'=>50, 'notnull'=>-1,),
		'road_name' => array('type'=>'varchar(255)', 'label'=>'RoadName', 'enabled'=>1, 'visible'=>1, 'position'=>50, 'notnull'=>-1,),
		'zip' => array('type'=>'varchar(70)', 'label'=>'Zip', 'enabled'=>1, 'visible'=>1, 'position'=>50, 'notnull'=>-1,),
		'city' => array('type'=>'varchar(255)', 'label'=>'City', 'enabled'=>1, 'visible'=>1, 'position'=>50, 'notnull'=>-1,),
		'country' => array('type'=>'varchar(255)', 'label'=>'Country', 'enabled'=>1, 'visible'=>1, 'position'=>50, 'notnull'=>-1,),
	);
	public $rowid;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $longitude;
	public $latitude;
	public $road_nb;
	public $road_name;
	public $zip;
	public $city;
	public $country;

    private $markerIcon;
    private $locationIcon;
    private $errorIcon;
    private $browsIcon;

	// END MODULEBUILDER PROPERTIES

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;
        $langs->load('h2g2@h2g2');
        $this->markerIcon = dol_buildpath('/h2g2/img/c42location/c42location_marker.png', 1);
        $this->locationIcon = dol_buildpath('/h2g2/img/c42location/c42location_icon.png', 1);
        $this->errorIcon = dol_buildpath('/h2g2/img/c42location/c42location_error.png', 1);
        $this->browsIcon = dol_buildpath('/h2g2/img/c42location/c42location_browsing.png', 1);
        if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible']=0;
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled']=0;

		// Unset fields that are disabled
		foreach($this->fields as $key => $val)
		{
			if (isset($val['enabled']) && empty($val['enabled']))
			{
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		foreach($this->fields as $key => $val)
		{
			if (is_array($val['arrayofkeyval']))
			{
				foreach($val['arrayofkeyval'] as $key2 => $val2)
				{
					$this->fields[$key]['arrayofkeyval'][$key2]=$langs->trans($val2);
				}
			}
		}
	}

    /**
     * Find latitude and longitude from an address (ex : address = 1+boulevard+salvador+4400+Nantes+france )
     *
     * @param $address
     * @return array [latitude, longitude], return [-180, -90] if no result find
     */
    public function getGeoCode($address)
    {
        ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
        $address = str_replace(' ', '+', $address);
        $url = "http://nominatim.openstreetmap.org/?format=json&q={$address}&limit=1";
        $resjson = file_get_contents($url);
        $res = json_decode($resjson, true);
        if (empty($res))
            return array('lat' => -180, 'lon' => -90);
        else
            return array('lat' => $res[0]['lat'], 'lon' => $res[0]['lon']);
	}

    /**
     * Create a map to print, base on a array of coordinates
     *
     * @param array $coordinates    array of array of coordinate where draw a marker, focus will be on the first array ( ex: [[lat,long], [lat,long], ...] )
     * @param string $morecss       css class added to map
     * @return String               map div to print in html
     */
    public function createMap($coordinates, $morecss = '')
    {
        $map = '<div id="map" class="'.$morecss.'">';
        $map .= '<script>
            var map = L.map(\'map\').setView(['.$coordinates[0][0].', '.$coordinates[0][1].'], 14);

            L.tileLayer(\'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\',
            {
                attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors\',
                maxZoom: 19,
            }).addTo(map);
            var redMarker = L.icon({
                iconUrl: \'' . $this->markerIcon . '\',
                iconSize: [32, 32]
            });';
        foreach ($coordinates as $coordinate) {
            $map .= 'var marker = L.marker(['.$coordinate[0].','.$coordinate[1].'], {icon: redMarker}).addTo(map);';
        }
        $map .= '</script>';
        $map .= '</div>';
        return $map;
    }

	/**
     * Print this location map
     *
     * @param integer $id device id
     * @param string $morecss css class added to map
     */
	private function printLocationMap($id, $morecss = '')
    {
        global $langs;
        $address = $this->road_nb . ' ' . $this->road_name . ', ' . $this->city . ' ' . $this->zip . ', ' . $this->country;
        print '<div class="fichecenter c42location-box c42location-margin-top">';
        print '<img class="iconeLocalisation" src="'.$this->locationIcon.'"></img>';
        print '<div class="localisation">';
        print '<h2>Localisation</h2><p>'.$address.'</p></div>';
        if ($this->longitude == -90 or $this->latitude == -180) {
            print '<div id="map">';
            print '<br><h2 class="center">'.$langs->trans("ErrorAddress").'</h2>';
            print '<br><p class="center">'.$address.'</p>';
            print '<br><img class="center c42location-error" src="'.$this->errorIcon.'"></img>';
            print '</div>';
        } else {
            print $this->createMap(array(array($this->latitude, $this->longitude)), $morecss);
        }
        print '<div class="right c42location-margin-bottom">';
        print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&amp;action=mapedit">'.$langs->trans("Modify").'</a>';
        print '<a class="butActionDelete" href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&amp;action=deletelocation">'.$langs->trans("Delete").'</a>';
        print '</div>';
    }

    /**
     * Print edit Map, view depending of parameters
     *
     * @param float $latitude
     * @param float $longitude
     * @param string $road_nb
     * @param string $road_name
     * @param string $city
     * @param string $country
     * @param string $zip
     * @param string $id
     * @param string $morecss
     */
    private function printEditMap($latitude, $longitude, $road_nb, $road_name, $city, $zip, $country, $id, $morecss = '')
    {
        global $langs;
        $callback = dol_buildpath($_SERVER['PHP_SELF'].'?id='.$id.'&action=mapedit', 1);
        print '<div class="fichecenter c42location-box c42location-margin-top">';
        print '<img id="iconeLocalisation" src="'.$this->locationIcon.'"></img>';
        print '<div class="localisation"><h2>Localisation</h2></div>';

        print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?id='.$id.'" id="update">';
        print '<input type="hidden" name="action" value="confirmEditMap">';
        print '<div class="fichecenter">';
        print '<div class="fichehalfleft">';
        print '<input type="text" class="c42location-input" id="roadNbr" name="roadNbr" placeholder="'.$langs->trans("StreetNumber").'" value="'.$road_nb.'"><br/>';
        print '<input type="text" class="c42location-input" id="roadName" name="roadName" placeholder="'.$langs->trans("StreetName").'" value="'.$road_name.'"><br>';
        print '<input type="text" class="c42location-input" id="zip" name="zip" placeholder="'.$langs->trans("ZipCode").'" value="'.$zip.'"><br>';
        print '</div>';
        print '<div class="fichehalfright">';
        print '<input type="text" class="c42location-input" id="city" name="city" placeholder="'.$langs->trans("City").'" value="'.$city.'"><br>';
        print '<input type="text" class="c42location-input" id="country" name="country" placeholder="'.$langs->trans("Country").'" value="'.$country.'">';
        print '</div>';
        print '<div class="clearboth"></div>';
        print '</div></form>';

        if ($longitude == -90 or $latitude == -180) {
            print '<div id="map">';
            print '<br><h2 class="center">'.$langs->trans("ErrorAddress").'</h2>';
            print '<br><p class="center">'.$road_nb.' '.$road_name.', '.$city.' '.$zip. ', '.$country.'</p>';
            print '<br><img class="center c42location-error" src="'.$this->errorIcon.'"></img>';
            print '</div>';
        } else {
            print $this->createMap(array(array($latitude, $longitude)), $morecss);
        }

        if (empty(GETPOST('preview'))) {
            print '<div class="right c42location-margin-bottom"><a class="butActionRefused" style="display: inline !important;" title="'.$langs->trans("SubmitDenied").'">'.$langs->trans("Save").'</a>';
        } else {
            print '<div class="right c42location-margin-bottom"><input type="submit" class="button" name="add" value="'.$langs->trans("Save").'" form="update">';
        }
        print '&nbsp;<input type="submit" class="button" name="preview" value="'.$langs->trans("Preview").'" form="update" formaction="'.$callback.'">';
        print '&nbsp;<input type="submit" class="button" name="cancel" title="test" value="'.$langs->trans("Cancel").'" form="update"></div>';
    }

    /**
     * @param float $latitude
     * @param float $longitude
     * @param string $road_nb
     * @param string $road_name
     * @param string $city
     * @param string $country
     * @param string $zip
     * @param string $id
     * @param string $morecss
     */
    private function printCreateLocalisation($latitude, $longitude, $road_nb, $road_name, $city, $zip, $country, $id, $morecss = '')
    {
        global $langs;
        $callback = dol_buildpath($_SERVER['PHP_SELF'].'?id='.$id.'&action=createMap', 1);
        print '<div class="fichecenter c42location-box c42location-margin-top">';
        print '<img id="iconeLocalisation" src="'.$this->locationIcon.'"></img>';
        print '<div class="localisation"><h2>Localisation</h2></div>';

        print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?id='.$id.'" id="update">';
        print '<input type="hidden" name="action" value="confirmCreateMap">';
        print '<div class="fichecenter">';
        print '<div class="fichehalfleft">';
        print '<input type="text" class="c42location-input" id="roadNbr" name="roadNbr" placeholder="'.$langs->trans("StreetNumber").'" value="'.$road_nb.'"><br/>';
        print '<input type="text" class="c42location-input" id="roadName" name="roadName" placeholder="'.$langs->trans("StreetName").'" value="'.$road_name.'"><br>';
        print '<input type="text" class="c42location-input" id="zip" name="zip" placeholder="'.$langs->trans("ZipCode").'" value="'.$zip.'"><br>';
        print '</div>';
        print '<div class="fichehalfright">';
        print '<input type="text" class="c42location-input" id="city" name="city" placeholder="'.$langs->trans("City").'" value="'.$city.'"><br>';
        print '<input type="text" class="c42location-input" id="country" name="country" placeholder="'.$langs->trans("Country").'" value="'.$country.'">';
        print '</div>';
        print '<div class="clearboth"></div>';
        print '</div></form>';

        if ($longitude == -90 or $latitude == -180) {
            print '<div id="map">';
            print '<br><h2 class="center">'.$langs->trans("ErrorAddress").'</h2>';
            print '<br><p class="center">'.$road_nb.' '.$road_name.', '.$city.' '.$zip. ', '.$country.'</p>';
            print '<br><img class="center c42location-error" src="'.$this->errorIcon.'"></img>';
            print '</div>';
        } else if ($road_name == '') {
            print '<div id="map">';
            print '<br><h2 class="center">'.$langs->trans("Preview").'</h2>';
            print '<br><p class="center">'.$langs->trans("PreviewMsg").'</p>';
            print '<br><img class="center c42location-error" src="'.$this->browsIcon.'"></img>';
            print '</div>';
        }
        else {
            print $this->createMap(array(array($latitude, $longitude)), $morecss);
        }

        if (empty(GETPOST('preview'))) {
            print '<div class="right c42location-margin-bottom"><a class="butActionRefused" style="display: inline !important;" title="'.$langs->trans("SubmitDenied").'">'.$langs->trans("Save").'</a>';
        } else {
            print '<div class="right c42location-margin-bottom"><input type="submit" class="button" name="add" value="'.$langs->trans("Save").'" form="update">';
        }
        print '&nbsp;<input type="submit" class="button" name="preview" value="'.$langs->trans("Preview").'" form="update" formaction="'.$callback.'">';
        print '&nbsp;<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'" form="update"></div>';
    }

    /**
     * Print map on a page, depending the action
     *
     * @param integer $id       device id
     * @param string $action    action to execute
     * @param string $morecss   css class added to map
     */
    public function printMap($id, $action, $morecss = '')
    {
        global $langs;
        $roadNbr = GETPOST('roadNbr');
        $roadName = GETPOST('roadName');
        $city = GETPOST('city');
        $zip = GETPOST('zip');
        $country = GETPOST('country');

        // $preview = true in preview mod
        $preview = !empty(GETPOST('preview'));

        // print : view map editor, view preview, view map
        if (!$this->rowid && !$action) {
            print '<div class="fichecenter c42location-box c42location-margin-top">';
            print '<img class="iconeLocalisation" src="'.$this->locationIcon.'"></img>';
            print '<div class="localisation"><a class="butAction c42location-float-right" href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&amp;action=createMap">'.$langs->trans("Create").'</a><h2>Localisation</h2></div>';
        } else {
            if ($action == 'createMap' && $preview == false) {
                $this->printCreateLocalisation('47.2067984', '-1.5750091', '', '', '', '', '', $id, $morecss);
            } else if ($action == 'createMap' && $preview == true) {
                $geocode = $this->getGeoCode($roadNbr . '+' . $roadName . '+' . $city . '+' . $zip . '+' . $country);
                $this->printCreateLocalisation($geocode['lat'], $geocode['lon'], $roadNbr, $roadName, $city, $zip, $country, $id, $morecss);
            } else if ($action == 'mapedit' && $preview == false) {
                $this->printEditMap($this->latitude, $this->longitude, $this->road_nb, $this->road_name, $this->city, $this->zip, $this->country, $id, $morecss);
            } else if ($action == 'mapedit' && $preview == true) {
                $geocode = $this->getGeoCode($roadNbr . '+' . $roadName . '+' . $city . '+' . $zip . '+' . $country);
                $this->printEditMap($geocode['lat'], $geocode['lon'], $roadNbr, $roadName, $city, $zip, $country, $id, $morecss);
            } else {
                $this->printLocationMap($id, $morecss);
            }
            if ($action == 'deletelocation') {
                $form=new Form($db);
                // Confirmation to delete location
                $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $id, $langs->trans("DeleteLocation"), $langs->trans("ConfirmDeleteLocation"), 'confirm_delete_location', '', 0, 1);
                print $formconfirm;
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
	 * Clone an object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		global $langs, $extrafields;
	    $error = 0;

	    dol_syslog(__METHOD__, LOG_DEBUG);

	    $object = new self($this->db);

	    $this->db->begin();

	    // Load source object
	    $result = $object->fetchCommon($fromid);
	    if ($result > 0 && ! empty($object->table_element_line)) $object->fetchLines();

	    // get lines so they will be clone
	    //foreach($this->lines as $line)
	    //	$line->fetch_optionals();

	    // Reset some properties
	    unset($object->id);
	    unset($object->fk_user_creat);
	    unset($object->import_key);


	    // Clear fields
	    $object->ref = "copy_of_".$object->ref;
	    $object->title = $langs->trans("CopyOf")." ".$object->title;
	    // ...
	    // Clear extrafields that are unique
	    if (is_array($object->array_options) && count($object->array_options) > 0)
	    {
	    	$extrafields->fetch_name_optionals_label($this->element);
	    	foreach($object->array_options as $key => $option)
	    	{
	    		$shortkey = preg_replace('/options_/', '', $key);
	    		if (! empty($extrafields->attributes[$this->element]['unique'][$shortkey]))
	    		{
	    			//var_dump($key); var_dump($clonedObj->array_options[$key]); exit;
	    			unset($object->array_options[$key]);
	    		}
	    	}
	    }

	    // Create clone
		$object->context['createfromclone'] = 'createfromclone';
	    $result = $object->createCommon($user);
	    if ($result < 0) {
	        $error++;
	        $this->error = $object->error;
	        $this->errors = $object->errors;
	    }

	    if (! $error)
	    {
	    	// copy internal contacts
	    	if ($this->copy_linked_contact($object, 'internal') < 0)
	    	{
	    		$error++;
	    	}
	    }

	    if (! $error)
	    {
	    	// copy external contacts if same company
	    	if (property_exists($this, 'socid') && $this->socid == $object->socid)
	    	{
	    		if ($this->copy_linked_contact($object, 'external') < 0)
	    			$error++;
	    	}
	    }

	    unset($object->context['createfromclone']);

	    // End
	    if (!$error) {
	        $this->db->commit();
	        return $object;
	    } else {
	        $this->db->rollback();
	        return -1;
	    }
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
	    $this->rowid = $id;
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && ! empty($this->table_element_line)) $this->fetchLines();
		return $result;
	}

	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records=array();

		$sql = 'SELECT ';
		$sql .= $this->getFieldList();
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element. ' as t';
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql .= ' WHERE t.entity IN ('.getEntity($this->table_element).')';
		else $sql .= ' WHERE 1 = 1';
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key=='t.rowid') {
					$sqlwhere[] = $key . '='. $value;
				}
				elseif (strpos($key, 'date') !== false) {
					$sqlwhere[] = $key.' = \''.$this->db->idate($value).'\'';
				}
				elseif ($key=='customsql') {
					$sqlwhere[] = $value;
				}
				else {
					$sqlwhere[] = $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' AND (' . implode(' '.$filtermode.' ', $sqlwhere).')';
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .=  ' ' . $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
            $i = 0;
			while ($i < min($limit, $num))
			{
			    $obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

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
	    $this->tms = dol_now();
	    $this->fk_user_modif = $user->id;
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
		//return $this->deleteCommon($user, $notrigger, 1);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = 'SELECT rowid, date_creation as datec, tms as datem,';
		$sql.= ' fk_user_creat, fk_user_modif';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		$sql.= ' WHERE t.rowid = '.$id;
		$result=$this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if ($obj->fk_user_author)
				{
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation   = $cuser;
				}

				if ($obj->fk_user_valid)
				{
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if ($obj->fk_user_cloture)
				{
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture   = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);
		}
		else
		{
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->initAsSpecimenCommon();
	}

	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	//public function doScheduledJob($param1, $param2, ...)
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error='';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}
}