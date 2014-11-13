<?php
require_once 'PMSEValidate.php';
require_once 'modules/pmse_Inbox/engine/PMSELogger.php';

class PMSERecordValidator implements PMSEValidate
{
    /**
     *
     * @var Integer
     */
    protected $level;

    /**
     *
     * @var PMSELogger
     */
    protected $logger;

    /**
     *
     * @param type $level
     * @codeCoverageIgnore
     */
    public function __construct($level)
    {
        $this->level = $level;
        $this->logger = PMSELogger::getInstance();
    }

    /**
     *
     * @return type
     * @codeCoverageIgnore
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     *
     * @return type
     * @codeCoverageIgnore
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     *
     * @param PMSELogger $logger
     * @codeCoverageIgnore
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     *
     * @param type $level
     * @codeCoverageIgnore
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     *
     * @param PMSERequest $request
     * @return \PMSERequest
     */
    public function validateRequest(PMSERequest $request)
    {
        $this->logger->info("Validate Request " . get_class($this));
        $this->logger->debug("Request data" . print_r($request, true));

        $request->validate();
        return $request;
    }


}
