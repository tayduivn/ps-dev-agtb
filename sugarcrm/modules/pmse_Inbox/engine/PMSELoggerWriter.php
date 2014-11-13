<?php

class PMSELoggerWriter extends SugarLogger
{

    public function __construct()
    {
        $config = SugarConfig::getInstance();
        $this->ext = '.log';
        $this->logfile = 'PMSE';
        $this->dateFormat = $config->get('logger.file.dateFormat', $this->dateFormat);
        $this->logSize = $config->get('logger.file.maxSize', $this->logSize);
        $this->maxLogs = $config->get('logger.file.maxLogs', $this->maxLogs);
        $this->filesuffix = $config->get('logger.file.suffix', $this->filesuffix);
        $log_dir = $config->get('log_dir' , $this->log_dir);
        $this->log_dir = $log_dir . (empty($log_dir)?'':'/');
        unset($config);
        $this->_doInitialization();
    }

    /**
     * 
     * @param type $dateFormat
     * @codeCoverageIgnore
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }
}

