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
include_once DOL_DOCUMENT_ROOT . '/core/class/stats.class.php';
include_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';

/**
 * Class ServiceStats
 *
 * Service statistics
 */
class ServiceStats extends Stats
{
    /** @var DoliDB Database handler */
    protected $db;
    /** @var Service actual service */
    protected $service;
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
        $this->service = new ContratLigne($db);
        $this->colorrng = array(
            'green' => array(28, 172, 72),
            'red'   => array(172, 28, 32),
            'blue'  => array(28, 104, 172),
            'grey'  => array(220, 220, 220),
            'black' => array(0, 0, 0),
            'white' => array(255, 255, 255),
            'yellow'=> array(172, 144, 28),
            'orange' => array(172, 120, 28),
            'purple' => array(120, 28, 172),
            'lightblue' => array(28, 128, 172),
        );
    }

    /**
     * Returns all services grouped by statut
     *
     * @param       string          $type           Type of contract (supplier, customer, all)
     * @param       string          $year           Year of filter
     * @param       int             $commercial     Commercial of filter
     * @param       int             $thirdparty     Thirdparty of filter
     * @return array|int
     * @throws Exception
     */
    public function getAllServiceByStatus($type = 'all', $year = '', $commercial = 0, $thirdparty = 0)
    {
        global $conf;

        $sql = "SELECT";
        $sql .= " count(DISTINCT cd.rowid), cd.statut";
        $sql .= " FROM " . MAIN_DB_PREFIX . "contratdet as cd";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contratdet_extrafields as cde ON cd.rowid = cde.fk_object";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat as c ON cd.fk_contrat = c.rowid";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat_extrafields as ce ON ce.fk_object = c.rowid";
        if ($commercial > 0)
            $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_commerciaux as sc ON sc.fk_soc = c.fk_soc";
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
        $sql .= " GROUP BY cd.statut";

        $result = array ();

        dol_syslog(get_class($this) . '::' . __METHOD__ . "", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            while ( $i < $num ) {
                $row = $this->db->fetch_row($resql);
                $result[$i] = array (
                    $this->service->LibStatut($row[1], 0). ' (' . $row[0] . ')',
                    $row[0]
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
     * Get the number of active services without serv_date_end info
     *
     * @param       string          $type           Type of contract (supplier, customer, all)
     * @param       string          $year           Year of filter
     * @param       int             $commercial     Commercial of filter
     * @param       int             $thirdparty     Thirdparty of filter
     * @return      int
     */
    private function getActiveServiceWithoutInfo($type, $year, $commercial, $thirdparty = 0)
    {
        global $conf;

        $sql = "SELECT";
        $sql .= " COUNT(cd.rowid) as nb";
        $sql .= " FROM " . MAIN_DB_PREFIX . "contratdet as cd";
        $sql .= " INNER JOIN ".MAIN_DB_PREFIX."contratdet_extrafields as cde ON cd.rowid = cde.fk_object";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat as c ON cd.fk_contrat = c.rowid";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat_extrafields as ce ON ce.fk_object = c.rowid";
        if ($commercial > 0)
            $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_commerciaux as sc ON sc.fk_soc = c.fk_soc";
        $sql .= " WHERE c.entity = ".$conf->entity;
        if ($type == 'customer')
            $sql .= " AND (ce.supplier_contract IS NULL OR ce.supplier_contract = 0)";
        else if ($type == 'supplier')
            $sql .= " AND ce.supplier_contract = 1";
        if ($year && !empty($year))
            $sql .= " AND date_format(cde.serv_date_start, '%Y') = ".$year;
        if ($commercial > 0)
            $sql .= " AND sc.fk_user = ".$commercial;
        if ($thirdparty > 0)
            $sql .= " AND c.fk_soc = ".$thirdparty;
        $sql .= " AND cde.serv_date_start IS NULL AND cd.statut = 4";

        $resql = $this->db->query($sql);
        if ($resql) {
            $row = $resql->fetch_object();
            return $row->nb ? $row->nb : 0;
        }
        return 0;
    }

    /**
     * Returns all services grouped by status with serv_date_start info
     *
     * @param       string          $type           Type of contract (supplier, customer, all)
     * @param       string          $year           Year of filter
     * @param       int             $commercial     Commercial of filter
     * @param       int             $thirdparty     Thirdparty of filter
     * @param       int             $withurl        0 to disable link on label
     * @return      array|int
     * @throws      Exception
     */
    public function getAllServiceByStatusPlus($type = 'all', $year = '', $commercial = 0, $thirdparty = 0, $withurl = 1)
    {
        global $conf, $langs;

        // Color for pie status
        $color = array(
            '5' => $this->colorrng['grey'], // closed
            '4' => $this->colorrng['green'], // open
            '0' => $this->colorrng['yellow'], // unactive
            '40' => $this->colorrng['red']); // open without info

        $sql = "SELECT";
        $sql .= " count(DISTINCT cd.rowid), cd.statut";
        $sql .= " FROM " . MAIN_DB_PREFIX . "contratdet as cd";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contratdet_extrafields as cde ON cd.rowid = cde.fk_object";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat as c ON cd.fk_contrat = c.rowid";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat_extrafields as ce ON ce.fk_object = c.rowid";
        if ($commercial > 0)
            $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_commerciaux as sc ON sc.fk_soc = c.fk_soc";
        $sql .= " WHERE c.entity = ".$conf->entity;
        if ($type == 'customer')
            $sql .= " AND (ce.supplier_contract IS NULL OR ce.supplier_contract = 0)";
        else if ($type == 'supplier')
            $sql .= " AND ce.supplier_contract = 1";
        if ($year && !empty($year))
            $sql .= " AND date_format(cde.serv_date_start, '%Y') = ".$year;
        if ($commercial > 0)
            $sql .= " AND sc.fk_user = ".$commercial;
        if ($thirdparty > 0)
            $sql .= " AND c.fk_soc = ".$thirdparty;
        $sql .= " GROUP BY cd.statut";

        $result = array ();

        dol_syslog(get_class($this) . '::' . __METHOD__ . "", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            $activewithoutinfo = $this->getActiveServiceWithoutInfo($type, $year, $commercial, $thirdparty);
            // Add + 1 because we have to add active service without info
            while ( $i < $num + 1 ) {
                $row = $this->db->fetch_row($resql);
                $openurl = '';
                $closeurl = '';

                // Setup redirection
                if ($withurl) {
                    $param = '';
                    // Filter type
                    if ($type == 'supplier')
                        $param.='&search_cli_four=1';
                    else if ($type == 'customer')
                        $param.='&search_cli_four=0';
                    // Filter status
                    $param.='&search_status='.$row[1];
                    // Filter thirdparty
                    if ($thirdparty > 0) {
                        $soc = new Societe($this->db);
                        $soc->fetch($thirdparty);
                        $param.='&search_name='.$soc->name;
                    }
                    // Filter year
                    if ($year && !empty($year))
                        $param.='&search_year='.$year;
                    // Filter sale
                    if ($commercial > 0)
                        $param.='&search_sale='.$commercial;

                    $url = dol_buildpath('contratplus/services.php?'.$param, 1);
                    $openurl = '<a href="'.$url.'">';
                    $closeurl = '</a>';
                }

                // 4 = Active service
                // We add the line active service without info
                if ($row[1] == '4') {
                    $openurl = str_replace("&search_status=4", "&search_status=4&search_no_date=1", $openurl);
					$element = $langs->trans('WithoutActivationDate').' ('.$activewithoutinfo.')';
                    $result[$i++] = array (
						((float)DOL_VERSION >= 12) ? html_entity_decode($element) : ($openurl.$element.$closeurl),
                        $activewithoutinfo,
                        $color[40]
                    );
                    $openurl = str_replace("&search_status=4&search_no_date=1", "&search_status=4&filter_opouvertureprevue=>", $openurl);
                    $element = $langs->trans('WithActivationDate').' ('.strval((int) $row[0] - (int) $activewithoutinfo).')';
                    $result[$i] = array (
						((float)DOL_VERSION >= 12) ? html_entity_decode($element) : ($openurl.$element.$closeurl),
                        strval((int) $row[0] - (int) $activewithoutinfo),
                        $color[$row[1]]
                    );
                } else {
                	$element = $this->service->LibStatut($row[1], 0).' ('.$row[0].')';
                    $result[$i] = array (
						((float)DOL_VERSION >= 12) ? html_entity_decode($element) : ($openurl.$element.$closeurl),
                        $row[0],
                        $color[$row[1]]
                    );
                }
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

        $sql = "SELECT date_format(cde.serv_date_start,'%Y') as year, COUNT(DISTINCT cd.rowid) as nb, SUM(cd.total_ht) as total";
        $sql .= " FROM " . MAIN_DB_PREFIX . "contratdet as cd";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contratdet_extrafields as cde ON cd.rowid = cde.fk_object";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat as c ON cd.fk_contrat = c.rowid";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat_extrafields as ce ON ce.fk_object = c.rowid";
        if ($commercial > 0)
            $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_commerciaux as sc ON sc.fk_soc = c.fk_soc";
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
     * Get nb service by month for a year
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

        $sql = "SELECT date_format(cde.serv_date_start,'%m') as dm, COUNT(DISTINCT cd.rowid) as nb";
        $sql .= " FROM " . MAIN_DB_PREFIX . "contratdet as cd";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contratdet_extrafields as cde ON cd.rowid = cde.fk_object";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat as c ON cd.fk_contrat = c.rowid";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat_extrafields as ce ON ce.fk_object = c.rowid";
        if ($commercial > 0)
            $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_commerciaux as sc ON sc.fk_soc = c.fk_soc";
        $sql .= " WHERE c.entity = ".$conf->entity;
        if ($year && !empty($year))
            $sql .= " AND date_format(cde.serv_date_start, '%Y') = ".$year;
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
     * @return      array|int
     */
    public function getCAByMonthWithPrevYear($endyear, $startyear, $type = 'all', $commercial = 0, $thirdparty = 0)
    {
        if ($startyear > $endyear) return -1;

        $datay=array();

        $year=$startyear;
        while($year <= $endyear)
        {
            $datay[$year] = $this->getCAByMonth($type, $year, $commercial, $thirdparty);
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

        $sql = "SELECT date_format(cde.serv_date_start,'%m') as dm, SUM(cd.total_ht) as nb";
        $sql .= " FROM " . MAIN_DB_PREFIX . "contratdet as cd";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contratdet_extrafields as cde ON cd.rowid = cde.fk_object";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat as c ON cd.fk_contrat = c.rowid";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat_extrafields as ce ON ce.fk_object = c.rowid";
        if ($commercial > 0)
            $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_commerciaux as sc ON sc.fk_soc = c.fk_soc";
        $sql .= " WHERE c.entity = ".$conf->entity;
        if ($year && !empty($year))
            $sql .= " AND date_format(cde.serv_date_start, '%Y') = ".$year;
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
}
