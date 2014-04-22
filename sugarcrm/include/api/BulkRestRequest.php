<?php
/*
* By installing or using this file, you are confirming on behalf of the entity
* subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
* the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
* http://www.sugarcrm.com/master-subscription-agreement
*
* If Company is not bound by the MSA, then by installing or using this file
* you are agreeing unconditionally that Company will be bound by the MSA and
* certifying that you have authority to bind Company accordingly.
*
* Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
*/
/**
 * Request class for bulk requests
 */
class BulkRestRequest extends RestRequest
{
    /**
     * Construct request from request data
     * @param array $request
     */
    public function __construct($request)
    {
        $svars = $_SERVER;
        $rvars = array();

        $rvars['__sugar_url'] = $request['url'];
        if(!empty($request['headers'])) {
            foreach($request['headers'] as $hname => $hval) {
                $svars['HTTP_'.str_replace("-", "_", strtoupper($hname))] = $hval;
            }
        }
        if(!empty($request['method'])) {
            $svars['REQUEST_METHOD'] = $request['method'];
        } else {
            $svars['REQUEST_METHOD'] = 'GET';
        }

        if(isset($request['data'])) {
            $this->postContents =  $request['data'];
        }

        parent::__construct($svars, $rvars);
    }
}

