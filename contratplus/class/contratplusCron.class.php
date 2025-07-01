<?php
/* Copyright (C) 2017-2019  Eric GROULT             <eric@code42.fr>
 * Copyright (C) 2017-2019  Fabien             <fabien@code42.fr>
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

dol_include_once('/contratplus/core/function.php');
dol_include_once('/contratplus/core/automatefunction.php');
dol_include_once('/contratplus/class/renew.class.php');

require_once DOL_DOCUMENT_ROOT."/cron/class/cronjob.class.php";

/**
 * Class to manage Cron
 */
class contratplusCron
{
    /**
    * Class to exe
    * @return no
    */
    public function exec()
    {
        global $conf, $db, $langs, $user;

        if ($conf->global->CONTRATPLUS_CRON_LOCK == 0)
        {
            dolibarr_set_const($db, 'CONTRATPLUS_CRON_LOCK', 1, '', $conf->entity);
            $cron = new CronJob($db);

            $id = getCronIdByLabel('ContratPlusCron');
            $cron->fetch($id);

            $langs->load('contratplus@contratplus');

            // Si la date est la même (interval +5 min)
            dol_syslog("Code 42: Date of renew: " . date('d/m/Y H:i', $conf->global->CONTRATPLUS_CRON_DATE), LOG_INFO);
            dol_syslog("Code 42: Date: " . date('d/m/Y H:i', dol_now()), LOG_INFO);
            if ($conf->global->CONTRATPLUS_CRON_DATE >= dol_now() - 1*60 && $conf->global->CONTRATPLUS_CRON_DATE <= dol_now() + 5*60)
            {
                dol_syslog("Code 42: Time for cron", LOG_INFO);

                $ids = getContractIds($db);
                $selected = array();
                $renew = new Renew($db, $conf, $user);

                foreach($ids as $id)
                {
                    if (isAutomatedId($id))
                        $selected[] = $id;
                }

                $renew->renewAllCron(getOption(), $selected);
                setEventMessage($langs->trans('AutoRenewPassed'), 'mesgs');
            }
            else
                dol_syslog("Code 42: Not time for cron", LOG_INFO);

            dolibarr_set_const($db, 'CONTRATPLUS_CRON_LOCK', 0, '', $conf->entity);
        }
        else
            dol_syslog("Code 42: One cron is already running", LOG_INFO);
    }
}
