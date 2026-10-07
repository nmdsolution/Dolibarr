<?php
/* Copyright (C) 2004-2014	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012	Regis Houssin		<regis.houssin@inodbox.com>
 * Copyright (C) 2008		Raphael Bertrand	<raphael.bertrand@resultic.fr>
 * Copyright (C) 2010-2013	Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2012      	Christophe Battarel <christophe.battarel@altairis.fr>
 * Copyright (C) 2012       Cedric Salvador     <csalvador@gpcsolutions.fr>
 * Copyright (C) 2015       Marcos García       <marcosgdf@gmail.com>
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
 *	\file       htdocs/core/modules/commande/doc/pdf_proforma.modules.php
 *	\ingroup    commande
 *	\brief      File of Class to generate PDF orders with template Proforma
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/commande/doc/pdf_eratosthene.modules.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';


/**
 *	Class to generate PDF orders with template Proforma
 */
class pdf_proforma extends pdf_eratosthene
{
	/**
	 * @var int Extra height (mm) reserved above the footer for the KMS tagline (read by pdf_eratosthene)
	 */
	public $extraheightforfooter = 22;

	/**
	 * @var float Height (mm) of the KMS top band image, printed at the top right edge of the page
	 */
	public $kmsHeaderBandHeight = 24;

	/**
	 * @var float Height (mm) of the KMS bottom band image, printed full width at the bottom of the page
	 */
	public $kmsFooterBandHeight = 23.1;

	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		global $conf, $langs, $mysoc;

		parent::__construct($db);

		$this->name = "proforma";
		$this->description = $langs->trans('PDFProformaDescription');

		// Leave room for the KMS top band (header) and bottom band (footer) printed on every page
		$this->marge_haute = $this->kmsHeaderBandHeight + 4;
		$this->marge_basse = $this->kmsFooterBandHeight + 4;
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *  Show top header of page.
	 *
	 *  @param	TCPDF		$pdf     		Object PDF
	 *  @param  Commande	$object     	Object to show
	 *  @param  int	    	$showaddress    0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @param  Translate	$outputlangsbis	Object lang for output bis
	 *  @param	string		$titlekey		Translation key to show as title of document
	 *  @return	int                         Return topshift value
	 */
	protected function _pagehead(&$pdf, $object, $showaddress, $outputlangs, $outputlangsbis = null, $titlekey = "InvoiceProForma")
	{
		// phpcs:enable
		global $conf, $langs, $hookmanager;

		$topshift = parent::_pagehead($pdf, $object, $showaddress, $outputlangs, $outputlangsbis, $titlekey);

		// KMS top band (company activities and tax/trade register numbers), 112 mm wide, flush with the right edge
		$band = DOL_DOCUMENT_ROOT.'/theme/common/kms/kms_header_band.png';
		if (is_readable($band)) {
			$w = 112;
			$pdf->Image($band, $this->page_largeur - $w, 0, $w, $this->kmsHeaderBandHeight);
		}

		return $topshift;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *  Show footer of page, preceded by the KMS tagline and customer appreciation texts.
	 *
	 *  @param	TCPDF		$pdf     			PDF
	 *  @param	Commande	$object				Object to show
	 *  @param	Translate	$outputlangs		Object lang for output
	 *  @param	int			$hidefreetext		1=Hide free text
	 *  @return	int								Return height of bottom margin including footer text
	 */
	protected function _pagefoot(&$pdf, $object, $outputlangs, $hidefreetext = 0)
	{
		// phpcs:enable
		// KMS bottom band (address, phones, website, email), full page width, drawn first so texts stay above it
		$band = DOL_DOCUMENT_ROOT.'/theme/common/kms/kms_footer_band.png';
		if (is_readable($band)) {
			$pdf->Image($band, 0, $this->page_hauteur - $this->kmsFooterBandHeight, $this->page_largeur, $this->kmsFooterBandHeight);
		}

		$footerheight = parent::_pagefoot($pdf, $object, $outputlangs, $hidefreetext);

		$outputlangs->load('sendings');
		$default_font_size = pdf_getPDFFontSize($outputlangs);

		$wtext = 110;
		$xtext = ($this->page_largeur - $wtext) / 2;
		$ytext = $this->page_hauteur - $footerheight - 22;

		// Tagline (black, bold) then customer appreciation (red, bold)
		$pdf->SetFont('', 'B', $default_font_size - 1);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY($xtext, $ytext);
		$pdf->MultiCell($wtext, 4, $outputlangs->transnoentities("TagLine"), 0, 'C');

		$pdf->SetTextColor(200, 0, 0);
		$pdf->SetXY($xtext, $pdf->GetY() + 2);
		$pdf->MultiCell($wtext, 4, $outputlangs->transnoentities("CustomerApreciation"), 0, 'C');
		$pdf->SetTextColor(0, 0, 0);

		return $footerheight;
	}
}
