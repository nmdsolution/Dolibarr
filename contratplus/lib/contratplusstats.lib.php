<?php

/**
 * Display a listing of all draft contract
 *
 * @param   int         $limit      Limit for the listing
 * @param   string      $type       Type of contract (supplier, customer, all)
 * @return  string                  html formated string
 */
function draftContractListing($limit = 5, $type = 'all')
{
    global $db, $conf, $langs;

    $view = '<table class="border" width="100%">';
    $view .= '<tr class="liste_titre"><td class="liste_titre" colspan="3"><b>'.$langs->trans($type."DraftContractListing", $limit).'</b></td></tr>';

    $sql = "SELECT c.rowid, c.fk_soc, c.datec  FROM ".MAIN_DB_PREFIX."contrat as c";
    $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat_extrafields as ce ON c.rowid = ce.fk_object";
    $sql .= " WHERE statut = 0 AND entity = ".$conf->entity;
    if ($type == 'customer')
        $sql .= " AND (ce.supplier_contract IS NULL OR ce.supplier_contract = 0)";
    else if ($type == 'supplier')
        $sql .= " AND ce.supplier_contract = 1";
    $sql .= " ORDER BY datec DESC LIMIT ".$limit;

    dol_syslog("ContratPlus => lastCreatedContractListing : ".$sql, LOG_DEBUG);
    $resql = $db->query($sql);
    if ($resql) {
        foreach ($resql as $row => $value) {
            $contract = new CustomContrat($db);
            $thirdparty = new Societe($db);
            $contract->fetch($value['rowid']);
            $thirdparty->fetch($value['fk_soc']);
            $view .= '<tr class="impair">';
            $view .= '<td>'.$contract->getNomUrl(1).'</td>';
            $view .= '<td>'.$thirdparty->getNomUrl(1).'</td>';
            $view .= '<td>'.$value['datec'].'</td>';
            $view .= '</tr>';
        }
    } else {
        $view .= '<tr class="impair">';
        $view .= '<td colspan="3" align="center" class="opacitymedium">'.$langs->trans('None').'</td>';
        $view .= '</tr>';
    }

    $view .= '</table>';

    return $view;
}

/**
 * Display a listing of last contract created
 *
 * @param   int         $limit      Limit to display
 * @param   string      $type       Type of contract (supplier, customer, all)
 * @return  string                  html formated string
 */
function lastCreatedContractListing($limit = 5, $type = 'all')
{
    global $db, $conf, $langs;

    $view = '<table class="border" width="100%">';
    $view .= '<tr class="liste_titre"><td class="liste_titre" colspan="3"><b>'.$langs->trans($type."LastCreatedContractListing", $limit).'</b></td></tr>';
    $sql = "SELECT c.rowid, c.fk_soc, c.datec  FROM ".MAIN_DB_PREFIX."contrat as c";
    $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat_extrafields as ce ON c.rowid = ce.fk_object";
    $sql .= " WHERE statut = 1 AND entity = ".$conf->entity;
    if ($type == 'customer')
        $sql .= " AND (ce.supplier_contract IS NULL OR ce.supplier_contract = 0)";
    else if ($type == 'supplier')
        $sql .= " AND ce.supplier_contract = 1";
    $sql .= " ORDER BY datec DESC LIMIT ".$limit;

    dol_syslog("ContratPlus => lastCreatedContractListing : ".$sql, LOG_DEBUG);
    $resql = $db->query($sql);
    if ($resql) {
        foreach ($resql as $row => $value) {
            $contract = new CustomContrat($db);
            $thirdparty = new Societe($db);
            $contract->fetch($value['rowid']);
            $thirdparty->fetch($value['fk_soc']);
            $view .= '<tr class="impair">';
            $view .= '<td>'.$contract->getNomUrl(1).'</td>';
            $view .= '<td>'.$thirdparty->getNomUrl(1).'</td>';
            $view .= '<td>'.$value['datec'].'</td>';
            $view .= '</tr>';
        }
    } else {
        $view .= '<tr class="impair">';
        $view .= '<td colspan="3" align="center" class="opacitymedium">'.$langs->trans('None').'</td>';
        $view .= '</tr>';
    }

    $view .= '</table>';

    return $view;
}

