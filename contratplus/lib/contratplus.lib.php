<?php

/**
 * Get number of line of a contract
 *
 * @param   int         $id         Id of contract
 * @param   Database    $db         Db handler
 *
 * @return int | null
 */
function getNbLignes($id, $db)
{
    $sql = "SELECT count(rowid) as nb FROM ".MAIN_DB_PREFIX."contratdet WHERE fk_contrat =".$id;
    $result = $db->query($sql);
    if ($result) {
        $objet = $db->fetch_object($result);
        return $objet->nb;
    } else {
        return null;
    }
}

/**
 * Check if position of a service exist on a contract
 *
 * @param   int         $contractid         Id of contract
 * @param   int         $pos                Position to check
 * @param   Database    $db                 Db handler
 *
 * @return bool
 */
function posExist($contractid, $pos, $db)
{

    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."contratdet WHERE fk_contrat =".$contractid." AND contratplus_rang =".$pos;
    $result = $db->query($sql);
    if($result) {
        return true;
    } else {
        return false;
    }
}

/**
 * Get last position of a service on a contract
 *
 * @param   int         $contractid         Id of contract
 * @param   Database    $db                 Db handler
 *
 * @return int | null
 */
function getLastServicePos($contractid, $db)
{
    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."contratdet WHERE fk_contrat =".$contractid." ORDER BY contratplus_rang DESC LIMIT 1";
    $result = $db->query($sql);
    if($result) {
        $object = $db ->fetch_object($result);
        return (int) $object->contratplus_rang;
    } else {
        return null;
    }
}

/**
 * Get first position of a service on a contract
 *
 * @param   int         $contractid         Id of contract
 * @param   Database    $db                 Db handler
 *
 * @return int | null
 */
function getFirstServicePos($contractid, $db)
{
    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."contratdet WHERE fk_contrat =".$contractid." ORDER BY contratplus_rang ASC LIMIT 1";
    $result = $db->query($sql);
    if ($result) {
        $object = $db->fetch_object($result);
        return (int) $object->contratplus_rang;
    } else {
        return null;
    }
}

/**
 * Set a unique pos to the last service created
 *
 * @param   int         $contractid         Id of contract
 * @param   Database    $db                 Db handler
 *
 * @return void
 */
function setUniquePos2($contractid, $db)
{
    $sql = "SELECT MAX(rowid) as rowid,contratplus_rang FROM ".MAIN_DB_PREFIX."contratdet WHERE fk_contrat =".$contractid." GROUP BY contratplus_rang HAVING COUNT(*)>1";
    $result = $db->query($sql);
    if ($result >1)
    {
        $object = $db->fetch_object($result);
        $sql = "UPDATE ".MAIN_DB_PREFIX."contratdet SET contratplus_rang =".$object->contratplus_rang."+1 WHERE rowid =".$object->rowid;
        $db->query($sql);
    }
}

/**
 * Swap actual pos with new position
 *
 * @param   int         $contractid         Id of contract
 * @param   int         $actualPos          Actual position
 * @param   int         $newPos             New position
 * @param   Database    $db                 Db handler
 *
 * @return void
 */
function swapPos($contractid, $actualPos, $newPos, $db)
{
    if ($newPos > $actualPos) {
        $sql2 = "SELECT rowid,contratplus_rang as cpr FROM ".MAIN_DB_PREFIX."contratdet WHERE contratplus_rang > ".$actualPos." AND fk_contrat = ".$contractid." ORDER BY cpr ASC LIMIT 1";
        $result2 = $db->query($sql2);
        $obj = $db->fetch_object($result2);
        $newPos = $obj->cpr;
    } else if($newPos < $actualPos) {
        $sql3 = "SELECT rowid,contratplus_rang as cpr FROM ".MAIN_DB_PREFIX."contratdet WHERE contratplus_rang < ".$actualPos." AND fk_contrat = ".$contractid." ORDER BY cpr DESC LIMIT 1";
        $result3 = $db->query($sql3);
        $obj2 = $db->fetch_object($result3);
        $newPos = $obj2->cpr;
    }


    $sql = "UPDATE ".MAIN_DB_PREFIX."contratdet ";
    $sql.= "SET contratplus_rang = CASE contratplus_rang ";
    $sql.= "WHEN ".$actualPos." THEN ".$newPos." ";
    $sql.= "WHEN ".$newPos." THEN ".$actualPos." ";
    $sql.= "ELSE contratplus_rang END ";

    $sql.= "WHERE fk_contrat = ".$contractid;
    $db->query($sql);
}

