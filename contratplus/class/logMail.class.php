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

require_once DOL_DOCUMENT_ROOT . '/core/lib/functions.lib.php';

class LogMail
{

    protected $fileHandler = null;

    protected $dir = null;

    const DEBUG     = 'DEBUG';
    const INFO      = 'INFO';
    const WARNING   = 'WARNING';
    const ERROR     = 'ERROR';

    // @codingStandardsIgnoreEnd
    /**
     * Constructor. Define names, constants, directories, boxes, permissions
     *
     * @param file  $logfile Default mail.log
     */
    public function __construct($logfile = 'mail.log')
    {
        $document = DOL_DATA_ROOT . '/contratplus/';
        if (!$this->dir)
        {
            if (is_dir($document))
            {
                $this->dir = $document;
            }
            else
            {
                mkdir($document);
                if (is_dir($document))
                    $this->dir = $document;
                else {
                    $this->dir = DOL_DOCUMENT_ROOT . '/contratplus/docs/';
                    if (!is_dir($this->dir))
                        $this->dir = DOL_DOCUMENT_ROOT . '/custom/contratplus/docs/';
                }
            }
        }

        if (!$this->fileHandler)
            $this->openLogFile($logfile);
    }

    /**
     *  Get the directory of mail log
     *
     *  @return     string                      Directory path
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     *  Open log file with name $logfile
     *
     *  @param      string      $logfile        Logfile name (not path, path will be contratplus/docs)
     *  @return     int                         < 0 on error, > 0 on success
     */
    public function openLogFile($logfile)
    {
        $this->closeLogFile();

        $path = $this->dir . $logfile;
        if(!$this->fileHandler = fopen($path, 'a+')){
            return -1;
        }
        return 1;
    }

    /**
     *  Close log file
     *
     *  @return     void
     */
    public function closeLogFile()
    {
        if ($this->fileHandler)
        {
            fclose($this->fileHandler);
            $this->fileHandler = null;
        }
    }

    /**
     *  Write log in file handle with $this->handler
     *
     *  @param      string      $msg        Message to log
     *  @param      string      $level      Level of log (DEBUG | INFO | WARNING | ERROR)
     *  @return     int                     < 0 on error (-1 file not open, -2 message not a string, -3 level error), > 0 on success
     */
    public function writeLog($msg, $level = LogMail::DEBUG)
    {
        if (!$this->fileHandler)
            return -1;

        if (!is_string($msg))
            return -2;

        if ($level != LogMail::DEBUG &&
            $level != LogMail::INFO &&
            $level != LogMail::WARNING &&
            $level != LogMail::ERROR)
        {
            return -3;
        }

        $this->writeToLogFile(date('d-m-Y H:i:s', dol_now()) . "\t" . $level . "\t" . $msg);
        return 1;
    }

    /**
     *  Write delimiter with message in log file
     *
     *  @param      string      $msg        Message to log between delimiter
     *  @return     int                     < 0 on error, > 0 on success
     */
    public function writeDelimiter($msg = '')
    {
        if (!$this->fileHandler)
            return -1;

        $this->writeToLogFile("*****" . $msg . "*****");

        return 1;
    }

    /**
     *  Write msg in file after verification in writeLog
     *
     *  @param      string      $msg        Message to log
     *  @return     void
     */
    private function writeToLogFile($msg)
    {
        flock($this->fileHandler, LOCK_EX);
        fwrite($this->fileHandler, $msg . PHP_EOL);
        flock($this->fileHandler, LOCK_UN);
    }
}
