<?php
/* Copyright (C) 2003       Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2016  Regis Houssin           <regis.houssin@capnetworks.com>
 * Copyright (C) 2017-2018	Eric GROULT			    <eric@code42.fr>
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
 * 	\defgroup   mymodule     Module MyModule
 *  \brief      Example of a module descriptor.
 *				Such a file must be copied into htdocs/mymodule/core/modules directory.
 *  \file       htdocs/mymodule/core/modules/modMyModule.class.php
 *  \ingroup    mymodule
 *  \brief      Description and activation file for module MyModule
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

/**
 *  Description and activation class for module MyModule
 */
class modContratPlus extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
        global $langs,$conf;

        $this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 448001;		// Code 42: ID 448000 - 448999
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'contratplus';
		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "Code 42";
		// Gives the possibility to the module, to provide his own family info and position of this family. (canceled $this->family)
		//$this->familyinfo = array('Code 42' => array('position' => '', 'label' => $langs->trans("Code 42")));
		// Module position in the family
		$this->module_position = 500;
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = 'Description of module ContratPlus';
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or 'dolibarr_deprecated' or version
		$this->version = '12.0.2';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='contratplus@contratplus';
		$this->editor_name = 'Code 42';
		$this->editor_url = 'http://www.code42.fr';

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /mymodule/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /mymodule/core/modules/barcode)
		// for specific css file (eg: /mymodule/css/mymodule.css.php)
		//$this->module_parts = array(
		//                        	'triggers' => 0,                                 	// Set this to 1 if module has its own trigger directory (core/triggers)
		//							'login' => 0,                                    	// Set this to 1 if module has its own login method directory (core/login)
		//							'substitutions' => 0,                            	// Set this to 1 if module has its own substitution function file (core/substitutions)
		//							'menus' => 0,                                    	// Set this to 1 if module has its own menus handler directory (core/menus)
		//							'theme' => 0,                                    	// Set this to 1 if module has its own theme directory (theme)
		//                        	'tpl' => 0,                                      	// Set this to 1 if module overwrite template dir (core/tpl)
		//							'barcode' => 0,                                  	// Set this to 1 if module has its own barcode directory (core/modules/barcode)
		//							'models' => 0,                                   	// Set this to 1 if module has its own models directory (core/modules/xxx)
		//							'css' => array('/mymodule/css/mymodule.css.php'),	// Set this to relative path of css file if module has its own css file
	 	//							'js' => array('/mymodule/js/mymodule.js'),          // Set this to relative path of js file if module must load a js on all pages
		//							'hooks' => array('hookcontext1','hookcontext2')  	// Set here all hooks context managed by module
		//							'dir' => array('output' => 'othermodulename'),      // To force the default directories names
		//							'workflow' => array('WORKFLOW_MODULE1_YOURACTIONTYPE_MODULE2'=>array('enabled'=>'! empty($conf->module1->enabled) && ! empty($conf->module2->enabled)', 'picto'=>'yourpicto@mymodule')) // Set here all workflow context managed by module
		//                        );
		$this->module_parts = array(
		                            'hooks' => array('contractcard','globalcard',''),
                                    'css' => array('/contratplus/css/contratPlus.css'),
                                    'js' => array('/contratplus/js/contratPlus.js'),
                                    'triggers' => 1,
                                    'models' => 1);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array();

		// Config pages. Put here list of php page, stored into mymodule/admin directory, to use to setup module.
		$this->config_page_url = array("config.php@contratplus");

		// Dependencies
		$this->hidden = false;			// A condition to hide module
		$this->depends = array('modContrat', 'modFournisseur', 'modSyslog', 'modFacture', 'modH2G2');		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->conflictwith = array();	// List of modules id this module is in conflict with
		$this->phpmin = array(5,6);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(4,0);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("contratplus@contratplus");

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('MYMODULE_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//                             1=>array('MYMODULE_MYNEWCONST2','chaine','myvalue','This is another constant to add',0, 'current', 1)
		// );
		$this->const = array();

		// Array to add new pages in new tabs
		// Example: $this->tabs = array('objecttype:+tabname1:Title1:mylangfile@mymodule:$user->rights->mymodule->read:/mymodule/mynewtab1.php?id=__ID__',  					// To add a new tab identified by code tabname1
        //                              'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@mymodule:$user->rights->othermodule->read:/mymodule/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
        //                              'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname
		// where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in fundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in customer order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view
        $this->tabs = array('contract:+contratplus:<i class="fa fa-file" aria-hidden="true"></i> Contrat Plus:contratplus@contratplus:$user->rights->contratplus->view:/contratplus/card.php?id=__ID__');
		// $this->tabs[] = array('data' => 'thirdparty:+contratPlus:ContratPlusTabName:contratplus@contratplus:$user->rights->contratplus->read:/contratplus/card.php?id=__ID__');

        // Dictionaries
		//    if (! isset($conf->contratplus->enabled))
		//       {
		//       	$conf->contratplus=new stdClass();
		//       	$conf->contratplus->enabled=0;
		//       }
		// $this->dictionaries=array();
        /* Example:
        if (! isset($conf->mymodule->enabled)) $conf->mymodule->enabled=0;	// This is to avoid warnings
        $this->dictionaries=array(
            'langs'=>'mylangfile@mymodule',
            'tabname'=>array(MAIN_DB_PREFIX."table1",MAIN_DB_PREFIX."table2",MAIN_DB_PREFIX."table3"),		// List of tables we want to see into dictonnary editor
            'tablib'=>array("Table1","Table2","Table3"),													// Label of tables
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),	// Request to select fields
            'tabsqlsort'=>array("label ASC","label ASC","label ASC"),																					// Sort order
            'tabfield'=>array("code,label","code,label","code,label"),																					// List of fields (result of select to show dictionary)
            'tabfieldvalue'=>array("code,label","code,label","code,label"),																				// List of fields (list of fields to edit a record)
            'tabfieldinsert'=>array("code,label","code,label","code,label"),																			// List of fields (list of fields for insert)
            'tabrowid'=>array("rowid","rowid","rowid"),																									// Name of columns with primary key (try to always name it 'rowid')
            'tabcond'=>array($conf->mymodule->enabled,$conf->mymodule->enabled,$conf->mymodule->enabled)												// Condition to show each dictionary
        );
        */

        // Boxes
		// Add here list of php file(s) stored in core/boxes that contains class to show a box.
        $this->boxes = array();			// List of boxes
		// Example:
		//$this->boxes=array(
		//    0=>array('file'=>'myboxa.php@mymodule','note'=>'','enabledbydefaulton'=>'Home'),
		//    1=>array('file'=>'myboxb.php@mymodule','note'=>''),
		//    2=>array('file'=>'myboxc.php@mymodule','note'=>'')
		//);

		// Cronjobs
        // unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
        if (DOL_VERSION >= 6) {
            $this->cronjobs = array(
                0 => array('label' => 'ContratPlusCron', 'jobtype' => 'method', 'class' => 'contratplus/class/contratplusCron.class.php', 'objectname'=>'contratplusCron', 'method' => 'exec', 'parameters' => '', 'comment' => 'Comment', 'frequency' => 5, 'unitfrequency' => 60, 'status' => 0));
        }
        else
    		$this->cronjobs = array();
		// Example: $this->cronjobs=array(0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'', 'comment'=>'Comment', 'frequency'=>3600, 'unitfrequency'=>3600),
		//                                1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'', 'comment'=>'Comment', 'frequency'=>3600, 'unitfrequency'=>3600)
		// );

		// Permissions
		$this->rights = array();		// Permission array used by this module
		$r=0;

        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = $langs->trans('ViewModule');
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'view';
        $this->rights[$r][5] = 'view';
        $r++;

        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = $langs->trans('ConfigurateModule');
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'action';
        $this->rights[$r][5] = 'configurer';
        $r++;

        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = $langs->trans('ConfigurateRenewSimple');
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'renew';
        $this->rights[$r][5] = 'simple';
        $r++;

        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = $langs->trans('ConfigurateRenewAll');
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'renew';
        $this->rights[$r][5] = 'all';
        $r++;

        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = $langs->trans('ConfigurateAutomate');
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'renew';
        $this->rights[$r][5] = 'auto';
        $r++;
		// Add here list of permission defined by an id, a label, a boolean and two constant strings.
		// Example:
		// $this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		// $this->rights[$r][1] = 'Permision label';	// Permission label
		// $this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		// $this->rights[$r][4] = 'level1';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		// $this->rights[$r][5] = 'level2';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		// $r++;

		// Main menu entries
		$this->menu = array();			// List of menus to add
		$r=0;

		$this->menu[$r]=array(	'fk_menu'=>0,
								'type'=>'top',
								'titre'=>'Contrat Plus',
								'mainmenu'=>'contratplus',
								'leftmenu'=>'1',
								'url'=>'/contratplus/index.php',
								'langs'=>'',
								'position'=>1100+$r,
								'enabled'=>'$conf->contratplus->enabled',
								'perms'=>'$user->rights->contratplus->view',
								'target'=>'',
								'user'=>2);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=contratplus',
								'type'=>'left',
								'titre'=>'Contrat Plus - v.' . $this->version . '',
								'mainmenu'=>'contratplus',
                                'leftmenu'=>'contratplus_dashboard',
								'url'=>'/contratplus/index.php',
								'langs'=>'',
								'position'=>1100+$r,
								'enabled'=>'$conf->contratplus->enabled',
								'perms'=>'$user->rights->contrat->lire',
								'target'=>'',
								'user'=>2);
		$r++;

        $this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=contratplus',
                                'type'=>'left',
                                'titre'=>$langs->trans('ListContractMenu'),
                                'mainmenu'=>'contratplus',
                                'leftmenu'=>'contratplus_contract_list',
                                'url'=>'/contratplus/list.php',
                                'langs'=>'contratplus@contratplus',
                                'position'=>1100+$r,
                                'enabled'=>'$conf->contratplus->enabled',
                                'perms'=>'$user->rights->contrat->lire',
                                'target'=>'',
                                'user'=>2);
        $r++;

        $this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=contratplus,fk_leftmenu=contratplus_contract_list',
                                'type'=>'left',
                                'titre'=>$langs->trans('CreateContractMenu'),
                                'mainmenu'=>'contratplus',
                                'leftmenu'=>'contratplus_contract_create',
                                'url'=>'/contrat/card.php?action=create',
                                'langs'=>'contratplus@contratplus',
                                'position'=>1100+$r,
                                'enabled'=>'$conf->contratplus->enabled',
                                'perms'=>'$user->rights->contrat->creer',
                                'target'=>'',
                                'user'=>2);
        $r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=contratplus',
								'type'=>'left',
								'titre'=>$langs->trans('ListServiceMenu'),
								'mainmenu'=>'contratplus',
                                'leftmenu'=>'contratplus_service_list',
								'url'=>'/contratplus/services.php',
								'langs'=>'contratplus@contratplus',
								'position'=>1100+$r,
								'enabled'=>'$conf->contratplus->enabled',
								'perms'=>'$user->rights->contrat->lire',
								'target'=>'',
								'user'=>2);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=contratplus',
								'type'=>'left',
								'titre'=>$langs->trans('ConfigurationMenu'),
								'mainmenu'=>'contratplus',
								'leftmenu'=>'contratplus_configuration',
								'url'=>'/contratplus/admin/config.php',
								'langs'=>'contratplus@contratplus',
								'position'=>1100+$r,
								'enabled'=>'$conf->contratplus->enabled',
								'perms'=>'$user->rights->contratplus->action->configurer',
								'target'=>'blank',
								'user'=>2);
		$r++;

        $this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=contratplus',
                                'type'=>'left',
                                'titre'=>$langs->trans('DocumentationMenu'),
                                'mainmenu'=>'contratplus',
                                'leftmenu'=>'contratplus_documentation',
                                'url'=>'/contratplus/documentation.php',
                                'langs'=>'contratplus@contratplus',
                                'position'=>1100+$r,
                                'enabled'=>'$conf->contratplus->enabled',
                                'perms'=>'$conf->contratplus->enabled',
                                'target'=>'blank',
                                'user'=>2);
        $r++;

        $this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=contratplus',
                                'type'=>'left',
                                'titre'=>$langs->trans('News'),
                                'mainmenu'=>'contratplus',
                                'leftmenu'=>'contratplus_news',
                                'url'=>'/contratplus/changelog.php',
                                'langs'=>'contratplus@contratplus',
                                'position'=>1100+$r,
                                'enabled'=>'$conf->contratplus->enabled',
                                'perms'=>'$conf->contratplus->enabled',
                                'target'=>'blank',
                                'user'=>2);
        $r++;

		// Exports
		$r=1;

		// Example:
		// $this->export_code[$r]=$this->rights_class.'_'.$r;
		// $this->export_label[$r]='MyModule';	// Translation key (used only if key ExportDataset_xxx_z not found)
        // $this->export_enabled[$r]='1';                               // Condition to show export in list (ie: '$user->id==3'). Set to 1 to always show when module is enabled.
        // $this->export_icon[$r]='generic:MyModule';
		// $this->export_permission[$r]=array(array("mymodule","level1","level2"));
		// $this->export_fields_array[$r]=array('s.rowid'=>"IdCompany",'s.nom'=>'CompanyName','s.address'=>'Address','s.zip'=>'Zip','s.town'=>'Town','s.fk_pays'=>'Country','s.phone'=>'Phone','s.siren'=>'ProfId1','s.siret'=>'ProfId2','s.ape'=>'ProfId3','s.idprof4'=>'ProfId4','s.code_compta'=>'CustomerAccountancyCode','s.code_compta_fournisseur'=>'SupplierAccountancyCode','f.rowid'=>"InvoiceId",'f.facnumber'=>"InvoiceRef",'f.datec'=>"InvoiceDateCreation",'f.datef'=>"DateInvoice",'f.total'=>"TotalHT",'f.total_ttc'=>"TotalTTC",'f.tva'=>"TotalVAT",'f.paye'=>"InvoicePaid",'f.fk_statut'=>'InvoiceStatus','f.note'=>"InvoiceNote",'fd.rowid'=>'LineId','fd.description'=>"LineDescription",'fd.price'=>"LineUnitPrice",'fd.tva_tx'=>"LineVATRate",'fd.qty'=>"LineQty",'fd.total_ht'=>"LineTotalHT",'fd.total_tva'=>"LineTotalTVA",'fd.total_ttc'=>"LineTotalTTC",'fd.date_start'=>"DateStart",'fd.date_end'=>"DateEnd",'fd.fk_product'=>'ProductId','p.ref'=>'ProductRef');
		// $this->export_TypeFields_array[$r]=array('t.date'=>'Date', 't.qte'=>'Numeric', 't.poids'=>'Numeric', 't.fad'=>'Numeric', 't.paq'=>'Numeric', 't.stockage'=>'Numeric', 't.fadparliv'=>'Numeric', 't.livau100'=>'Numeric', 't.forfait'=>'Numeric', 's.nom'=>'Text','s.address'=>'Text','s.zip'=>'Text','s.town'=>'Text','c.code'=>'Text','s.phone'=>'Text','s.siren'=>'Text','s.siret'=>'Text','s.ape'=>'Text','s.idprof4'=>'Text','s.code_compta'=>'Text','s.code_compta_fournisseur'=>'Text','s.tva_intra'=>'Text','f.facnumber'=>"Text",'f.datec'=>"Date",'f.datef'=>"Date",'f.date_lim_reglement'=>"Date",'f.total'=>"Numeric",'f.total_ttc'=>"Numeric",'f.tva'=>"Numeric",'f.paye'=>"Boolean",'f.fk_statut'=>'Status','f.note_private'=>"Text",'f.note_public'=>"Text",'fd.description'=>"Text",'fd.subprice'=>"Numeric",'fd.tva_tx'=>"Numeric",'fd.qty'=>"Numeric",'fd.total_ht'=>"Numeric",'fd.total_tva'=>"Numeric",'fd.total_ttc'=>"Numeric",
        //'fd.date_start'=>"Date",'fd.date_end'=>"Date",'fd.special_code'=>'Numeric','fd.product_type'=>"Numeric",'fd.fk_product'=>'List:product:label','p.ref'=>'Text','p.label'=>'Text','p.accountancy_code_sell'=>'Text');
		// $this->export_entities_array[$r]=array('s.rowid'=>"company",'s.nom'=>'company','s.address'=>'company','s.zip'=>'company','s.town'=>'company','s.fk_pays'=>'company','s.phone'=>'company','s.siren'=>'company','s.siret'=>'company','s.ape'=>'company','s.idprof4'=>'company','s.code_compta'=>'company','s.code_compta_fournisseur'=>'company','f.rowid'=>"invoice",'f.facnumber'=>"invoice",'f.datec'=>"invoice",'f.datef'=>"invoice",'f.total'=>"invoice",'f.total_ttc'=>"invoice",'f.tva'=>"invoice",'f.paye'=>"invoice",'f.fk_statut'=>'invoice','f.note'=>"invoice",'fd.rowid'=>'invoice_line','fd.description'=>"invoice_line",'fd.price'=>"invoice_line",'fd.total_ht'=>"invoice_line",'fd.total_tva'=>"invoice_line",'fd.total_ttc'=>"invoice_line",'fd.tva_tx'=>"invoice_line",'fd.qty'=>"invoice_line",'fd.date_start'=>"invoice_line",'fd.date_end'=>"invoice_line",'fd.fk_product'=>'product','p.ref'=>'product');
		// $this->export_dependencies_array[$r]=array('invoice_line'=>'fd.rowid','product'=>'fd.rowid'); // To add unique key if we ask a field of a child to avoid the DISTINCT to discard them
		// $this->export_sql_start[$r]='SELECT DISTINCT ';
		// $this->export_sql_end[$r]  =' FROM ('.MAIN_DB_PREFIX.'facture as f, '.MAIN_DB_PREFIX.'facturedet as fd, '.MAIN_DB_PREFIX.'societe as s)';
		// $this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'product as p on (fd.fk_product = p.rowid)';
		// $this->export_sql_end[$r] .=' WHERE f.fk_soc = s.rowid AND f.rowid = fd.fk_facture';
		// $this->export_sql_order[$r] .=' ORDER BY s.nom';
		// $r++;
	}

    /**
     *      Show the debug for createExtrafields() function
     *
     *      @param      array   $res        all return value of addExtrafields
     *      @return     int                 <= 0 on error, > 0 on success
     */
	public function showCreateDebug($res)
    {
        foreach($res as $row => $value)
        {
            if ($value > 0)
                dol_syslog("Code 42: Extrafields " . $row . " created.");
            else
                dol_syslog("Code 42: Extrafields " . $row . " not created.");
        }
    }

    /**
     *      Update or create extrafields if it doesn't exist
     *
     *      @param      Extrafield      $extrafields            Extrafield class
     *      @param      string          $attrname               Code of attribute
     *      @param      string          $label                  Label of attribute
     *      @param      int             $type                   Type of attribute ('int', 'text', 'varchar', 'date', 'datehour', 'float')
     *      @param      int             $pos                    Position of attribute
     *      @param      string          $elementtype            Element type ('member', 'product', 'thirdparty', ...)
     *      @param      array | string  $param                  Params for field (ex for select list : array('options' => array(value'=>'label of option')) )
     *      @param      int             $alwayseditable         Is attribute always editable regardless of the document status
     *      @param      string          $perms                  Permission to check for extrafield display
     *      @param      int             $list                   Visibility of the extrafield
     *      @return     int                                     <= 0 on error, > 0 on success
     */
    public function addUpdateExtrafields($extrafields, $attrname, $label, $type, $pos, $elementtype, $param, $alwayseditable, $perms = null, $list = 1)
    {

        $res = $extrafields->update($attrname, $label, $type, '', $elementtype, 0, 0, $pos, $param, $alwayseditable, $perms, $list);
        if ($res <= 0)
            $res = $extrafields->addExtrafield($attrname, $label, $type, $pos, '', $elementtype, 0, 0, '', $param, $alwayseditable, $perms, $list);

        return $res;
    }

    /**
     *      Create extrafields used by contratplus
     *
     *      @return     void
     */
	public function createExtrafields()
    {
        global $langs;

        $langs->load("contratplus@contratplus");
        $extrafields = new Extrafields($this->db);
        $option = array('options' => array('1' => '12',
                                            '2' => '24',
                                            '3' => '36',
                                            '4' => '48',
                                            '5' => '60',
                                            '6' => 'autres'));
        $i = 0;
        $res[$i++] = $this->addUpdateExtrafields($extrafields, 'serv_date_start', 'ContratplusDateStart', 'date', 6, 'contratdet', '', 1);
        $res[$i++] = $this->addUpdateExtrafields($extrafields, 'serv_date_start', 'ContratplusDateStart', 'date', 6, 'facturedet', '', 1);
        $res[$i++] = $this->addUpdateExtrafields($extrafields, 'serv_date_start', 'ContratplusDateStart', 'date', 6, 'facture_fourn_det', '', 1);
        $res[$i++] = $this->addUpdateExtrafields($extrafields, 'serv_date_end', 'ContratplusDateEnd', 'date', 4, 'contratdet', '', 0);
        $res[$i++] = $this->addUpdateExtrafields($extrafields, 'serv_date_end', 'ContratplusDateEnd', 'date', 4, 'facturedet', '', 0);
        $res[$i++] = $this->addUpdateExtrafields($extrafields, 'serv_date_end', 'ContratplusDateEnd', 'date', 4, 'facture_fourn_det', '', 0);
        $res[$i++] = $this->addUpdateExtrafields($extrafields, 'supplier_contract', $langs->trans('ModSupplierContract'), 'boolean', 0, 'contrat', '', 1, '$conf->global->CONTRATPLUS_SUPPLIER_CONTRACT');
        $res[$i++] = $this->addUpdateExtrafields($extrafields, 'contratplus_automated', $langs->trans('ContratplusAutomated'), 'boolean', 1, 'contrat', '', 1);
        // Issue 224 : Delete this extrafields
        //$res[$i++] = $this->addUpdateExtrafields($extrafields, 'contratplus_closed', $langs->trans('ContratplusClosed'), 'boolean', 0, 'contrat', '', 1);
        $res[$i++] = $this->addUpdateExtrafields($extrafields, 'contratplus_bank', $langs->trans('ContratplusBank'), 'sellist', 3, 'contrat', array("options" => array("bank_account:label:rowid" => null)), 1);
        $res[$i++] = $this->addUpdateExtrafields($extrafields, 'serv_duree', 'ContratplusEngage', 'select', 5, 'propaldet', $option, 1);
        $res[$i++] = $this->addUpdateExtrafields($extrafields, 'serv_duree', 'ContratplusEngage', 'select', 5, 'contratdet', $option, 1);
        $res[$i++] = $this->addUpdateExtrafields($extrafields, 'serv_duree', 'ContratplusEngage', 'select', 5, 'commandedet', $option, 1);
        $res[$i++] = $this->addUpdateExtrafields($extrafields, 'serv_duree', 'ContratplusEngage', 'select', 5, 'facturedet', $option, 1);
        $res[$i++] = $this->addUpdateExtrafields($extrafields, 'serv_duree', 'ContratplusEngage', 'select', 5, 'facture_fourn_det', $option, 1);
        // Add Extrafield for sepa on contract
        $res[$i++] = $this->addUpdateExtrafields($extrafields, 'contratplus_sepa_activated', 'ContratplusSepaActivated', 'boolean', 2, 'contrat', '', 1, '$conf->global->CONTRATPLUS_SEPA_LINK && $conf->prelevement->enabled');
        //$this->showCreateDebug($res);
    }

    /**
     *      Delete extrafields created by contratplus
     *
     *      @return     void
     */
    public function deleteExtrafields()
    {
        $extrafields = new Extrafields($this->db);

        $extrafields->delete('serv_date_start', 'contratdet');
        $extrafields->delete('serv_date_start', 'facturedet');
        $extrafields->delete('serv_date_start', 'facture_fourn_det');
        $extrafields->delete('serv_date_end', 'contratdet');
        $extrafields->delete('serv_date_end', 'facturedet');
        $extrafields->delete('serv_date_end', 'facture_fourn_det');
        $extrafields->delete('supplier_contract', 'contrat');
        $extrafields->delete('contratplus_automated', 'contrat');
        //$extrafields->delete('contratplus_closed', 'contrat');
        $extrafields->delete('contratplus_bank', 'contrat');
        $extrafields->delete('serv_duree', 'propaldet');
        $extrafields->delete('serv_duree', 'commandedet');
        $extrafields->delete('serv_duree', 'contratdet');
        $extrafields->delete('serv_duree', 'facturedet');
        $extrafields->delete('serv_duree', 'facture_fourn_det');
    }

    /**
     *      Print debug for pdf model creation
     *
     *      @param      bool        $res        Return value of the copy()
     *      @param      string      $src        Source file path
     *      @param      string      $dest       Destination file path
     *      @return     void
     */
    public function showCreatePdfDebug($res, $src, $dest)
    {
        if (!$res)
            dol_syslog('Code 42: Can\'t copy ' . $src . 'to ' . $dest);
        else
            dol_syslog('Code 42: ' . $src . ' copied to ' . $dest);
    }

    /**
     * Init contratdet extrafields (serv_date_start, serv_date_end, serv_duree) if they're not set
     *
     * @return  int                     1 if OK, 0 if KO
     */
    private function initExtrafields()
    {
        global $db;

        // Init serv_date_start, serv_date_end and serv_duree on contratdet if not exist
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "contratdet_extrafields (fk_object, serv_date_start, serv_date_end, serv_duree)";
        $sql .= " SELECT DISTINCT rowid, null, null, 0 FROM " . MAIN_DB_PREFIX . "contratdet";
        $sql .= " WHERE rowid NOT IN (SELECT fk_object FROM " . MAIN_DB_PREFIX . "contratdet_extrafields)";
        $resql = $db->query($sql);
        return $resql ? 1 : 0;
    }

    /**
     * Delete Contratplus_closed extrafield of a contract and transfer the value
     * to the contract status field (2 for closed)
     *
     * @return void
     */
    private function deleteClosedContractExtrafields()
    {
        $extrafields = new Extrafields($this->db);

        // Transfer data from extrafield to contract status
        $sql = "UPDATE ".MAIN_DB_PREFIX."contrat set statut = 2 WHERE rowid IN (";
        $sql .= "SELECT fk_object FROM ".MAIN_DB_PREFIX."contrat_extrafields WHERE contratplus_closed = 1)";
        $res = $this->db->query($sql);

        if ($res) {
            // Delete extrafield
            $extrafields->delete('contratplus_closed', 'contrat');
        }
    }

	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories
	 *
     *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
	    global $conf;
		$sql = array();

		$result = $this->_load_tables('/contratplus/sql/');
        if ($result < 0) return -1; // Do not activate module if not allowed errors found on module SQL queries (the _load_table run sql with run_sql with error allowed parameter to 'default')


        //$this->deleteExtrafields();

        // Issue 224 : delete extrafields & close all closed contract
        $this->deleteClosedContractExtrafields();

		$this->createExtrafields();

		$this->initExtrafields();

        if (!isset($conf->global->CONTRATPLUS_SUPPLIER_CONTRACT))
            dolibarr_set_const($this->db, 'CONTRATPLUS_SUPPLIER_CONTRACT', 0, '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_OPTION))
            dolibarr_set_const($this->db, 'CONTRATPLUS_OPTION', 0, '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_BYPASS))
            dolibarr_set_const($this->db, 'CONTRATPLUS_BYPASS', 0, '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_COND_PAIEMENT_DEFAUT))
			dolibarr_set_const($this->db, 'CONTRATPLUS_COND_PAIEMENT_DEFAUT', 1, '', $conf->entity);

		if (!isset($conf->global->CONTRATPLUS_COND_PAIEMENT_DEFAUT))
			dolibarr_set_const($this->db, 'CONTRATPLUS_TYPE_PAIEMENT_DEFAUT', 1, '', $conf->entity);

		if (!isset($conf->global->CONTRATPLUS_EMAIL_TEMPLATE))
            dolibarr_set_const($this->db, 'CONTRATPLUS_EMAIL_TEMPLATE', '', '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_EMAIL_TEMPLATE_TOPIC))
            dolibarr_set_const($this->db, 'CONTRATPLUS_EMAIL_TEMPLATE_TOPIC', '', '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_EMAIL_TEMPLATE_CONTENT))
            dolibarr_set_const($this->db, 'CONTRATPLUS_EMAIL_TEMPLATE_CONTENT', '', '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_IS_CONFIGURED))
            dolibarr_set_const($this->db, 'CONTRATPLUS_IS_CONFIGURED', 0, '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_IS_MAIL_CONFIGURED))
            dolibarr_set_const($this->db, 'CONTRATPLUS_IS_MAIL_CONFIGURED', 0, '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_CRON_DATE))
            dolibarr_set_const($this->db, 'CONTRATPLUS_CRON_DATE', 0, '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_CRON_LOCK))
            dolibarr_set_const($this->db, 'CONTRATPLUS_CRON_LOCK', 0, '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_WIZARD_INDEX))
            dolibarr_set_const($this->db, 'CONTRATPLUS_WIZARD_INDEX', '', 'chaine', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_WIZARD_CONTRAT))
            dolibarr_set_const($this->db, 'CONTRATPLUS_WIZARD_CONTRAT', '', 'chaine', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_WIZARD_SERVICE))
            dolibarr_set_const($this->db, 'CONTRATPLUS_WIZARD_SERVICE', '', 'chaine', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_PROPAL_EXTRA_DUREE))
            dolibarr_set_const($this->db, 'CONTRATPLUS_PROPAL_EXTRA_DUREE', 1, '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_FACTURE_EXTRA_DUREE))
            dolibarr_set_const($this->db, 'CONTRATPLUS_FACTURE_EXTRA_DUREE', 1, '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_FACTURE_EXTRA_DATE_START))
            dolibarr_set_const($this->db, 'CONTRATPLUS_FACTURE_EXTRA_DATE_START', 1, '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_FACTURE_EXTRA_DATE_END))
            dolibarr_set_const($this->db, 'CONTRATPLUS_FACTURE_EXTRA_DATE_END', 1, '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_DATEEND_ACTIVE))
            dolibarr_set_const($this->db, 'CONTRATPLUS_DATEEND_ACTIVE', 1, '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_DATESTART_CONTRACT))
            dolibarr_set_const($this->db, 'CONTRATPLUS_DATESTART_CONTRACT', 1, '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_DATEEND_CONTRACT))
            dolibarr_set_const($this->db, 'CONTRATPLUS_DATEEND_CONTRACT', 1, '', $conf->entity);

        if (!isset($conf->global->CONTRATPLUS_DUREE_CONTRACT))
            dolibarr_set_const($this->db, 'CONTRATPLUS_DUREE_CONTRACT', 1, '', $conf->entity);

        // Sepa link
        if (!isset($conf->global->CONTRATPLUS_SEPA_LINK))
            dolibarr_set_const($this->db, 'CONTRATPLUS_SEPA_LINK', 0, 'integer', $conf->entity);

        return $this->_init($sql, $options);
	}

	/**
	 * Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * @param      string	$options    Options when enabling module ('', 'noboxes')
	 * @return     int             	1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}
}
