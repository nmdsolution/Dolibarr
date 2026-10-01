<?php
/**
 * \file       custom/stockalert/class/pdf_stockalert.class.php
 * \ingroup    stockalert
 * \brief      PDF summary sheet of stock alerts and pending supplier back-orders
 */

require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

/**
 * Class to build the stock alert PDF (TCPDF)
 */
class PdfStockAlert
{
	/** @var DoliDB */
	public $db;

	/** @var float[] Column widths (mm), total 190 for A4 portrait with 10mm margins */
	private $w = array(38, 50, 17, 17, 20, 26, 22);

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
	 * Build the PDF and send it to the browser.
	 *
	 * @param  array     $rows      Rows from StockAlert::fetchAll()
	 * @param  Translate $outputlangs Language object
	 * @return void
	 */
	public function write($rows, $outputlangs)
	{
		global $conf, $mysoc, $user;

		$outputlangs->loadLangs(array("main", "products", "stockalert@stockalert"));

		$pdf = pdf_getInstance('A4');
		$pdf->SetCreator('Dolibarr '.DOL_VERSION);
		$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
		$pdf->SetTitle($outputlangs->transnoentities("StockAlertTitle"));
		$pdf->SetMargins(10, 10, 10);
		$pdf->SetAutoPageBreak(false);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->AddPage();

		$t = function ($s) use ($outputlangs) {
			return $outputlangs->convToOutputCharset($outputlangs->transnoentities($s));
		};
		$fmt = function ($n) {
			return price2num($n, 'MS');
		};

		// Institutional header: logo, company, print date/time, author
		$y = 10;
		$logo = $conf->mycompany->dir_output.'/logos/'.$mysoc->logo;
		if (!empty($mysoc->logo) && is_readable($logo)) {
			$height = pdf_getHeightForLogo($logo);
			$pdf->Image($logo, 10, $y, 0, min($height, 20));
		}
		$pdf->SetFont('', 'B', 12);
		$pdf->SetXY(100, $y);
		$pdf->MultiCell(100, 5, $outputlangs->convToOutputCharset($mysoc->name), 0, 'R');
		$pdf->SetFont('', '', 8);
		$pdf->SetXY(100, $y + 6);
		$pdf->MultiCell(100, 4, $t("StockAlertPrintedOn").' '.dol_print_date(dol_now(), 'dayhour', 'tzuser', $outputlangs)
			.' '.$t("StockAlertPrintedBy").' '.$outputlangs->convToOutputCharset($user->getFullName($outputlangs)), 0, 'R');
		$pdf->SetFont('', 'B', 14);
		$pdf->SetXY(10, $y + 24);
		$pdf->MultiCell(190, 7, $t("StockAlertTitle"), 0, 'C');
		$y = $y + 36;

		$parts = array(
			'StockAlertPart1' => array_filter($rows, function ($r) {
				return $r->is_alert;
			}),
			'StockAlertPart2' => array_filter($rows, function ($r) {
				return $r->reliquat > 0;
			}),
		);

		$heads = array('Ref', 'Label', 'StockAlertPhysical', 'StockAlertDesired', 'StockAlertReliquat', 'StockAlertDueDate', 'StockAlertSuggested');
		$bottom = 262; // keep room for signature zone

		$drawHead = function ($title) use ($pdf, &$y, $t, $heads) {
			$pdf->SetFont('', 'B', 10);
			$pdf->SetXY(10, $y);
			$pdf->Cell(190, 6, $t($title), 0, 1, 'L');
			$y += 7;
			$pdf->SetFont('', 'B', 7);
			$pdf->SetFillColor(230, 230, 230);
			$x = 10;
			foreach ($heads as $i => $h) {
				$pdf->SetXY($x, $y);
				$pdf->MultiCell($this->w[$i], 8, $t($h), 1, 'C', true, 0, '', '', true, 0, false, true, 8, 'M');
				$x += $this->w[$i];
			}
			$y += 8;
		};

		foreach ($parts as $title => $list) {
			if ($y > $bottom - 20) {
				$pdf->AddPage();
				$y = 10;
			}
			$drawHead($title);
			$pdf->SetFont('', '', 7);
			if (empty($list)) {
				$pdf->SetXY(10, $y);
				$pdf->Cell(190, 6, $t("StockAlertNothing"), 1, 1, 'C');
				$y += 6;
			}
			foreach ($list as $r) {
				if ($y > $bottom) {
					$pdf->AddPage();
					$y = 10;
					$drawHead($title);
					$pdf->SetFont('', '', 7);
				}
				$due = ($r->next_date ? dol_print_date($r->next_date, 'day', 'tzuser', $outputlangs) : '').($r->is_late ? ' !' : '');
				$cells = array(
					$r->ref,
					$r->label,
					$fmt($r->stock),
					$r->souhaitee === null ? '' : $fmt($r->souhaitee),
					$r->reliquat > 0 ? $fmt($r->reliquat) : '',
					$due,
					$fmt($r->to_order),
				);
				$x = 10;
				foreach ($cells as $i => $c) {
					$pdf->SetXY($x, $y);
					// Text too long for its column is squeezed horizontally instead of overflowing
					$pdf->Cell($this->w[$i], 6, $outputlangs->convToOutputCharset($c), 1, 0, $i < 2 ? 'L' : 'R', false, '', $i < 2 ? 1 : 0);
					$x += $this->w[$i];
				}
				$y += 6;
			}
			$y += 6;
		}

		// Footer: signature zone for purchase validation (last page)
		$pdf->SetFont('', '', 8);
		$pdf->Rect(110, 268, 90, 22);
		$pdf->SetXY(112, 269);
		$pdf->MultiCell(86, 4, $t("StockAlertSignature"), 0, 'L');

		$pdf->Output('stock_alertes_'.dol_print_date(dol_now(), '%Y%m%d').'.pdf', 'I');
	}
}
