<?php
/* Copyright (C) 2017-2018	Eric GROULT			    <eric@code42.fr>
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
 * Return a serv_duree from an index
 *
 * @param   int     $index      Index of a serv_duree
 * @return  string              null on error, value of the index on success
 */
function getServDureeFromIndex($index)
{
    global $db;

    $extrafield = new ExtraFields($db);

    $extrafield->fetch_name_optionals_label('contratdet');

    foreach ($extrafield->attribute_label as $key => $val)
    {
        if ($key == 'serv_duree')
            return $extrafield->attribute_param[$key]['options'][$index];
    }
    return null;
}

/**
 *      Update all serv_date_end field of lines given in parameter
 *
 *      @param      array       $lines      Lines to update
 *      @return     void
 */
function modifyDateEnd($lines)
{
    global $db;

    foreach ($lines as $key => $line)
    {
        $date = date_create($line['serv_date_start']);
        $duration = getServDureeFromIndex($line['serv_duree']);
        if (is_numeric($duration) && $line['serv_date_start'] != '0000-00-00') {
            //$old = clone $date;
            //echo $line['rowid'] . ': ' . $old->format('Y-m-d') . ' for ' . $duration . ' month => ' . $date->format('Y-m-d') . '<br/>';
            $date->modify('+' . $duration . ' month');
            $sql = 'UPDATE llx_contratdet_extrafields SET serv_date_end = "' . $date->format('Y-m-d H:i:s') . '" WHERE llx_contratdet_extrafields.rowid = ' . $line['rowid'];
            $db->query($sql);

            /*$sql = 'UPDATE llx_contratdet SET date_fin_validite = "' . $date->format('Y-m-d H:i:s') . '" WHERE llx_contratdet.rowid = ' . $line['fk_object'];
            $db->query($sql);*/
        }
    }
}

/**
 *      Get all lines without serv_date_end attribute
 *
 *      @return     array
 */
function getIdWithoutDateEnd()
{
    global $db;
    $sql = 'SELECT * FROM llx_contratdet_extrafields as cd WHERE cd.serv_date_end IS NULL';

    $resql = $db->query($sql);

    foreach($resql as $key => $row)
    {
        $lines[] = $row;
    }
    return $lines;
}

/**
 *      Set NULL to all serv_date_end field of each lines on Db and redirect
 *
 *      @return     void
 */
function cleanDateEnd()
{
    global $db;
    $sql = 'SELECT * FROM llx_contratdet_extrafields as cd';

    $resql = $db->query($sql);

    foreach($resql as $key => $row)
    {
        $sql = 'UPDATE llx_contratdet_extrafields SET serv_date_end = NULL WHERE llx_contratdet_extrafields.rowid = ' . $row['rowid'];
        $db->query($sql);
    }
    exit(header("Location: ".$_SERVER['PHP_SELF']));
}

/**
 *      Populate the serv_date_end extrafields of contratdet on Db and redirect
 *
 *      @return     void
 */
function populateDb()
{
    $lines = getIdWithoutDateEnd();
    modifyDateEnd($lines);
    //exit(header("Location: ".$_SERVER['PHP_SELF']);
}
