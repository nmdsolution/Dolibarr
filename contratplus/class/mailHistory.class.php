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
 *  Class used to convert a line of mail.log into an object
 */
class MailHistory
{
    public $date = null;
    public $id = 0;
    public $supplier = false;
    public $topic = null;
    public $mailto = null;
    public $mailfrom = null;

    /**
     *  __construct
     *
     *  @param      string      $logline       Line to parse
     *  @return     void
     */
    public function __construct($logline)
    {
        $this->setAttributeFromLine($logline);
    }

    /**
     *  Parse line and set attribute of object
     *
     *  @param      string      $line       Line to parse
     *  @return     void
     */
    public function setAttributeFromLine($line)
    {
        $array = explode("\t", $line);
        $date = $array[0];
        $info = $array[2];

        $this->date = $date;
        $this->topic = $this->getStringBetween($info, '[', ']');

        $array_info = explode(' ', $info);
        $this->supplier = ($array_info[0] == 'Supplier') ? true : false;
        $this->id = $array_info[2];
        // get mail between 'sent to' and 'by'
        $arr = explode('sent to', $info);
        $arr = explode('by', $arr[1]);
        $this->mailto = $arr[0];
        $this->mailfrom = end($array_info);
    }

    /**
     *  Get a string between two delimiters
     *
     *  @param      string      $str        String to parse
     *  @param      string      $from       First delimiter
     *  @param      string      $to         Second delimiter
     *  @return     string
     */
    private function getStringBetween($str, $from, $to)
    {
        $sub = substr($str, strpos($str, $from) + strlen($from), strlen($str));
        return substr($sub, 0, strpos($sub, $to));
    }
}
