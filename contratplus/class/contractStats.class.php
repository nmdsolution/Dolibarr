<?php
/*
 * Copyright (C) 2019-2020 Fabien FERNANDES ALVES <fabien.fernandes-alves@epitech.eu>
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
require_once DOL_DOCUMENT_ROOT."/contrat/class/contrat.class.php";
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
include_once DOL_DOCUMENT_ROOT.'/core/class/stats.class.php';
include_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

/**
 * Class ContractStats
 *
 * Contract statistics
 */
class ContractStats extends Stats
{
    /** @var DoliDB Database handler */
    protected $db;
    /** @var Contract actual contract */
    protected $contract;
    /** @var int User ID */
    public $userid;
    /** @var int Company ID */
    public $socid;
    /** @var int Year */
    public $year;
    /** @var int Month of the year */
    public $yearmonth;
    /** @var int Status ID */
    public $status;
    /** @var string Error message */
    public $error;
    /** @var array Color Range */
    public $colorrng;

    /**
     * Instanciate a new lead stat
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->contract = new Contrat($db);
        $this->colorrng = array(
            'green'     => array(28, 172, 72),
            'red'       => array(172, 28, 32),
            'blue'      => array(28, 104, 172),
            'grey'      => array(220, 220, 220),
            'black'     => array(0, 0, 0),
            'white'     => array(255, 255, 255),
            'yellow'    => array(172, 144, 28),
            'orange'    => array(172, 120, 28),
            'purple'    => array(120, 28, 172),
            'lightblue' => array(28, 128, 172),
            'greenblue' => array(28, 172, 168)
        );
    }

    /**
     * Returns all contracts grouped by statut
     *
     *
     * @param       string          $type           Type of contract (supplier, customer, all)
     * @param       string          $year           Year of filter
     * @param       int             $commercial     Commercial of filter
     * @param       int             $thirdparty     Thirdparty of filter
     * @param       int             $withurl        0 to disable link on label
     * @return      array|int
     */
    public function getAllContractByStatus($type = 'all', $year = '', $commercial = 0, $thirdparty = 0, $withurl = 1)
    {
        global $conf;

        // Color for pie status
        $color = array(
            '0' => $this->colorrng['yellow'], // draft
            '1' => $this->colorrng['green'], // validated
            '2' => $this->colorrng['grey'], // closed
        );

        $sql = "SELECT";
        $sql .= " count(DISTINCT c.rowid), c.statut";
        $sql .= " FROM " . MAIN_DB_PREFIX . "contrat as c";
        if ($commercial > 0)
            $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_commerciaux as sc ON sc.fk_soc = c.fk_soc";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat_extrafields as ce ON c.rowid = ce.fk_object";
        $sql .= " WHERE c.entity = ".$conf->entity;
        if ($type == 'customer')
            $sql .= " AND (ce.supplier_contract IS NULL OR ce.supplier_contract = 0)";
        else if ($type == 'supplier')
            $sql .= " AND ce.supplier_contract = 1";
        if ($year && !empty($year))
            $sql .= " AND date_format(c.date_contrat, '%Y') = ".$year;
        if ($commercial > 0)
            $sql .= " AND sc.fk_user = ".$commercial;
        if ($thirdparty > 0)
            $sql .= " AND c.fk_soc = ".$thirdparty;

        $sql .= " GROUP BY c.statut";

        $result = array ();
        dol_syslog(get_class($this) . '::' . __METHOD__ . "", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            $openurl = '';
            $closeurl = '';
            $num = $this->db->num_rows($resql);
            $i = 0;
            while ( $i < $num ) {
                $row = $this->db->fetch_row($resql);

                // Setup redirection
                if ($withurl) {
                    $param = '';
                    // Filter type
                    if ($type == 'supplier')
                        $param.='&search_cli_four=1';
                    else if ($type == 'customer')
                        $param.='&search_cli_four=0';
                    // Filter status
                    if ($row[1] == 0) // Brouillon
                        $param.='&search_status=1';
                    else if ($row[1] == 1) // Validated
                        $param.='&search_status=2';
                    else if ($row[1] == 2) // Closed
                        $param.='&search_status=3';
                    // Filter thirdparty
                    if ($thirdparty > 0) {
                        $soc = new Societe($this->db);
                        $soc->fetch($thirdparty);
                        $param.='&search_name='.$soc->name;
                    }
                    // Filter year
                    if ($year && !empty($year))
                        $param.='&year='.$year;
                    // Filter sale
                    if ($commercial > 0)
                        $param.='&search_sale='.$commercial;

                    $url = dol_buildpath('contratplus/list.php?'.$param, 1);
                    $openurl = '<a href="'.$url.'">';
                    $closeurl = '</a>';
                }

                $element = $this->contract->LibStatut($row[1], 0).' ('.$row[0].')';
                $result[$i] = array (
					((float)DOL_VERSION >= 12) ? html_entity_decode($element) : ($openurl.$element.$closeurl),
                    $row[0],
                    $color[$row[1]]
                );
                $i++;
            }
            $this->db->free($resql);
        } else {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . '::' . __METHOD__ . ' ' . $this->error, LOG_ERR);
            return - 1;
        }

