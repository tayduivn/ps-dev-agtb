<?php
require_once 'PMSEValidate.php';
require_once 'modules/pmse_Inbox/engine/PMSELogger.php';

/**
 * Description of PMSEConcurrencyValidator
 * The concurrency validator class purpose is to filter duplicate requests
 * from the same event and process since it's possible to send twice the data
 * from a direct request.
 *
 */
class PMSEConcurrencyValidator implements PMSEValidate
{
    /**
     *
     * @var PMSELogger
     */
    protected $logger;

    /**
     * Class constructor.
     * Retrieving the logger instance from the singleton.
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->logger = PMSELogger::getInstance();
    }

    /**
     *
     * @return PMSELogger
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
     * Validates that if a second request from the same event and bean record
     * is received, the second request should be invalidated and thus ignored.
     * @param PMSERequest $request
     * @return \PMSERequest
     */
    public function validateRequest(PMSERequest $request)
    {
        $this->logger->info("Validate Request " . get_class($this));
        $this->logger->debug("Request data" . print_r($request, true));

        $args = $request->getArguments();
        $flowId = isset($args['idFlow']) ? $args['idFlow'] : $args['flow_id'];
        if (!isset($_SESSION['locked_flows']) || !in_array($flowId, $_SESSION['locked_flows'])) {
            $request->validate();
        } else {
            $request->invalidate();
        }
        return $request;
    }
}
