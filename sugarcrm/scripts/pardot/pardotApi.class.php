<?php

require_once('pardotResponse.class.php');
require_once('pardotProspect.class.php');
require_once('pardotLogger.class.php');

/**
 * This class is a collection of convenience functions for connecting to pardot
 * and querying its data.
 */
class pardotApi {
    /*
     * These members handle
     */
    private $apiUrl = 'https://pi.pardot.com/api';
    private $apiVersion = 3;
    private $apiEmail;
    private $apiPassword;
    private $apiUserKey;
    private $apiConnectionTimeout = 20;

    /**
     * After a successful authentication, this api key will be filled in and
     * is good for one hour.
     */
    private $apiKey;

    /**
     * A cache of responses.  We might not actually want to cache these since
     * they take up memory.
     */
    private $responses;

    /**
     * Upon successful authentication, we put a timestamp into this member
     */
    private $connectedSince;

    /**
     * Usually we want the count of the last result.
     */
    private $lastResultCount;


    /*
    * Unfortunately, PHP does not allow constants to be arrays, so the following are static.
    */

    /**
     * Operators that pardot should accept for requests.
     */
    private static $operators = array('create',
        'read',
        'query',
        'update',
        'upsert');

    /**
     * Allowed criteria for prospects.
     */
    private static $criteria = array('assigned' => '<boolean>',
        'assigned_to_user' => '<user_id>',
        'created_before' => '<time>',
        'created_after' => '<time>',
        'deleted' => '<boolean>',
        'id_greater_than' => '<integer>',
        'id_less_than' => '<integer>',
        'last_activity_before' => '<time>',
        'last_activity_after' => '<time>',
        'new' => '<boolean>',
        'score_greater_than' => '<integer>',
        'score_less_than' => '<integer>',
        'score_equal_to' => '<integer>');

    /**
     * Callbacks for validating criteria.
     */
    private static $validators = array('<boolean>' => array('self', 'isBoolean'),
        '<integer>' => array('self', 'isInteger'),
        '<time>' => array('self', 'isTime'),
        '<user_id>' => array('self', 'isUserId'));

    private $pardot_config;

    /**
     * The constructor.
     */
    public function __construct($apiEmail, $apiPassword, $apiUserKey, $force_new = false) {
        $this->apiEmail = $apiEmail;
        $this->apiPassword = $apiPassword;
        $this->apiUserKey = $apiUserKey;
        $this->apiKey = null;

        $this->responses = array();
        $this->connectedSince = null;

        require('pardot_config.php');
        $this->pardot_config = $pardot_config;

        $this->login($force_new);
    }

    /**
     * Fill in the default connection information here.  Note that this allows
     * us to still manually connect.
     */
    static public function magic($force_new = false) {
        static $api = null;
        if (!$api) {
            $api = new pardotApi('internalsystems@sugarcrm.com', 'oz0oxcutO', '9263f390befda902e936b329168605b3', $force_new);
        }
        return $api;
    }

    /**
     * Validate an operation
     */
    private function isValidOperation($operation) {
        return in_array($operation, self::$operators);
    }

    /**
     * Check that the field exists, that we have a filter set up for it, and
     * that the given value is a valid criterion
     */
    private function isValidCriterion($field, $value) {
        return (isset(self::$criteria[$field])
                && ($criteria = self::$criteria[$field])
                && ($validator = self::$validators[$criteria])
                && call_user_func($validator, $value));
    }

    /*
     * The input validation filters could be put into their own class, but whatever.
     */
    /**
     * This provides basic validation of a userid
     */
    private function isUserID($input) {
        return (self::isInteger($input) || (is_string($input) && self::isInteger(floatval($input))));
    }

    /**
     * This checks if a given input is a boolean suitable for passing into pardot.
     */
    private function isBoolean($input) {
        return (false === $input) || (true === $input) || ('true' === $input) || ('false' === $input);
    }

    /**
     * Check that the input is a number or a numeric string, and that
     * casting it as an integer has the same value as casting it as a
     * float so as to filter out non-whole numbers.
     */
    private function isInteger($input) {
        return is_numeric($input) && (intval($input) == floatval($input));
    }

    /**
     * Validates an input against the strings that pardot allows.
     */
    private function isTime($input) {
        return (is_string($input)
                && (in_array($input, array('today',
                    'yesterday',
                    'last_7_days',
                    'this_month',
                    'last_month'))
                        || strtotime($input)));
    }