        return $result;
    }

    /**
     * Return count, and sum of products
     *
     * @param       string          $type           Type of contract (supplier, customer, all)
     * @param       int             $commercial     Commercial of filter
     * @param       int             $thirdparty     Thirdparty of filter
     * @return      array of values
     */
    public function getAllByYear($type = 'all', $commercial = 0, $thirdparty = 0)
    {
        global $conf, $user;

        $sql = "SELECT date_format(c.date_contrat,'%Y') as year, COUNT(DISTINCT c.rowid) as nb";
        $sql .= " FROM " . MAIN_DB_PREFIX . "contrat as c";
        if ($commercial > 0)
            $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_commerciaux as sc ON sc.fk_soc = c.fk_soc";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat_extrafields as ce ON c.rowid = ce.fk_object";
        $sql .= " WHERE c.entity = ".$conf->entity;
        if ($commercial > 0)
            $sql .= " AND sc.fk_user = ".$commercial;
        if ($type == 'customer')
            $sql .= " AND (ce.supplier_contract IS NULL OR ce.supplier_contract = 0)";
        else if ($type == 'supplier')
            $sql .= " AND ce.supplier_contract = 1";
        if ($thirdparty > 0)
            $sql .= " AND c.fk_soc = ".$thirdparty;
        $sql .= " GROUP BY year";
        $sql .= $this->db->order('year', 'DESC');

        return $this->_getAllByYear($sql);
    }

    /**
     * Return average of entity by month for several years.
     *
     * @param       string          $endyear        End year to filter
     * @param       string          $startyear      Start year to filter
     * @param       string          $type           Type of contract (supplier, customer, all)
     * @param       int             $commercial     Commercial of filter
     * @param       int             $thirdparty     Thirdparty of filter
     * @return      array|int
     */
    public function getNbByMonthWithPrevYear($endyear, $startyear, $type = 'all', $commercial = 0, $thirdparty = 0)
    {
        if ($startyear > $endyear) return -1;

        $datay=array();

        $year=$startyear;
        while($year <= $endyear)
        {
            $datay[$year] = $this->getNbByMonth($type, $year, $commercial, $thirdparty);
            $year++;
        }

        $data = array();

        for ($i = 0 ; $i < 12 ; $i++)
        {
            $data[$i][]=$datay[$endyear][$i][0];
            $year=$startyear;
            while($year <= $endyear)
            {
                $data[$i][]=$datay[$year][$i][1];
                $year++;
            }
        }

        return $data;
    }

    /**
     * Get nb contract by month for a year
     *
     * @param       string          $type           Type of contract (supplier, customer, all)
     * @param       string          $year           Year of filter
     * @param       int             $commercial     Commercial of filter
     * @param       int             $thirdparty     Thirdparty of filter
     * @return      mixed
     */
    public function getNbByMonth($type = 'all', $year = '', $commercial = 0, $thirdparty = 0)
    {
        global $conf;

        $this->yearmonth = $year;

        $sql = "SELECT date_format(c.date_contrat,'%m') as dm, COUNT(DISTINCT c.rowid) as nb";
        $sql .= " FROM " . MAIN_DB_PREFIX . "contrat as c";
        if ($commercial > 0)
            $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_commerciaux as sc ON sc.fk_soc=c.fk_soc";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat_extrafields as ce ON c.rowid = ce.fk_object";
        $sql .= " WHERE c.entity = ".$conf->entity;
        if ($year && !empty($year))
            $sql .= " AND date_format(c.date_contrat, '%Y') = ".$year;
        if ($commercial > 0)
            $sql .= " AND sc.fk_user = ".$commercial;
        if ($type == 'customer')
            $sql .= " AND (ce.supplier_contract IS NULL OR ce.supplier_contract = 0)";
        else if ($type == 'supplier')
            $sql .= " AND ce.supplier_contract = 1";
        if ($thirdparty > 0)
            $sql .= " AND c.fk_soc = ".$thirdparty;
        $sql .= " GROUP BY dm";
        $sql .= $this->db->order('dm', 'DESC');

        $this->yearmonth=0;

        $res = $this->_getNbByMonth($year, $sql);
        return $res;
    }

    /**
     * Return total ca by month for several years.
     *
     * @param       string          $endyear        End year to filter
     * @param       string          $startyear      Start year to filter
     * @param       string          $type           Type of contract (supplier, customer, all)
     * @param       int             $commercial     Commercial of filter
     * @param       int             $thirdparty     Thirdparty of filter
     *
     * @return      array|int
     */
    public function getCAByMonthWithPrevYear($endyear, $startyear, $type = 'all', $commercial = 0, $thirdparty = 0)
    {
        if ($startyear > $endyear) return -1;

        $datay=array();

        $year=$startyear;
        $total = 0;
        while($year <= $endyear)
        {
            $tmp = $this->getCAByMonth($type, $year, $commercial, $thirdparty);

            // Cumulate c.a for each month for prev year and  actual year
            /*for ($i = 0 ; $i < 12 ; $i++)
            {
                $total += $tmp[$i][1];
                $tmp[$i][1] = $total;
            }*/

            $datay[$year] = $tmp;
            $year++;
        }

        $data = array();

        for ($i = 0 ; $i < 12 ; $i++)
        {
            $data[$i][]=$datay[$endyear][$i][0];
            $year=$startyear;
            while($year <= $endyear)
            {
                $data[$i][]=$datay[$year][$i][1];
                $year++;
            }
        }
        return $data;
    }

    /**
     * Get total ca by month for a year
     *
     * @param       string          $type           Type of contract (supplier, customer, all)
     * @param       string          $year           Year of filter
     * @param       int             $commercial     Commercial of filter
     * @param       int             $thirdparty     Thirdparty of filter
     * @return      mixed
     */
    public function getCAByMonth($type = 'all', $year = '', $commercial = 0, $thirdparty = 0)
    {
        global $conf;

        $this->yearmonth = $year;

        $sql = "SELECT date_format(f.datef,'%m') as dm, SUM(f.total) as nb ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "facture as f";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "element_element as c ON c.fk_target=f.rowid";
        if ($commercial > 0)
            $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_commerciaux as sc ON sc.fk_soc=f.fk_soc";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "contrat_extrafields as ce ON c.fk_source=ce.fk_object";
        $sql .= " WHERE f.entity = ".$conf->entity;
        $sql .= " AND c.sourcetype = 'contrat' AND c.targettype= 'facture'";
        if ($year && !empty($year))
            $sql .= " AND date_format(f.datef, '%Y') = ".$year;
        if ($commercial > 0)
            $sql .= " AND sc.fk_user = ".$commercial;
        if ($type == 'customer')
            $sql .= " AND (ce.supplier_contract IS NULL OR ce.supplier_contract = 0)";
        else if ($type == 'supplier')
            $sql .= " AND ce.supplier_contract = 1";
        if ($thirdparty > 0)
            $sql .= " AND f.fk_soc = ".$thirdparty;
        $sql .= " GROUP BY dm";
        $sql .= $this->db->order('dm', 'DESC');

        $this->yearmonth=0;

        $res = $this->_getNbByMonth($year, $sql);
        return $res;
    }
}
