<?php
/**
 * Created by PhpStorm.
 * User: df
 * Date: 17/7/19
 * Time: 14:09
 */

namespace KResque\Jobs;

use Resque_Log;
use Psr\Log\LogLevel;

class FileLog extends Resque_Log
{


    protected $_file_out = null;

    /**
     * @var JobConfig
     */
    protected $_config = null;


    public function __construct($verbose = false)
    {
        parent::__construct($verbose);
    }


    public function setConfig(JobConfig $config)
    {
        $this->_config = $config;
    }


    public function getLogFile()
    {

        $logFile = $this->_config->getQueue() . '.resque.log';
        $logFile = rtrim($this->_config->log_path, '/') . "/$logFile";

        return $logFile;
    }


    protected function _getOut()
    {
        //prd($this->_config->log_path);
        if (!is_dir($this->_config->log_path)) {
            mkdir($this->_config->log_path);
        }

        $logFile = $this->getLogFile();

        if (!file_exists($logFile) || is_null($this->_file_out)) {
            $this->_file_out = fopen($logFile, 'a');
        }

        return $this->_file_out;
    }


    public function log($level, $message, array $context = [])
    {
        $pid = getmypid();
        if ($this->verbose) {
            fwrite(
                $this->_getOut(),
                '[' . $level . '][' . $pid . '][' . strftime('%Y-%m-%d %T') . '] ' . $this->interpolate($message, $context) . PHP_EOL
            );

            return;
        }

        if (!($level === LogLevel::INFO || $level === LogLevel::DEBUG)) {
            fwrite(
                $this->_getOut(),
                '[' . $level . '][' . $pid . ']' . $this->interpolate($message, $context) . PHP_EOL
            );
        }
    }

}