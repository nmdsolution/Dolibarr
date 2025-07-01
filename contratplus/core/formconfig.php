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
 *  Get the email template
 *
 *  @param      Database        $db             The object given by dolibarr global (global $db)
 *  @param      string          $label          Label of the email template
 *  @return     object
 */
function getEmailTemplate($db, $label)
{
    global $user;

    $sql = "SELECT label, topic, content";
    $sql .= " FROM " . MAIN_DB_PREFIX . 'c_email_templates';
    $sql .= " WHERE (type_template='facture_send' OR type_template='all')";
    $sql .= " AND entity IN (".getEntity('c_email_templates', 0).")";
    $sql .= " AND (private = 0 OR fk_user = " . $user->id . ")";       // Get all public or private owned
    $sql .= " AND active = 1";
    $sql .= " AND label = '" . $label . "'";
    $sql .= $db->order("position,lang,label", "ASC");
    dol_syslog($sql, LOG_DEBUG);

    $resql = $db->query($sql);
    if ($resql)
        return $db->fetch_object($resql);
    else
        return null;
}

/**
 *  Get all email template label created and active
 *
 *  @param      Database        $db             The object given by dolibarr global (global $db)
 *  @return     array                           List of email template label
 */
function getEmailTemplates($db)
{
    global $user;
    $ret = array('' => '');

    $sql = "SELECT label";
    $sql .= " FROM " . MAIN_DB_PREFIX . 'c_email_templates';
    $sql .= " WHERE (type_template='facture_send' OR type_template='all')";
    $sql .= " AND entity IN (".getEntity('c_email_templates', 0).")";
    $sql .= " AND (private = 0 OR fk_user = " . $user->id . ")";       // Get all public or private owned
    $sql .= " AND active = 1";
    $sql .= $db->order("position,lang,label", "ASC");
    dol_syslog($sql, LOG_DEBUG);

    $resql = $db->query($sql);
    if ($resql)
    {
        foreach($resql as $key => $res)
        {
            $ret[$res['label']] = $res['label'];
        }
    }
    return $ret;
}

/**
 *  Get email template selection form
 *
 *  @param      Database        $db             The object given by dolibarr global (global $db)
 *  @param      string          $selected       Label of email template selected
 *  @return     string
 */
function formEmailTemplate($db, $selected = '')
{
    global $langs, $form;
    $ret = '<div>' . $langs->trans('SelectEmailTemplate') . ': ';

    $templates = getEmailTemplates($db);
    $ret .= $form->selectarray('email_template', $templates, $selected);
    $ret .= img_picto($langs->trans('ChangeMailModel'), 'info');
    $ret .= '<input type="hidden" name="action" value="settemplate" />';
    $ret .= ' <input class="button" type="submit" value="' . $langs->trans('Use') . '" id="modelselected">';
    $ret .= '</div>';
    return $ret;
}
