<?php
namespace Yiiep;
/**
 * Description of ApiClient
 *
 * @author Elom Raydino
 * @package Yiiep
 */

require_once '../vendor/autoload.php';

use Ahc\Jwt\JWT;

class ApiClient {
    protected $_protocole = 'https';
    protected $_host = 'sandbox.yiiep.com';
    protected $_port = '443';
    protected $_path = '/webapi/v2/';
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
    public function __construct($apiId = '', $apiKey = '', $testmode = false) {
        $this->_testmode = $testmode;
        $this->_publicKey = $apiId;
        $this->_privateKey = $apiKey;
		if($this->_testmode === false){
            $this->_host = 'yiiep.com';
        }
        $this->_baseUrl = "{$this->_protocole}://{$this->_host}:{$this->_port}{$this->_path}";
        $this->_lastReply = array();
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
     * 
     * AddSignature to request params
     * 
     * @param string $method
     * @param array $params
     * @return array
     */
    public function sign($ressource, $params) {
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

        $jwt = new JWT($this->_privateKey, 'HS256', 180);
        $payload = array('apicall' => $params, 'sub' => $ressource, 'iss' =>'Yiiep WebAPI', 'iat' => time());
        $token = $jwt->encode($payload);
        return $token;
    }
    /**
     * 
     * Parse request response
     * 
     * @param Request Response Object $response
     * @return array
     */
    public function parseResponse($httpResponse) {
        //Http Status
        if ($httpResponse->success) {
            $apiResponse = json_decode($httpResponse->body, true);

            //JWT encoded
            if(isset($apiResponse['sdata'])){
                $jwt = new JWT($this->_privateKey, 'HS256', 180);
                $payload = $jwt->decode($apiResponse['sdata']);

                //Chech JWT decode is OK
                if(!is_array($payload) || !isset($payload['yiiepdata'])){
                    return array('success' => false, 'message' => 'Invalid Response', 'status' => '500');
                }

                $apiResponse = json_decode(json_encode($payload['yiiepdata']), true);

                //var_dump($apiResponse);
            }

            return $apiResponse;

        } else {
            return array('success' => false, 'message' => 'Request Failed', 'status' => $httpResponse->status_code);
        }
    }
    
    /**
     * 
     * @param array $response; Appi call response
     * @return boolean
     */
    public function isSuccess($response){
        $this->_lastReply = $response;
        return (isset($this->_lastReply['success']) && $this->_lastReply['success'] === TRUE);
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
     * Post data to server
     * 
     * @param type $ressource
     * @param type $params
     * @return type array
     */
    public function send($ressource, $params) {
        //Signing
        $signed = $this->sign($ressource, $params);
        //$url
        $url = "{$this->_baseUrl}{$ressource}";
        //Request
        $response = \Requests::post($url, array('Accept' => 'application/json'), array('identity'=> $this->_publicKey,'data'=> $signed));
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