/**
 * Set a pos to the last service created
 *
 * @param   int         $contractid         Id of contract
 * @param   Database    $db                 Db handler
 *
 * @return void
 */
function setNewServicePos($contractid, $db)
{
    $nbServices = getNbLignes($contractid, $db);
    $counter = 1;
	for ($i = 0; $i < $nbServices; $i++) {
	    $sql2 = "SELECT rowid,contratplus_rang as cpr FROM ".MAIN_DB_PREFIX."contratdet WHERE fk_contrat = ".$contractid." LIMIT 1 OFFSET ".$i;
		$result2 = $db->query($sql2);

		$obj = $db->fetch_object($result2);
		if ($obj->cpr == 0) {
			$sql3 = "UPDATE ".MAIN_DB_PREFIX."contratdet SET contratplus_rang =".$i." + 1 WHERE rowid =".$obj->rowid;
			$db->query($sql3);
		}

		$counter++;
	}
}

/**
 * Prepare head for custom contract
 *
 * @param   Contrat         $object         Contract object handler
 * @return  array
 */
function customContractPrepareHead(Contrat $object)
{
    global $db, $langs, $conf, $user;

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath('/contratplus/card.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("ContractCard");
    $head[$h][2] = 'card';
    $h++;

    if (empty($conf->global->MAIN_DISABLE_CONTACTS_TAB))
    {
        $nbContact = count($object->liste_contact(-1, 'internal')) + count($object->liste_contact(-1, 'external'));
        $head[$h][0] = dol_buildpath('/contrat/contact.php', 1).'?id='.$object->id;
        $head[$h][1] = $langs->trans("ContactsAddresses");
        if ($nbContact > 0) $head[$h][1].= '<span class="badge marginleftonlyshort">'.$nbContact.'</span>';
        $head[$h][2] = 'contact';
        $h++;
    }

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname);                                                   to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'contract');

    if (empty($conf->global->MAIN_DISABLE_NOTES_TAB))
    {
        $nbNote = 0;
        if(!empty($object->note_private)) $nbNote++;
        if(!empty($object->note_public)) $nbNote++;
        $head[$h][0] = dol_buildpath('/contrat/note.php', 1).'?id='.$object->id;
        $head[$h][1] = $langs->trans("Notes");
        if ($nbNote > 0) $head[$h][1].= '<span class="badge marginleftonlyshort">'.$nbNote.'</span>';
        $head[$h][2] = 'note';
        $h++;
    }

    require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
    require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
    $upload_dir = $conf->contrat->dir_output . "/" . dol_sanitizeFileName($object->ref);
    $nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
    $nbLinks=Link::count($db, $object->element, $object->id);
    $head[$h][0] = dol_buildpath('/contrat/document.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("Documents");
    if (($nbFiles+$nbLinks) > 0) $head[$h][1].= '<span class="badge marginleftonlyshort">'.($nbFiles+$nbLinks).'</span>';
    $head[$h][2] = 'documents';
    $h++;

    $head[$h][0] = dol_buildpath('/contrat/agenda.php', 1).'?id='.$object->id;
    $head[$h][1].= $langs->trans("Events");
    if (! empty($conf->agenda->enabled) && (!empty($user->rights->agenda->myactions->read) || !empty($user->rights->agenda->allactions->read) ))
    {
        $head[$h][1].= '/';
        $head[$h][1].= $langs->trans("Agenda");
    }
    $head[$h][2] = 'agenda';
    $h++;

    complete_head_from_modules($conf, $langs, $object, $head, $h, 'contract', 'remove');

    return $head;
}