    /**
     * This is a helper function that, given a key => value array, returns a
     * urlencoded and ampersand separated string
     */
    static public function arrayToQueryString($array) {
        $output_array = array();
        foreach ($array as $key => $value) {
            $output_array[] = urlencode($key) . '=' . urlencode($value);
        }
        return join('&', $output_array);
    }

    /**
     * Log in to pardot.
     */
    private function login($force_new = false) {
        $data = array(
            'email' => $this->apiEmail,
            'password' => $this->apiPassword,
            'user_key' => $this->apiUserKey
        );
        $request_url = $this->apiUrl . '?' . self::arrayToQueryString($data);

        $previous_connection = self::checkForExistingConnection();
        if ($force_new || !$previous_connection) {
            $ch = curl_init();
            $timeout = $this->apiConnectionTimeout;
            curl_setopt($ch, CURLOPT_URL, $request_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $data = curl_exec($ch);
            if ($data === false) {
                $curl_error = curl_error($ch);
                trigger_error($curl_error, E_USER_NOTICE);
            }
            curl_close($ch);
            if ($data) {
                $response = new pardotResponse($data, false);
                if (is_object($response) && $response->success()) {
                    $data = $response->getData();
                    $api_key = (string) $data->api_key[0];
                    if ($api_key) {
                        $this->apiKey = (string) $api_key;
                        $this->connectedSince = time();
                        $successful_set = self::setConnectionParams();
                        if (!$successful_set) {
                            trigger_error('Unable to set connection parameters in sugar database', E_USER_NOTICE);
                        }
                    } else {
                        trigger_error('Did not find api_key when expected', E_USER_NOTICE);
                    }
                } else {
                    trigger_error('There was a problem with the curl data', E_USER_NOTICE);
                }
            } else {
                trigger_error('Error retrieving login via curl with request url ' . $request_url, E_USER_NOTICE);
            }
        }

        return (boolean) $this->connectedSince;
    }

    private function assertSugarDatabase() {
        if (!isset($GLOBALS['db'])) {
            echo "Generated by " . __FILE__ . " on line " . __LINE__ . "\n";
            die('The pardot api must be used within the context of Sugar');
        }
    }

    /**
     * Check fro an existing connection to pardot, so we avoid connecting
     * The general purpose of the connection params is to reuse previously existing connections when we call the pardot login function
     */
    private function checkForExistingConnection() {
        $return_value = false;

        $connection_info = self::getConnectionParams();

        if (!empty($connection_info)) {
            $this->apiKey = (string) $connection_info['api_key'];
            $this->connectedSince = $connection_info['connected_since'];

            $return_value = true;
        }

        return $return_value;
    }

    private function assertConnectionIsValid() {
        $return_value = false;

        $previous_connection = self::checkForExistingConnection();

        if ($previous_connection) {
            $prospect = self::getProspectByEmail('sadek@sugarcrm.com');

            if ($prospect) {
                $return_value = true;
            }
        }

        // Discovered no valid Connection existed. Try to reconnect before returning
        if ($return_value == false) {
            $successful_login = self::login(true);
            if (!$successful_login) {
                trigger_error('Could not assert a valid connection', E_USER_WARNING);
                $this->apiKey = null;
                $this->connectedSince = null;
                $return_value = false;
            }
            else {
                $return_value = true;
            }
        }

        return $return_value;
    }

    /**
     * Get the connection parameters for pardot
     * The general purpose of the connection params is to reuse previously existing connections when we call the pardot login function
     */
    private function getConnectionParams() {
        self::assertSugarDatabase();

        $apiQuery = "select value from config where category = 'pardot' and name = 'api_key'";
        $connectedSinceQuery = "select value from config where category = 'pardot' and name = 'connected_since'";

        $res1 = $GLOBALS['db']->query($apiQuery);
        $res2 = $GLOBALS['db']->query($connectedSinceQuery);
        $api_key = '';
        $connected_since = '';

        $connection_info = array();

        if ($res1 && $res2) {
            $row1 = $GLOBALS['db']->fetchByAssoc($res1);
            $row2 = $GLOBALS['db']->fetchByAssoc($res2);
            if ($row1 && $row2) {
                $connection_info['api_key'] = $row1['value'];
                $connection_info['connected_since'] = $row2['value'];
            }
        }

        return $connection_info;
    }

    /**
     * Set the connection parameters for pardot, as stored in sugar internal via getConnectionParams
     * The general purpose of the connection params is to reuse previously existing connections when we call the pardot login function
     */
    private function setConnectionParams() {
        self::assertSugarDatabase();

        if (empty($this->apiKey) || empty($this->connectedSince)) {
            return false;
        }
        else {
            $apiQuery = "select value from config where category = 'pardot' and name = 'api_key'";
            $connectedSinceQuery = "select value from config where category = 'pardot' and name = 'connected_since'";
            $res1 = $GLOBALS['db']->query($apiQuery);
            $res2 = $GLOBALS['db']->query($connectedSinceQuery);
            if ($res1) {
                $row1 = $GLOBALS['db']->fetchByAssoc($res1);
                if (!empty($row1['value'])) {
                    $GLOBALS['db']->query("update config set value = '{$this->apiKey}' where category = 'pardot' and name = 'api_key'");
                }
                else {
                    $GLOBALS['db']->query("insert into config set value = '{$this->apiKey}', category = 'pardot', name = 'api_key'");
                }
            }
            if ($res2) {
                $row2 = $GLOBALS['db']->fetchByAssoc($res2);
                if (!empty($row2['value'])) {
                    $GLOBALS['db']->query("update config set value = '{$this->connectedSince}' where category = 'pardot' and name = 'connected_since'");
                }
                else {
                    $GLOBALS['db']->query("insert into config set value = '{$this->connectedSince}', category = 'pardot', name = 'connected_since'");
                }
            }

            return true;
        }
    }

    private function buildRequestURL($object, $operator, $identifier_type, $identifier, $parameters_for_request) {
        $parts = array($this->apiUrl, $object, 'version', $this->apiVersion, 'do', $operator);
        if (!is_null($identifier_type)) {
            $parts[] = $identifier_type;
        }
        if (!is_null($identifier)) {
            $parts[] = $identifier;
        }
        $request_url = join('/', $parts);

        $parameter_parts = array();
        $parameters_for_request['api_key'] = $this->apiKey;
        $parameters_for_request['user_key'] = $this->apiUserKey;
        foreach ($parameters_for_request as $key => $value) {
            $parameter_parts[$key] = $value;
        }

        $request_url .= '?' . self::arrayToQueryString($parameter_parts);

        return $request_url;
    }

    /**
     * Make a request from pardot.  Returns a pardotResponse object or null if
     * there was an error.
     */
    private function request($object,
                             $operator,
                             $identifier_type = null,
                             $identifier = null,
                             $parameters_for_request = null) {
        $response = null;

        if (!$this->connectedSince) {
            trigger_error('Tried to request from pardot, but not connected', E_USER_NOTICE);
            return $response;
        }
        /* https://pi.pardot.com/api/<object>/version/2/do/<operator>/<identifier_type>/<identifier>?
       * api_key=<your_api_key>&user_key=<your_user_key>&<parameters_for_request>
       */

        $request_url = self::buildRequestURL($object, $operator, $identifier_type, $identifier, $parameters_for_request);

        $successful_request = false;
        $iteration = 0;
        while (!$successful_request && $iteration < $this->pardot_config['request_attempts']) {
            // We call login on all requests, which inherently checks for a current connect
            // If the connection doesn't exist, it connects
            // The purpose of this is to ensure we don't error out if we lose a connection in the middle of making requests
            if ($iteration > 0 && ($identifier_type != 'email' || $identifier != 'sadek@sugarcrm.com')) {
                $asserted_valid_connection = self::assertConnectionIsValid();
                $request_url = self::buildRequestURL($object, $operator, $identifier_type, $identifier, $parameters_for_request);
                if (!$asserted_valid_connection) {
                    continue;
                }
            }

            $ch = curl_init();
            $timeout = $this->apiConnectionTimeout;
            curl_setopt($ch, CURLOPT_URL, $request_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $data = curl_exec($ch);
            if ($data === false) {
                $curl_error = curl_error($ch);
                trigger_error($curl_error, E_USER_NOTICE);
            }
            curl_close($ch);
            if ($data) {
                $notices = true;
                // Only display notices if we're not doing a test connection or we're on our last attempt
                if (($identifier_type == 'email' && $identifier == 'sadek@sugarcrm.com')) {
                    $notices = false;
                }
                else if ($iteration < $this->pardot_config['request_attempts'] - 1) {
                    $notices = false;
                }

                $response = new pardotResponse($data, $notices);
                /*
                 * Comment this next line out if we do not want to cache responses
                 */
                $this->responses[] = $response;
                $this->lastResultCount = $response->getResultCount();
                $successful_request = $response->success();
            } else {
                trigger_error('Error retrieving via curl with request URL: ' . $request_url, E_USER_NOTICE);
            }
            $iteration++;
        }

        return $response;
    }

    /**
     * Does what it says on the tin.
     */
    public function getLastResultCount() {
        return $this->lastResultCount;
    }

    /**
     * Retrieve prospects en masse from pardot.  Note that the returned
     * prospects do not have all of the data populated.  To get the full
     * complement of data, try getProspectById
     *
     * @param $criteria an array of items to search on.  See the API Developer Reference
     * @param $output The XML Response Format - full or mobile
     * @param $return_fields_as_array
     */
    public function getProspectsWhere($criteria = null, $output = 'full', array $return_fields_as_array = array()) {
        $data = array();
        if (!$this->connectedSince) {
            trigger_error('Not corrently connected', E_USER_NOTICE);
            return $data;
        }
        $filtered_criteria = array('deleted' => 'false');
        if ($criteria) {
            foreach ($criteria as $field => $value) {
                if (self::isValidCriterion($field, $value)) {
                    if (true === $value) {
                        $value = 'true';
                    } elseif (false === $value) {
                        $value = 'false';
                    }
                    $filtered_criteria[$field] = $value;
                } else {
                    trigger_error('Invalid criteria: ' . $field . ':' . $value, E_USER_NOTICE);
                }
            }
        }
        if (!empty($output)) {
            $filtered_criteria['output'] = $output;
        }
        $object = 'prospect';
        $operator = 'query';
        $identifier_type = null;
        $identifier = null;

        $response = self::request($object,
            $operator,
            $identifier_type,
            $identifier,
            $filtered_criteria);

        if (is_object($response) && $response->success()) {
            $xmldata = $response->getData();
            foreach ($xmldata->result->prospect as $i => $prospectXML) {
                $prospect = new pardotProspect();
                if ($prospect->loadFromSimpleXML($prospectXML)) {
                    // jwhitcraft 5.17.10 change to have it return an multi-dem array instead of an array of objects
                    // don't need to return the class but return an array based on the old fields needed
                    // so we don't chew up as much memory.
                    if (is_array($return_fields_as_array) && !empty($return_fields_as_array)) {
                        // loop through all the files that are request to be returned
                        foreach ($return_fields_as_array as $field) {
                            // avoid errors if the field doesn't exist
                            if(isset($prospect->$field)) {
                                $data[$prospect->id][$field] = $prospect->$field;
                            }
                        }
                        // unset the class as is no longer needed
                        unset($prospect);
                    } else {

                        $data[$prospect->id] = $prospect;
                    }
                    // end jwhitcraft change 5.17.10
                } else {
                    if ($response->displayNotices()) {
                        trigger_error('Error loading prospect', E_USER_NOTICE);
                    }
                }
            }
        } else {
            if (!is_object($response) || $response->displayNotices()) {
                $trigger_message = 'Did not get successful response';
                trigger_error($trigger_message, E_USER_NOTICE);
            }
        }
        return $data;
    }

    /**
     * Get all the information that Pardot has about a prospect based on either
     * id or email.
     */
    Private function getProspectByIdentifier($identifier_type, $identifier) {
        $valid_identifiers = array('email', 'id');
        $prospect = null;
        if (!$this->connectedSince) {
            trigger_error('Not corrently connected', E_USER_NOTICE);
            return $prospect;
        }
        if (!in_array($identifier_type, $valid_identifiers)) {
            trigger_error("$identifier_type is not a valid criterion", E_USER_NOTICE);
            return $prospect;
        }

        $object = 'prospect';
        $operator = 'read';

        $filtered_criteria = array();

        $response = self::request($object, $operator, $identifier_type, $identifier, $filtered_criteria);

        if (!is_null($response) && $response->success()) {
            $xmldata = $response->getData();
            if ($xmldata->prospect) {
                $prospect = new pardotProspect();
                if ($prospect->loadFromSimpleXML($xmldata->prospect)) {
                    return $prospect;
                } else {
                    if ($response->displayNotices()) {
                        trigger_error('Error loading prospect', E_USER_NOTICE);
                    }
                    $prospect = null;
                }
            }
        } else {
            if ($response->displayNotices()) {
                trigger_error('Did not get successful response', E_USER_NOTICE);
            }
        }
        return $prospect;
    }

    /**
     * Retrieve a single prospect by id.
     */
    public function getProspectById($prospect_id) {
        return self::getProspectByIdentifier('id', $prospect_id);
    }

    /**
     * Retrieve a single prospect by email address
     */
    function getProspectByEmail($prospect_email) {
        return self::getProspectByIdentifier('email', $prospect_email);
    }
}
