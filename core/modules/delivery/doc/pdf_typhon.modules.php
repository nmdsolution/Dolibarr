<?php
/* Copyright (C) 2004-2014 Laurent Destailleur   <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2014 Regis Houssin         <regis.houssin@inodbox.com>
 * Copyright (C) 2007      Franky Van Liedekerke <franky.van.liedekerke@telenet.be>
 * Copyright (C) 2008      Chiptronik
 * Copyright (C) 2011-2021 Philippe Grand        <philippe.grand@atoo-net.com>
 * Copyright (C) 2015      Marcos García         <marcosgdf@gmail.com>
 * Copyright (C) 2024      KMS - Kasi Medical Solutions (customisation)
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
 * or see https://www.gnu.org/
 */

/**
 *	\file       htdocs/core/modules/delivery/doc/pdf_typhon.modules.php
 *	\ingroup    livraison
 *	\brief      Fichier de la classe pour générer les bons de livraison avec le modèle Typhon (KMS)
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/delivery/modules_delivery.php';
require_once DOL_DOCUMENT_ROOT.'/delivery/class/delivery.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';


/**
 *	Class to build Delivery Order documents with typhon model
 */
class pdf_typhon extends ModelePDFDeliveryOrder
{
	/**
	 * @var DoliDb Database handler
	 */
	public $db;

	/**
	 * @var string model name
	 */
	public $name;

	/**
	 * @var string model description (short text)
	 */
	public $description;

	/**
	 * @var int     Save the name of generated file as the main doc when generating a doc with this template
	 */
	public $update_main_doc_field;

	/**
	 * @var string document type
	 */
	public $type;

	/**
	 * @var array Minimum version of PHP required by module.
	 * e.g.: PHP >= 7.0 = array(7, 0)
	 */
	public $phpmin = array(7, 0);

	/**
	 * Dolibarr version of the loaded document
	 * @var string
	 */
	public $version = 'dolibarr';

	/**
	 * @var int page_largeur
	 */
	public $page_largeur;

	/**
	 * @var int page_hauteur
	 */
	public $page_hauteur;

	/**
	 * @var array format
	 */
	public $format;

	/**
	 * @var int marge_gauche
	 */
	public $marge_gauche;

	/**
	 * @var int marge_droite
	 */
	public $marge_droite;

	/**
	 * @var int marge_haute
	 */
	public $marge_haute;

	/**
	 * @var int marge_basse
	 */
	public $marge_basse;

	/**
	 * Issuer
	 * @var Societe Object that emits
	 */
	public $emetteur;


	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		global $conf, $langs, $mysoc;

		// Translations
		$langs->loadLangs(array("main", "bills", "sendings", "companies"));

		$this->db = $db;
		$this->name = "Typhon";
		$this->description = $langs->trans("DocumentModelTyphon");
		$this->update_main_doc_field = 1; // Save the name of generated file as the main doc when generating a doc with this template

		// Page size for A4 format
		$this->type = 'pdf';
		$formatarray = pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array($this->page_largeur, $this->page_hauteur);
		$this->marge_gauche = getDolGlobalInt('MAIN_PDF_MARGIN_LEFT', 10);
		$this->marge_droite = getDolGlobalInt('MAIN_PDF_MARGIN_RIGHT', 10);
		$this->marge_haute  = getDolGlobalInt('MAIN_PDF_MARGIN_TOP', 10);
		$this->marge_basse  = getDolGlobalInt('MAIN_PDF_MARGIN_BOTTOM', 10);

		$this->option_logo = 1; // Display logo
		$this->option_tva  = 1; // Manage the vat option

		// Get source company
		$this->emetteur = $mysoc;
		if (empty($this->emetteur->country_code)) {
			$this->emetteur->country_code = substr($langs->defaultlang, -2);
		}

		// Define position of columns
		$this->posxdesc        = $this->marge_gauche + 1;
		$this->posxcomm        = 100;
		$this->posxqty         = 165;
		$this->posxremainingqty = 120;

		if ($this->page_largeur < 210) { // To work with US executive format
			$this->posxcomm         -= 20;
			$this->posxqty          -= 20;
		}