/**
 * Prepare head for contract stats
 *
 * @return  array
 */
function contractStatsPrepareHead()
{

    global $langs, $db;

    // Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
    include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
    $hookmanager=new HookManager($db);
    $hookmanager->initHooks(array('contratplus'));

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath('/contratplus/index.php', 1);
    $head[$h][1] = $langs->trans("IndexStats");
    $head[$h][2] = 'stats';
    $h++;

    $head[$h][0] = dol_buildpath('/contratplus/statscontract.php', 1);
    $head[$h][1] = $langs->trans("ContractStats");
    $head[$h][2] = 'contractstats';
    $h++;

    $head[$h][0] = dol_buildpath('/contratplus/statsservice.php', 1);
    $head[$h][1] = $langs->trans("ServiceStats");
    $head[$h][2] = 'servicestats';
    $h++;

    $parameters = array('head'=>$head, 'h'=>$h);
    $object = null;
    $action = null;
    $reshook = $hookmanager->executeHooks('statsPrepareHead', $parameters, $object, $action);
    if (!empty($reshook))
    {
        return $reshook;
    }
    return $head;
}

/**
 * Get substitution array for contratplus
 *
 * @param   Langs       $langs      Output language
 * @return  mixed                   Substitution array
 */
function getContratPlusSubstitution($langs)
{
    require_once DOL_DOCUMENT_ROOT . '/core/lib/functions.lib.php';
    include_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

    $substitutionarray = getCommonSubstitutionArray($langs);
    $tmp = dol_getdate(dol_now(), true);
    $month = date("n", dol_now());
    $substitutionarray = array_merge($substitutionarray, array(
        '__C_D__' => $langs->trans('Day'.$tmp['wday']),
        '__C_M__' => $langs->trans('Month'.sprintf("%02d", $tmp['mon'])),
        '__C_Y__' => date("Y", dol_now()),
        '__CURRENT_WEEK__' => $langs->trans('Week').' '.date("W", dol_now()),
        '__C_W__' => $langs->trans('Week').' '.date("W", dol_now()),
        '__CURRENT_QUARTER__' => $langs->trans('Quarter').' '.ceil($month / 3),
        '__C_Q__' => $langs->trans('Quarter').' '.ceil($month / 3),
        '__CURRENT_HALF__' => $langs->trans('Half').' '.ceil($month / 6),
        '__C_H__' => $langs->trans('Half').' '.ceil($month / 6),
    ));

    return $substitutionarray;
}

/**
 * Mass action, set date and duration for all selected service
 *
 * @param   array       $selected           Ids of services to update
 * @param   string      $date               Start date
 * @param   string      $duration           Duration code
 * @return  int
 */
function massactionSetDateAndDuration($selected, $date, $duration)
{
    global $db;

    $values = array('1'=>'12', '2'=>'24', '3'=>'36', '4'=>'48', '5'=>'60', '6'=>'autres');
    if ($date || $duration) {
        $sql = "UPDATE ".MAIN_DB_PREFIX."contratdet_extrafields SET";
        if ($date)
            $sql.=" serv_date_start = '".$date."',";

        if ($duration)
            $sql.=" serv_duree = '".$duration."',";

        $sql = rtrim($sql, ','); // Remove last coma
        $sql.=" WHERE fk_object IN (".implode(', ', $selected).")";

        $res = $db->query($sql);
        if (!$res)
            return -1;
    }

    // for all selected, select date start and duration if duration > 0 and < 6 and date start set and calc date end and update
    foreach ($selected as $row => $id) {
        $sql = "SELECT serv_duree as duration, serv_date_start as date FROM ".MAIN_DB_PREFIX."contratdet_extrafields WHERE fk_object = ".$id;
        $resql = $db->query($sql);
        if ($resql) {
            $obj = $resql->fetch_object();
            $duration = $values[$obj->duration];
            $date = $obj->date;
            if ($duration && $date && $obj->duration > 0 && $obj->duration < 6) {
                $sql = "UPDATE ".MAIN_DB_PREFIX."contratdet_extrafields SET serv_date_end = DATE_ADD(\"".$date."\", INTERVAL ".$duration." MONTH)";
                $sql .= " WHERE fk_object = ".$id;
                $db->query($sql);
            }
        } else
            break;
    }

    return 1;
}

