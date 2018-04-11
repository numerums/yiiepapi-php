<?php
/**
 * Description of ApiClient
 *
 * @author Elom Raydino
 */
require_once '../vendor/autoload.php';

class ApiClient {
    protected $_protocole = 'https';
    protected $_host = 'www.yiiep.com';
    protected $_port = '443';
    protected $_path = '/webapi/v1/';
    protected $_testmode = false;
    protected $_publicKey = '';
    protected $_privateKey = '';
    protected $_baseUrl = '';
    private $_lastReply;
    /**
     * 
     * Constructor
     * 
     * @param string $publicKey
     * @param string $privateKey
     */
    public function __construct($testmode = false, $publicKey = '', $privateKey = '') {
        $this->_testmode = $testmode;
        $this->_publicKey = $publicKey;
        $this->_privateKey = $privateKey;
        $this->_baseUrl = "{$this->_protocole}://{$this->_host}:{$this->_port}{$this->_path}";
        $this->_lastReply = array();
        //Requests::register_autoloader();
    }
    /** Calculate key based hashcode for given string
     * 
     * @param String $data
     * @param String $key
     * @param String $arlgotithme
     * @return String
     */
    public function getHash($data, $key, $arlgotithme = 'sha256') {
        return hash_hmac($arlgotithme, $data, $key);
    }
    /**
     * 
     * @param int $length
     * @return string
     */
    public function random_str($length = 16) {
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }
    /**
     *  Function to order query string before calculating signature
     * 
     * @param array $paramsArray
     * @param boolean $rawencode
     * @return string
     */
    public function getOrderedQS($paramsArray, $rawencode = true) {
        $array = array();
        foreach ($paramsArray as $index => $param) {
            array_push($array, array('key' => $index, 'val' => $param));
        }
        // Unsorted array of strings to hold ASCII byte encodings.  
        $ordBytes = array();
        foreach ($array as $param) {
            $bytes_str = "";
            // Concatenate key+val pairs and expand to array of char bytes.
            $chars = str_split($param['key'] . $param['val'], 1);
            // Convert chars to string of ASCII in hex format.
            foreach ($chars as $char) {
                $bytes_str .= dechex(ord($char));
            }
            // Now holds string of key+value in ASCII hex.
            array_push($ordBytes, $bytes_str);
        }
        // Sort hex strings, keep index. 
        asort($ordBytes, SORT_STRING);
        $retval = "";
        $len = count($array) - 1;
        foreach ($ordBytes as $index => $value) {
            // Build return string using the reordered index.
            if ($rawencode) {
                $retval .= rawurlencode($array[$index]['key']) . "=" . rawurlencode($array[$index]['val']);
            } else {
                $retval .= $array[$index]['key'] . "=" . $array[$index]['val'];
            }
            if ($len--)
                $retval .= "&";
        }
        // Return key/value pairs with amazingly fresh indexing.
        return $retval;
    }
    /**
     * 
     * AddSignature to request params
     * 
     * @param string $method
     * @param array $params
     * @return array
     */
    public function sign($method, $ressource, $params) {
        //Call Mode
        if($this->_testmode){
            $params['mode'] = 'test';
        }else{
            $params['mode'] = 'real';
        }       
        //Originating Time
        $params['time'] = time();
        //Client ID
        $params['identity'] = $this->_publicKey;
        //Random Seed
        $params['rseed'] = $this->random_str();
        //Request Method
        $request['method'] = $method;
        //--Host
        $request['host'] = "{$this->_host}:{$this->_port}";
        //--Path
        $request['path'] = "{$this->_path}{$ressource}";
        //--Query string - Sort before hashing
        $request['querystr'] = $this->getOrderedQS($params, false);
        $toSign = implode("\n", $request);
        $params['signature'] = $this->getHash($toSign, $this->_privateKey);        
        return $params;
    }
    /**
     * 
     * Parse request response
     * 
     * @param Request Response Object $response
     * @return array
     */
    public function parseResponse($response) {
        if ($response->success) {
            return json_decode($response->body, true);
        } else {
            return array('success' => false, 'message' => 'Request Fail', 'status' => $response->status_code);
        }
    }
    
    /**
     * 
     * @param array $response; Appi call response
     * @return boolean
     */
    public function isSuccess($response){
        $this->_lastReply = $response;
        return (isset($this->_lastReply[success]) && $this->_lastReply[success] === TRUE);
    }
    
    /**
     * 
     * Last api call fail message; empty string if no error
     * 
     * @return string
     */
    public function message(){
        if(isset($this->_lastReply['message'])){
            return $this->_lastReply['message'];
        }else{
            return '';
        }
    }
    
    /**
     * 
     * last api call data; empty array if call success
     * 
     * @return array
     */
    public function data(){
        if(isset($this->_lastReply['data'])){
            return $this->_lastReply['data'];
        }else{
            return array();
        }
    }
    /**
     * 
     * Make Http query
     * 
     * @param type $ressource
     * @param type $params
     * @return type array
     */
    public function query($ressource, $params = array()) {
        //Signing
        $signed = $this->sign('GET', $ressource, $params);
        $url = "{$this->_baseUrl}{$ressource}?" . $this->getOrderedQS($signed, true);
        //die($url);
        $response = Requests::get($url, array('Accept' => 'application/json'));
        //var_dump($request->status_code);
        return $this->parseResponse($response);
    }
    /**
     * 
     * Post data to server
     * 
     * @param type $ressource
     * @param type $params
     * @return type array
     */
    public function send($ressource, $params) {
        //Signing
        $signed = $this->sign('POST', $ressource, $params);
        //$url
        $url = "{$this->_baseUrl}{$ressource}";
        //Request
        $response = Requests::post($url, array('Accept' => 'application/json'), $signed);
        //var_dump($response);
        return $this->parseResponse($response);
    }
    /**
     * 
     * Return API ID
     * 
     * @return string
     */
    public function getId(){
        return $this->_publicKey;
    }
}