		$this->tva                    = array();
		$this->tva_array              = array();
		$this->localtax1              = array();
		$this->localtax2              = array();
		$this->atleastoneratenotnull  = 0;
		$this->atleastonediscount     = 0;
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Function to build pdf onto disk
	 *
	 *  @param      Delivery	$object				Object to generate
	 *  @param      Translate	$outputlangs		Lang output object
	 *  @param      string		$srctemplatepath	Full path of source filename for generator using a template file
	 *  @param      int			$hidedetails		Do not show line details
	 *  @param      int			$hidedesc			Do not show desc
	 *  @param      int			$hideref			Do not show ref
	 *  @return     int             			1=OK, 0=KO
	 */
	public function write_file($object, $outputlangs, $srctemplatepath = '', $hidedetails = 0, $hidedesc = 0, $hideref = 0)
	{
		// phpcs:enable
		global $user, $langs, $conf, $mysoc, $hookmanager;

		if (!is_object($outputlangs)) {
			$outputlangs = $langs;
		}
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (!empty($conf->global->MAIN_USE_FPDF)) {
			$outputlangs->charset_output = 'ISO-8859-1';
		}

		// Load translation files required by the page
		$outputlangs->loadLangs(array("main", "dict", "companies", "bills", "products", "sendings", "deliveries", "orders"));

		if ($conf->expedition->dir_output) {
			$object->fetch_thirdparty();

			// Definition of $dir and $file
			if ($object->specimen) {
				$dir  = $conf->expedition->dir_output."/receipt";
				$file = $dir."/SPECIMEN.pdf";
			} else {
				$objectref = dol_sanitizeFileName($object->ref);
				$dir  = $conf->expedition->dir_output."/receipt/".$objectref;
				$file = $dir."/".$objectref.".pdf";
			}

			if (!file_exists($dir)) {
				if (dol_mkdir($dir) < 0) {
					$this->error = $langs->transnoentities("ErrorCanNotCreateDir", $dir);
					return 0;
				}
			}

			if (file_exists($dir)) {
				// Add pdfgeneration hook
				if (!is_object($hookmanager)) {
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager = new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters = array('file' => $file, 'object' => $object, 'outputlangs' => $outputlangs);
				global $action;
				$reshook = $hookmanager->executeHooks('beforePDFCreation', $parameters, $object, $action);

				$nblines = count($object->lines);

				// Create pdf instance
				$pdf = pdf_getInstance($this->format);
				$default_font_size  = pdf_getPDFFontSize($outputlangs);
				$heightforinfotot   = 52; // Place pour signatures, slogan et remerciement sous le tableau
				$heightforfreetext  = (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT) ? $conf->global->MAIN_PDF_FREETEXT_HEIGHT : 5);
				$heightforfooter    = $this->marge_basse + 8;
				if (getDolGlobalInt('MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS') > 0) {
					$heightforfooter += 6;
				}
				$pdf->SetAutoPageBreak(1, 0);

				if (class_exists('TCPDF')) {
					$pdf->setPrintHeader(false);
					$pdf->setPrintFooter(false);
				}
				$pdf->SetFont(pdf_getPDFFont($outputlangs));
				// Set path to the background PDF File
				if (!empty($conf->global->MAIN_ADD_PDF_BACKGROUND)) {
					$pagecount = $pdf->setSourceFile($conf->mycompany->dir_output.'/'.$conf->global->MAIN_ADD_PDF_BACKGROUND);
					$tplidx = $pdf->importPage(1);
				}

				// We get the shipment that is the origin of delivery receipt
				$expedition = new Expedition($this->db);
				$result = $expedition->fetch($object->origin_id);
				// Now we get the order that is origin of shipment
				$commande = new Commande($this->db);
				if ($expedition->origin == 'commande') {
					$commande->fetch($expedition->origin_id);
				}
				$object->commande = $commande;
				$object->commande->loadExpeditions();

				$pdf->Open();
				$pagenb = 0;
				$pdf->SetDrawColor(128, 128, 128);

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("DeliveryOrder"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("DeliveryOrder"));
				if (!empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) {
					$pdf->SetCompression(false);
				}

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);

				// New page
				$pdf->AddPage();
				if (!empty($tplidx)) {
					$pdf->useTemplate($tplidx);
				}
				$pagenb++;
				$this->_pagehead($pdf, $object, 1, $outputlangs);
				$pdf->SetFont('', '', $default_font_size - 1);
				$pdf->MultiCell(0, 3, '');
				$pdf->SetTextColor(0, 0, 0);

				$tab_top          = 90;
				$tab_top_newpage  = (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD) ? 42 : 10);
				$tab_height       = 130;
				$tab_height_newpage = 150;

				// Incoterm
				$height_incoterms = 0;
				if ($conf->incoterm->enabled) {
					$desc_incoterms = $object->getIncotermsForPDF();
					if ($desc_incoterms) {
						$tab_top = 88;
						$pdf->SetFont('', '', $default_font_size - 1);
						$pdf->writeHTMLCell(190, 3, $this->posxdesc - 1, $tab_top - 1, dol_htmlentitiesbr($desc_incoterms), 0, 1);
						$nexY = $pdf->GetY();
						$height_incoterms = $nexY - $tab_top;
						$pdf->SetDrawColor(192, 192, 192);
						$pdf->Rect($this->marge_gauche, $tab_top - 1, $this->page_largeur - $this->marge_gauche - $this->marge_droite, $height_incoterms + 1);
						$tab_top = $nexY + 6;
						$height_incoterms += 4;
					}
				}

				// Affiche notes
				if (!empty($object->note_public)) {
					$tab_top = 88 + $height_incoterms;
					$pdf->SetFont('', '', $default_font_size - 1);
					$pdf->writeHTMLCell(190, 3, $this->posxdesc - 1, $tab_top, dol_htmlentitiesbr($object->note_public), 0, 1);
					$nexY         = $pdf->GetY();
					$height_note  = $nexY - $tab_top;
					$pdf->SetDrawColor(192, 192, 192);
					$pdf->Rect($this->marge_gauche, $tab_top - 1, $this->page_largeur - $this->marge_gauche - $this->marge_droite, $height_note + 1);
					$tab_height = $tab_height - $height_note;
					$tab_top    = $nexY + 6;
				} else {
					$height_note = 0;
				}

				$iniY = $tab_top + 11;
				$curY = $tab_top + 11;
				$nexY = $tab_top + 11;

				// Loop on each lines
				for ($i = 0; $i < $nblines; $i++) {
					$curY = $nexY;
					$pdf->SetFont('', '', $default_font_size - 1);
					$pdf->SetTextColor(0, 0, 0);

					$pdf->setTopMargin($tab_top_newpage);
					$pdf->setPageOrientation('', 1, $heightforfooter + $heightforfreetext + $heightforinfotot);
					$pageposbefore = $pdf->getPage();

					$curX = $this->posxdesc - 1;

					$showpricebeforepagebreak = 1;

					$pdf->startTransaction();
					pdf_writelinedesc($pdf, $object, $i, $outputlangs, $this->posxcomm - $curX, 3, $curX, $curY, $hideref, $hidedesc);
					$pageposafter = $pdf->getPage();
					if ($pageposafter > $pageposbefore) { // There is a pagebreak
						$pdf->rollbackTransaction(true);
						$pageposafter = $pageposbefore;
						$pdf->setPageOrientation('', 1, $heightforfooter);
						pdf_writelinedesc($pdf, $object, $i, $outputlangs, $this->posxcomm - $curX, 4, $curX, $curY, $hideref, $hidedesc);
						$posyafter = $pdf->GetY();
						if ($posyafter > ($this->page_hauteur - ($heightforfooter + $heightforfreetext + $heightforinfotot))) {
							if ($i == ($nblines - 1)) {
								$pdf->AddPage('', '', true);
								if (!empty($tplidx)) {
									$pdf->useTemplate($tplidx);
								}
								if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) {
									$this->_pagehead($pdf, $object, 0, $outputlangs);
								}
								$pdf->setPage($pageposafter + 1);
							}
						} else {
							if (!empty($conf->global->MAIN_PDF_DATA_ON_FIRST_PAGE)) {
								$showpricebeforepagebreak = 1;
							} else {
								$showpricebeforepagebreak = 0;
							}
						}
					} else {
						$pdf->commitTransaction();
					}

					$nexY = $pdf->GetY();
					$pageposafter = $pdf->getPage();
					$pdf->setPage($pageposbefore);
					$pdf->setTopMargin($this->marge_haute);
					$pdf->setPageOrientation('', 1, 0);

					if ($pageposafter > $pageposbefore && empty($showpricebeforepagebreak)) {
						$pdf->setPage($pageposafter);
						$curY = $tab_top_newpage;
					}

					$pdf->SetFont('', '', $default_font_size - 1);

					// Quantity shipped
					$pdf->SetXY($this->posxcomm, $curY);
					$pdf->MultiCell($this->posxqty - $this->posxcomm - $this->marge_droite * 4.5, 3, $object->lines[$i]->qty_shipped, 0, 'R');

					// Remaining to ship
					$pdf->SetXY($this->posxremainingqty, $curY);
					$qtyRemaining = $object->lines[$i]->qty_asked - $object->commande->expeditions[$object->lines[$i]->fk_origin_line];
					$pdf->MultiCell($this->marge_droite + 5, 3, $qtyRemaining, 0, 'R');

					// Add line separator
					if (!empty($conf->global->MAIN_PDF_DASH_BETWEEN_LINES) && $i < ($nblines - 1)) {
						$pdf->setPage($pageposafter);
						$pdf->SetLineStyle(array('dash' => '1,1', 'color' => array(80, 80, 80)));
						$pdf->line($this->marge_gauche, $nexY + 1, $this->page_largeur - $this->marge_droite, $nexY + 1);
						$pdf->SetLineStyle(array('dash' => 0));
					}

					$nexY += 2;

					// Detect if some page were added automatically and output _tableau for past pages
					while ($pagenb < $pageposafter) {
						$pdf->setPage($pagenb);
						if ($pagenb == 1) {
							$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1);
						} else {
							$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 1, 1);
						}
						$this->_pagefoot($pdf, $object, $outputlangs, 1);
						$pagenb++;
						$pdf->setPage($pagenb);
						$pdf->setPageOrientation('', 1, 0);
						if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) {
							$this->_pagehead($pdf, $object, 0, $outputlangs);
						}
					}
					if (isset($object->lines[$i + 1]->pagebreak) && $object->lines[$i + 1]->pagebreak) {
						if ($pagenb == 1) {
							$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1);
						} else {
							$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 1, 1);
						}
						$this->_pagefoot($pdf, $object, $outputlangs, 1);
						$pdf->AddPage();
						if (!empty($tplidx)) {
							$pdf->useTemplate($tplidx);
						}
						$pagenb++;
						if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) {
							$this->_pagehead($pdf, $object, 0, $outputlangs);
						}
					}
				}

				// Show square
				if ($pagenb == 1) {
					$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforinfotot - $heightforfreetext - $heightforfooter, 0, $outputlangs, 0, 0);
					$bottomlasttab = $this->page_hauteur - $heightforinfotot - $heightforfreetext - $heightforfooter + 1;
				} else {
					$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforinfotot - $heightforfreetext - $heightforfooter, 0, $outputlangs, 1, 0);
					$bottomlasttab = $this->page_hauteur - $heightforinfotot - $heightforfreetext - $heightforfooter + 1;
				}

				// Tableau info (zone signatures)
				$this->_tableau_info($pdf, $object, $bottomlasttab, $outputlangs);

				// Pied de page
				$this->_pagefoot($pdf, $object, $outputlangs);

				if (method_exists($pdf, 'AliasNbPages')) {
					$pdf->AliasNbPages();
				}

				$pdf->Close();
				$pdf->Output($file, 'F');

				// Add pdfgeneration hook
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters = array('file' => $file, 'object' => $object, 'outputlangs' => $outputlangs);
				$reshook = $hookmanager->executeHooks('afterPDFCreation', $parameters, $this, $action);
				if ($reshook < 0) {
					$this->error  = $hookmanager->error;
					$this->errors = $hookmanager->errors;
				}

				if (!empty($conf->global->MAIN_UMASK)) {
					@chmod($file, octdec($conf->global->MAIN_UMASK));
				}

				$this->result = array('fullpath' => $file);

				return 1; // No error
			} else {
				$this->error = $langs->transnoentities("ErrorCanNotCreateDir", $dir);
				return 0;
			}
		}

		$this->error = $langs->transnoentities("ErrorConstantNotDefined", "LIVRAISON_OUTPUTDIR");
		return 0;
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *   Show miscellaneous information (zone de signature)
	 *
	 *   @param		TCPDF		$pdf     		Object PDF
	 *   @param		Delivery	$object			Object to show
	 *   @param		int			$posy			Y
	 *   @param		Translate	$outputlangs	Langs object
	 *   @return	void
	 */
	protected function _tableau_info(&$pdf, $object, $posy, $outputlangs)
	{
		// phpcs:enable
		global $conf, $mysoc;
		$default_font_size = pdf_getPDFFontSize($outputlangs);

		$posy += 3; // Sous le tableau des lignes
		$larg_sign = 70;
		$xleft = $this->marge_gauche;
		$xright = $this->page_largeur - $this->marge_droite - $larg_sign;

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('', '', $default_font_size - 1);

		// Signature KMS
		$pdf->SetXY($xleft, $posy);
		$pdf->MultiCell($larg_sign, 4, $outputlangs->transnoentities("SignatureCompanyNameAndSignature"), 0, 'L');
		$pdf->Rect($xleft, $posy + 5, $larg_sign, 15);

		// Signature Client
		$pdf->SetXY($xright, $posy);
		$pdf->MultiCell($larg_sign, 4, $outputlangs->transnoentities("SignatureCustomerNameAndSignature"), 0, 'L');
		$pdf->Rect($xright, $posy + 5, $larg_sign, 15);

		// Slogan (noir, gras) puis remerciement (rouge, gras), centres au-dessus du pied de page
		$wtext = 110;
		$xtext = ($this->page_largeur - $wtext) / 2;

		$pdf->SetFont('', 'B', $default_font_size - 1);
		$pdf->SetXY($xtext, $posy + 23);
		$pdf->MultiCell($wtext, 4, $outputlangs->transnoentities("TagLine"), 0, 'C');

		$pdf->SetTextColor(200, 0, 0);
		$pdf->SetXY($xtext, $pdf->GetY() + 2);
		$pdf->MultiCell($wtext, 4, $outputlangs->transnoentities("CustomerApreciation"), 0, 'C');
		$pdf->SetTextColor(0, 0, 0);
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *   Show table for lines
	 *
	 *   @param		TCPDF		$pdf     		Object PDF
	 *   @param		string		$tab_top		Top position of table
	 *   @param		string		$tab_height		Height of table (rectangle)
	 *   @param		int			$nexY			Y (not used)
	 *   @param		Translate	$outputlangs	Langs object
	 *   @param		int			$hidetop		1=Hide top bar of array and title, 0=Hide nothing, -1=Hide only title
	 *   @param		int			$hidebottom		Hide bottom bar of array
	 *   @return	void
	 */
	protected function _tableau(&$pdf, $tab_top, $tab_height, $nexY, $outputlangs, $hidetop = 0, $hidebottom = 0)
	{
		// phpcs:enable
		global $conf, $mysoc;

		$hidebottom = 0;
		if ($hidetop) {
			$hidetop = -1;
		}

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('', '', $default_font_size - 2);

		// Output Rec
		$this->printRect($pdf, $this->marge_gauche, $tab_top, $this->page_largeur - $this->marge_gauche - $this->marge_droite, $tab_height, $hidetop, $hidebottom);

		if (empty($hidetop)) {
			$pdf->line($this->marge_gauche, $tab_top + 10, $this->page_largeur - $this->marge_droite, $tab_top + 10);
		}

		$pdf->SetDrawColor(128, 128, 128);
		$pdf->SetFont('', '', $default_font_size - 1);

		// Designation
		if (empty($hidetop)) {
			$pdf->SetXY($this->posxdesc - 1, $tab_top + 1);
			$pdf->MultiCell($this->posxcomm - $this->posxdesc, 2, $outputlangs->transnoentities("Designation"), '', 'L');
		}

		// Qty livrée
		$pdf->line($this->posxcomm, $tab_top, $this->posxcomm, $tab_top + $tab_height);
		if (empty($hidetop)) {
			$pdf->SetXY($this->posxcomm, $tab_top + 1);
			$pdf->MultiCell($this->posxqty - $this->posxcomm - $this->marge_droite * 4.5, 2, $outputlangs->transnoentities("QtyDelivered"), '', 'R');
		}

		// Reste à livrer
		$pdf->line($this->posxcomm + $this->marge_droite * 2, $tab_top, $this->posxcomm + $this->marge_droite * 2, $tab_top + $tab_height);
		if (empty($hidetop)) {
			$pdf->SetXY($this->posxremainingqty, $tab_top + 1);
			$pdf->MultiCell($this->marge_droite + 5, 2, $outputlangs->transnoentities("KeepToShipShort"), '', 'R');
		}

		// Commentaires
		$pdf->line($this->posxremainingqty + 15, $tab_top, $this->posxremainingqty + 15, $tab_top + $tab_height);
		if (empty($hidetop)) {
			$pdf->SetXY($this->posxremainingqty + 16, $tab_top + 1);
			$pdf->MultiCell($this->page_largeur - $this->marge_droite - ($this->posxremainingqty + 16), 2, $outputlangs->transnoentities("Comments"), '', 'C');
		}
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *  Show top header of page.
	 *
	 *  @param	TCPDF		$pdf     		Object PDF
	 *  @param  Delivery	$object     	Object to show
	 *  @param  int	    	$showaddress    0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @return	void
	 */
	protected function _pagehead(&$pdf, $object, $showaddress, $outputlangs)
	{
		// phpcs:enable
		global $conf, $langs, $hookmanager;

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		pdf_pagehead($pdf, $outputlangs, $this->page_hauteur);

		// Show Draft Watermark
		if ($object->statut == 0 && (!empty($conf->global->COMMANDE_DRAFT_WATERMARK))) {
			pdf_watermark($pdf, $outputlangs, $this->page_hauteur, $this->page_largeur, 'mm', $conf->global->COMMANDE_DRAFT_WATERMARK);
		}

		$pdf->SetTextColor(0, 0, 60);
		$pdf->SetFont('', 'B', $default_font_size + 3);

		$posy = $this->marge_haute;
		$posx = $this->page_largeur - $this->marge_droite - 100;

		$pdf->SetXY(0, 0);

		// ---- Image d'en-tête KMS ----
		$headerImage = DOL_DOCUMENT_ROOT.'/docs/header.jpg';
		$height = 0;
		if (is_readable($headerImage)) {
			$height = pdf_getHeightForLogo($headerImage);
			$pdf->Image($headerImage, $this->marge_gauche, $posy - 7, $this->page_largeur - 20, $height);
		}

		$posy = $height + 2;

		// Titre : BON DE LIVRAISON
		$pdf->SetFont('', 'B', $default_font_size + 2);
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$pdf->MultiCell(100, 3, $outputlangs->transnoentities("BonDeLivraison")." ".$outputlangs->convToOutputCharset($object->ref), '', 'R');

		$pdf->SetFont('', '', $default_font_size + 2);

		$posy += 5;
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		if ($object->date_valid) {
			$pdf->MultiCell(100, 4, $outputlangs->transnoentities("Date")." : ".dol_print_date($object->date_delivery, "%d %b %Y", false, $outputlangs, true), '', 'R');
		} else {
			$pdf->SetTextColor(255, 0, 0);
			$pdf->MultiCell(100, 4, $outputlangs->transnoentities("DeliveryNotValidated"), '', 'R');
			$pdf->SetTextColor(0, 0, 60);
		}

		if ($object->thirdparty->code_client) {
			$posy += 5;
			$pdf->SetXY($posx, $posy);
			$pdf->SetTextColor(0, 0, 60);
			$pdf->MultiCell(100, 3, $outputlangs->transnoentities("CustomerCode")." : ".$outputlangs->transnoentities($object->thirdparty->code_client), '', 'R');
		}

		$pdf->SetTextColor(0, 0, 60);

		$posy += 2;

		// Ref commande liée
		$reftoshow = '';
		$object->fetchObjectLinked();
		if (!empty($object->linkedObjects)) {
			foreach ($object->linkedObjects as $objecttype => $objects) {
				if ($objecttype == 'shipping') {
					foreach ($objects as $elementobject) {
						$reftoshow = $outputlangs->transnoentities("RefOrder").' : '.$outputlangs->convToOutputCharset($elementobject->ref);
					}
				}
			}
		}
		if ($reftoshow) {
			$posy += 3;
			$pdf->SetXY($posx, $posy);
			$pdf->SetFont('', '', $default_font_size - 2);
			$pdf->MultiCell(100, $posy, $reftoshow, '', 'R');
		}

		if ($showaddress) {
			// Sender properties
			$carac_emetteur = pdf_build_address($outputlangs, $this->emetteur, $object->thirdparty, '', 0, 'source', $object);

			// Show sender
			$posy = 50;
			$posx = $this->marge_gauche;
			if (!empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) {
				$posx = $this->page_largeur - $this->marge_droite - 80;
			}
			$hautcadre = 35;

			// Show sender frame
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size - 2);
			$pdf->SetXY($posx, $posy);
			$pdf->SetFillColor(230, 230, 230);
			$pdf->MultiCell(82, $hautcadre, "", 0, 'R', 1);
			$pdf->SetTextColor(0, 0, 60);

			// Show sender name
			$pdf->SetXY($posx + 2, $posy + 3);
			$pdf->SetFont('', 'B', $default_font_size);
			$pdf->MultiCell(80, 4, $outputlangs->convToOutputCharset($this->emetteur->name), 0, 'L');
			$posy = $pdf->getY();

			// Show sender information
			$pdf->SetXY($posx + 2, $posy);
			$pdf->SetFont('', '', $default_font_size - 1);
			$pdf->MultiCell(80, 4, $carac_emetteur, 0, 'L');

			// Client destinataire
			$posy = 50;
			$posx = 100;
			if (!empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) {
				$posx = $this->marge_gauche;
			}
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size - 2);
			$pdf->SetXY($posx, $posy - 5);
			$pdf->MultiCell(80, 5, $outputlangs->transnoentities("DeliveryAddress").":", 0, 'L');

			// If SHIPPING contact defined on order, we use it
			$usecontact = false;
			$arrayidcontact = $object->commande->getIdContact('external', 'SHIPPING');
			if ($arrayidcontact && count($arrayidcontact) > 0) {
				$usecontact = true;
				$result = $object->fetch_contact($arrayidcontact[0]);
			}

			// Recipient name
			if ($usecontact && ($object->contact->socid != $object->thirdparty->id && (!isset($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT) || !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)))) {
				$thirdparty = $object->contact;
			} else {
				$thirdparty = $object->thirdparty;
			}

			$carac_client_name = pdfBuildThirdpartyName($thirdparty, $outputlangs);
			$carac_client = pdf_build_address($outputlangs, $this->emetteur, $object->thirdparty, ($usecontact ? $object->contact : ''), $usecontact, 'target', $object);

			// Show recipient
			$widthrecbox = 100;
			if ($this->page_largeur < 210) {
				$widthrecbox = 84;
			}
			$posy = 50;
			$posx = $this->page_largeur - $this->marge_droite - $widthrecbox;
			if (!empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) {
				$posx = $this->marge_gauche;
			}

			// Show recipient frame
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size - 2);
			$pdf->SetXY($posx + 2, $posy + 2);
			$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre);

			// Show recipient name
			$pdf->SetXY($posx + 2, $posy + 3);
			$pdf->SetFont('', 'B', $default_font_size);
			$pdf->MultiCell($widthrecbox, 4, $carac_client_name, 0, 'L');

			$posy = $pdf->getY();

			// Show recipient information
			$pdf->SetFont('', '', $default_font_size - 1);
			$pdf->SetXY($posx + 2, $posy);
			$pdf->MultiCell($widthrecbox, 4, $carac_client, 0, 'L');
		}

		$pdf->SetTextColor(0, 0, 60);
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *  Show footer of page. Need this->emetteur object
	 *
	 *  @param	TCPDF		$pdf     			PDF
	 *  @param	Delivery	$object				Object to show
	 *  @param	Translate	$outputlangs		Object lang for output
	 *  @param	int			$hidefreetext		1=Hide free text
	 *  @return	void
	 */
	protected function _pagefoot(&$pdf, $object, $outputlangs, $hidefreetext = 0)
	{
		// phpcs:enable
		global $conf, $mysoc;

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		// ---- Image de pied de page KMS ----
		// (la zone de signatures est dans _tableau_info, sous le tableau)
		$posy = $this->page_hauteur - 22;
		$footerImage = DOL_DOCUMENT_ROOT.'/docs/footer.jpg';
		if (is_readable($footerImage)) {
			$height = pdf_getHeightForLogo($footerImage);
			$height += 5;
			$pdf->Image($footerImage, $this->marge_gauche, $posy, $this->page_largeur - 20, $height);
		}
	}
}