/**
 * Get all inactive user ids
 *
 * @return array            Ids of inactive users
 */
function getInactiveUsers()
{
    global $db;

    $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."user WHERE statut = 0";
    $arr = array();
    $resql = $db->query($sql);
    if ($resql) {
        foreach ($resql as $row) {
            $arr[] = $row['rowid'];
        }
    }

    return $arr;
}

/**
 * Show list element picture based on societe type
 *
 * @param	Object		$obj		The current fetched object
 * @param	Societe		&$socstatic	Societe object base
 */
function showContractLinePicto($obj, &$socstatic)
{
	global $db, $langs, $conf;

	$is_supplier = isSupplierContract($db, $obj->rowid);
	$socstatic->fetch($obj->socid);

	if (!$is_supplier && $socstatic->client == '0' && $socstatic->prospect == 0)
	{
		print '<td>'.img_error($langs->trans('NotCustomerWithCustomerContract')).'</td>';
	}
	else if ($is_supplier && $socstatic->fournisseur == '0')
	{
		print '<td>'.img_error($langs->trans('NotSupplierWithSupplierContract')).'</td>';
	}
	else if ($is_supplier && $conf->global->CONTRATPLUS_SUPPLIER_CONTRACT == 0)
	{
		print '<td>'.img_error($langs->trans('SupplierOptionNotActive')).'</td>';
	}
	else {
		if ($is_supplier) {
			print '<td>'.img_object($langs->trans('SupplierContract'), "sending").'</td>';
		}
		else {
			print '<td>'.img_object($langs->trans('CustomerContract'), "user").'</td>';
		}
	}
}

/**
 * Show the current sale representatives of a contract
 * @param 	Object		$obj		The current fetched object
 * @param	Societe		$socstatic	Societe object base
 */
function printSellRepresentative($obj, $socstatic)
{
	global $db, $user;

	if ($obj->socid > 0)
	{
		$listsalesrepresentatives=$socstatic->getSalesRepresentatives($user);
		if ($listsalesrepresentatives < 0) dol_print_error($db);
		$nbofsalesrepresentative=count($listsalesrepresentatives);
		if ($nbofsalesrepresentative > 3)   // We print only number
		{
			print '<a href="'.dol_buildpath('/societe/commerciaux.php', 1).'?socid='.$socstatic->id.'">';
			print $nbofsalesrepresentative;
			print '</a>';
		}
		else if ($nbofsalesrepresentative > 0)
		{
			$userstatic=new User($db);
			$j=0;
			foreach($listsalesrepresentatives as $val)
			{
				$userstatic->id=$val['id'];
				$userstatic->lastname=$val['lastname'];
				$userstatic->firstname=$val['firstname'];
				$userstatic->email=$val['email'];
				$userstatic->statut=$val['statut'];
				$userstatic->entity=$val['entity'];
				$userstatic->photo=$val['photo'];

				//print '<div class="float">':
				print $userstatic->getNomUrl(2).' ';
				print $userstatic->lastname .' '.$userstatic->firstname;
				$j++;
				if ($j < $nbofsalesrepresentative) print ' ';
				//print '</div>';
			}
		}
		//else print $langs->trans("NoSalesRepresentativeAffected");
	}
	else
	{
		print '&nbsp';
	}
}

/**
 * Fills the $moreforfilter parameter with the current user
 * @param		array		$search			Current search criterias
 * @param		string		&$moreforfilter
 */