/**
 * Generate and return html formated depth graph
 *
 * @param   string      $title              Title of the pie
 * @param   array       $data               Data for the pie
 * @param   string      $filenamenb         Filename to save the pie
 * @param   string      $fileurlnb          Fileurl to access the saved pie
 * @param   string      $startyear          Start Year for graph
 * @param   string      $endyear            End Year for graph
 * @return  mixed
 */
function loadDepth($title, $data, $filenamenb, $fileurlnb, $startyear, $endyear)
{
    $WIDTH=DolGraph::getDefaultGraphSizeForStats('width');
    $HEIGHT=DolGraph::getDefaultGraphSizeForStats('height');

    $px1 = new DolGraph();
    $mesg = $px1->isGraphKo();
    if (! $mesg)
    {
        $px1->SetData($data);
        $i=$startyear;$legend=array();
        while ($i <= $endyear)
        {
            $legend[]=$i;
            $i++;
        }
        $px1->SetDataColor(array (
            array(28, 128, 172),
            array(28, 172, 168)
        ));
        $px1->SetLegend($legend);
        $px1->SetMaxValue($px1->GetCeilMaxValue());
        $px1->SetWidth($WIDTH);
        $px1->SetHeight($HEIGHT);
        $px1->SetYLabel($title);
        $px1->SetShading(3);
        $px1->SetHorizTickIncrement(1);
        $px1->mode='depth';
        $px1->SetTitle('<b>'.$title.'</b>');

        $px1->draw($filenamenb, $fileurlnb);
    } else {
        setEventMessages(null, $mesg, 'errors');
    }

    return $px1->show();
}

/**
 * Generate and return html formated pie
 *
 * @param   string      $title              Title of the pie
 * @param   array       $data               Data for the pie
 * @param   string      $filenamenb         Filename to save the pie
 * @param   string      $fileurlnb          Fileurl to access the saved pie
 * @param   string      $staterror          Error of stat
 * @return  mixed
 */
function loadPie($title, $data, $filenamenb, $fileurlnb, $staterror)
{
    global $langs;

    if (!is_array($data) && $data<0) {
        setEventMessages($staterror, null, 'errors');
    }
    if (empty($data))
    {
        $showpointvalue=0;
        $nocolor=1;
        $data=array(array(0=>$langs->trans("None"),1=>1));
    }

    $px = new DolGraph();
    $mesg = $px->isGraphKo();
    $piedatatotal = 0;
    if (empty($mesg)) {
        $i=0;$tot=count($data);$legend=array();
        while ($i <= $tot)
        {
            $data[$i][0]=$data[$i][0];	// Required to avoid error "Could not draw pie with labels contained inside canvas"
            $legend[]=$data[$i][0];
            if (is_numeric($data[$i][1]))
                $piedatatotal += $data[$i][1];
            $i++;
        }

        $px->SetData($data);
        if ($nocolor)
            $px->SetDataColor(array (
                array (
                    220,
                    220,
                    220
                )
            ));
        else {
            $i = 0;
            $color = array();
            while ($i < $tot)
            {
                if ($data[$i][2])
                    $color[] = $data[$i][2];

                $i++;
            }
            if ($color)
                $px->SetDataColor($color);
        }
        unset($data);

        $px->SetLegend($legend);
        $px->setShowLegend(0);
        $px->setShowPointValue($showpointvalue);
        $px->setShowPercent(1);
        $px->SetMaxValue($px->GetCeilMaxValue());
        $px->SetWidth(300);
        $px->SetHeight(300);
        $px->SetShading(3);
        $px->SetHorizTickIncrement(1);
        $px->SetCssPrefix("cssboxes");
        $px->SetType(array (
            'pie'
        ));
        $px->SetTitle('<b>'.$title.' <span class="badge">'.$piedatatotal.'</span></b>');
        $result=$px->draw($filenamenb, $fileurlnb);
        if ($result<0) {
            setEventMessages($px->error, null, 'errors');
        }
    } else {
        setEventMessages(null, $mesg, 'errors');
    }

    return $px->show();
}
