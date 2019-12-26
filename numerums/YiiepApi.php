<?php
namespace Yiiep;
/**
 * Description of Yiiep Simplified API V1
 *
 * @author Elom Raydino
 * @package Yiiep
 */

class YiiepApi extends ApiClient{
    
    /**
     * 
     * constructor
     * 
     * @param boolean $testmode : Set appi call in test mode or not; default false
     * @param string $publicKey : Web seller api ID
     * @param type $privateKey : Web seller api secret for request signature
     */
    public function __construct($publicKey, $privateKey, $testmode = false) {
        parent::__construct($publicKey, $privateKey, $testmode);
    }
    
    /**
     * 
     * Setup bill on Yiiep plateform
     * 
     * @param string $billId
     * @param int $billAmount
     * @param int $billCurrency // XAF |XOF | NGN | GHS
     * @return boolean
     */
    public function presetBill($billId, $billAmount, $billCurrency){
        //Params
        $param['bill'] = $billId;
        $param['value'] = (float)$billAmount;
        $param['crcy'] = $billCurrency;
        //Query
        
        return $this->isSuccess($this->send('preset', $param));
    }
    
    /**
     * Cancel Bill on Yiiep
     * 
     * @param string $billHash
     * @return boolean
     */
    public function unsetBill($billHash){
        //Params
        $param['hash'] = $billHash;
        
        return $this->isSuccess($this->send('unset', $param));
    }
    
    /**
     * 
     * Pay Bill
     * 
     * @param string $billHash
     * @param string $paycode
     * @return boolean
     */
    public function payBill($billHash, $paycode){
        //Params
        $param['hash'] = $billHash;
        $param['paycode'] = $paycode;
        
        return $this->isSuccess($this->send('pay', $param));
    }
    
    /**
     * 
     * Get bill state
     * 
     * @param string $billHash
     * @return boolean
     */
    public function checkBill($billHash){
        //Params
        $param['hash'] = $billHash;
        
        return $this->isSuccess($this->send('bstate', $param));
    }
    
    /**
     * 
     * Cancel bill payment
     * 
     * @param string $billHash
     * @return boolean
     */
    public function refundBill($billHash){
        //Params
        $param['hash'] = $billHash;
        
        return $this->isSuccess($this->send('bstate', $param));
    }
    
    /**
     * 
     * Get web seller account state
     * 
     * @return boolean
     */
    public function accountState(){
        $param = array(); //TODO : add param to handle date period
        return $this->isSuccess($this->send('astate', $param));
    }
    
    /**
    * 
    * Build Yiiep pay url for a bill
    * 
    * @param string $billhash
    * @param string $class ; css class 
    * @return string
    */
   public function payUrl($billhash){
       return $this->_baseUrl . "pay/{$billhash}";
   }
   
   /**
    * 
    * Build Yiiep pay link tag for a bill
    * 
    * @param string $billhash
    * @param string $class ; css class 
    * @return string
    */
   public function payLink($billhash, $class = ''){
       return '<a class="' . $class . '" target="_blank" href="' . $this->payUrl($billhash) . '">YiiepPay</a>';
   }
    
    /**
     * 
     * Build Yiiep pay qr code imqge tag for a bill
     * 
     * @param string $billhash
     * @param string $class ; css class 
     * @return string (img tag);
     */
    public function payQR($billhash, $class = ''){
	$qrSrc = $this->qrSource($billhash);
        return '<a target="_blank" href="' . $this->_baseUrl . "pay/{$billhash}" . '"><img src="' . $qrSrc . '" class="' . $class . '"></a>';
    }
    
    /**
     * 
     * Build Yiiep QR Code link for a bill
     * 
     * @param string $billhash
     * @return string
     */
    public function qrSource($billhash){
        return $this->_baseUrl . "qrcode/{$billhash}";
    }
    
    /**
     * 
     * Transfert money to a Yiiep Account
     * 
     * @param float $amount - Money to transfer
     * @param string $currency - Currency ISO code
     * @param string $receiver - Yiiep Account
     * @return boolean
     *
     * Success Data contain : [uref,tif,ftid,xofamount,xoffees,xofbalance,currency,rate,amount,fees,balance,date]
     */
    public function transfer($amount, $currency, $receiver){
        //Params
        $param['value'] = (float)$amount;
        $param['crcy'] = $currency;
        $param['to'] = $receiver;
        //Query
        
        return $this->isSuccess($this->send('transfer', $param));
    }
    
    /**
     * 
     * Evaluate fees for a money transfert
     * 
     * @param float $amount - Money to transfer
     * @param string $currency - Currency ISO code
     * @param string $receiver - Yiiep Account
     * @return boolean
     *
     * Success Data contain : [xofamount,xoffees,xofbalance,currency,rate,amount,fees,balance,date]
     */
    public function evaluate($amount, $currency, $receiver){
        //Params
        $param['value'] = (float)$amount;
        $param['crcy'] = $currency;
        $param['to'] = $receiver;
        //Query
        
        return $this->isSuccess($this->send('evaluate', $param));
    }
    
}