function fillMoreForFilterForUser($search, &$moreforfilter)
{
	global $db, $langs, $conf, $user;
	$form = new Form($db);

	$langs->load('products');

	// Filtre Client / Fournisseur
	if ($user->rights->societe->client->voir & $user->rights->fournisseur->lire && $conf->global->CONTRATPLUS_SUPPLIER_CONTRACT == 1)
	{
		$moreforfilter.='<div class="divsearchfield">';
		$moreforfilter.=$langs->trans('ContratAllClientFournisseur');
		$moreforfilter.=' <select class="flat minwidth100 maxwidth300" id="search_cli_four" name="search_cli_four">
        <option value="-1" '.($search['cli_four']=="-1"?"selected":"").'>'.$langs->trans('All').'</option>
        <option value="0" '.($search['cli_four']=="0"?"selected":"").'>'.$langs->trans('Client').'</option>
        <option value="1" '.($search['cli_four']=="1"?"selected":"").'>'.$langs->trans('Fournisseur').'</option>
        </select>';
		$moreforfilter.='</div>';
	}

// If the user can view categories of products
	if ($conf->categorie->enabled && ($user->rights->produit->lire || $user->rights->service->lire))
	{
		include_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
		$moreforfilter.='<div class="divsearchfield">';
		$moreforfilter.=$langs->trans('IncludingProductWithTag'). ': ';
		$cate_arbo = $form->select_all_categories(Categorie::TYPE_PRODUCT, null, 'parent', null, null, 1);
		$moreforfilter.=$form->selectarray('search_product_category', $cate_arbo, $search['product_category'], 1, 0, 0, '', 0, 0, 0, 0, '', 1);
		$moreforfilter.='</div>';
	}
}

/**
 *  Return sale representatives and user for a contract
 *
 *  @return array					array of commercials
 */
function getSalesRepresentativesAndUsers()
{
	global $db, $conf, $user;

	// Get list of users allowed to be viewed
	$sql = "SELECT u.rowid, u.lastname, u.firstname, u.statut, u.login";
	$sql.= " FROM ".MAIN_DB_PREFIX."user as u";

	if (! empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE))
	{
		if (! empty($user->admin) && empty($user->entity) && $conf->entity == 1) {
			$sql.= " WHERE u.entity IS NOT NULL"; // Show all users
		} else {
			$sql.= " WHERE EXISTS (SELECT ug.fk_user FROM ".MAIN_DB_PREFIX."usergroup_user as ug WHERE u.rowid = ug.fk_user AND ug.entity IN (".getEntity('usergroup')."))";
			$sql.= " OR u.entity = 0"; // Show always superadmin
		}
	}
	else
	{
		$sql.= " WHERE u.entity IN (".getEntity('user').")";
	}

	if (empty($user->rights->user->user->lire)) $sql.=" AND u.rowid = ".$user->id;
	if (! empty($user->socid)) $sql.=" AND u.fk_soc = ".$user->socid;
	// Add existing sales representatives of thirdparty of external user
	if (empty($user->rights->user->user->lire) && $user->socid)
	{
		$sql.=" UNION ";
		$sql.= "SELECT u2.rowid, u2.lastname, u2.firstname, u2.statut, u2.login";
		$sql.= " FROM ".MAIN_DB_PREFIX."user as u2, ".MAIN_DB_PREFIX."societe_commerciaux as sc";

		if (! empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE))
		{
			if (! empty($user->admin) && empty($user->entity) && $conf->entity == 1) {
				$sql.= " WHERE u2.entity IS NOT NULL"; // Show all users
			} else {
				$sql.= " WHERE EXISTS (SELECT ug2.fk_user FROM ".MAIN_DB_PREFIX."usergroup_user as ug2 WHERE u2.rowid = ug2.fk_user AND ug2.entity IN (".getEntity('usergroup')."))";
			}
		}
		else
		{
			$sql.= " WHERE u2.entity IN (".getEntity('user').")";
		}

		$sql.= " AND u2.rowid = sc.fk_user AND sc.fk_soc=".$user->socid;
	}
	$sql.= " ORDER BY statut DESC, lastname ASC";  // Do not use 'ORDER BY u.statut' here, not compatible with the UNION.

	$resql = $db->query($sql);
	$result = array();
	foreach ($resql as $row) {
		$result[] = $row;
	}
	return ($result);
}
