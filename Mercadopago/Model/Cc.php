<?php
class Jbp_Mercadopago_Model_Cc extends Mage_Payment_Model_Method_Cc {
    
    protected $_code             = 'mercadopago';
    protected $_formBlockType    = 'mercadopago/form_cc';
    protected $_canSaveCc        = false;
    protected $_canAuthorize     = true;
    protected $_canCapture       = true;
    protected $_canVoid          = true;
    protected $_canCancelInvoice = true;
    protected $_canRefund        = true;
    protected $_isGateway        = true;
    protected $_canReviewPayment = true;
    protected $_token = null;
    protected $info   = null;
    
     /**
      * atribui os dados do form ao a instancia de pagamento
      * {@inheritDoc}
      * @see Mage_Payment_Model_Method_Cc::assignData()
      */
     public function assignData($data){
         
         if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
         }
        
         parent::assignData($data);
         
         $info = $this->getInfoInstance();
         
         $data = $this->_getHelper()->prepareDataCard($data, $this->getQuote()->getBaseGrandTotal());
         
         $info->setAdditionalInformation($data);
         
         return $this;
         
     }
    
     
    /**
     * método que autoriza o pagamento através da API 
     *  - autoriza o pagamento
     *    - cria um pedido com status processing
     *      - registra o id da transação
     *        - adiciona um comentário
     *          - não cria a fatura do pedido 
     * {@inheritDoc}
     * @see Mage_Payment_Model_Method_Abstract::authorize()
     */
    public function authorize($payment, $amount)
    {
        $order = $payment->getOrder();
        
        $result = $this->callApi($payment, $amount, 'authorize');
        
        if($result === false){
            
            $errorMsg  = $this->_getHelper()->getMsgError('500');
            
            Mage::log($result, null, '500_'.$this->getCode().'.log');
            
        }else{
            
            if($this->_getHelper()->getStatusPayOrLoad($result['status'])){
                
                $payment->setTransactionId($result['transaction_id']);
                
                $payment->setIsTransactionClosed(0);
                
                $payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, ['key1' => 'value']);
                
                Mage::log($result, null, '200_'.$this->getCode().'.log');
                
            }elseif($this->_getHelper()->getStatusRejected($result['status'])){
                Mage::log($result, null, '403_'.$this->getCode().'.log');
                $errorMsg  = $this->_getHelper()->getMsgError('payment_not_authorized');                
            }else{
                Mage::log($result, null, '500_'.$this->getCode().'.log');
                $errorMsg  = $this->_getHelper()->getMsgError('500');                
            }
            
        }
        
        if($errorMsg)
            Mage::throwException($errorMsg);
            
        return $this;
    }
        
    /**
     * método que autoriza e captura do pagamento através da API
     *  - autoriza o pagamento
     *    - cria um pedido com status processing
     *      - registra o id da transação
     *        - adiciona um comentário
     *          - cria a fatura do pedido
     * {@inheritDoc}
     * @see Mage_Payment_Model_Method_Abstract::authorize()
     */
    public function capture($payment, $amount){
        $this->authorize($payment, $amount);
        return $this;
    }
    
    public function getQuote(){
        return Mage::getSingleton('checkout/session')->getQuote();
    }
       
    /**
     * método executado antes do reembolso
     * {@inheritDoc}
     * @see Mage_Payment_Model_Method_Abstract::processBeforeRefund()
     */
    public function processBeforeRefund($invoice, $payment){}
    
    /**
     * método que executa o reembolso através da API
     * {@inheritDoc}
     * @see Mage_Payment_Model_Method_Abstract::refund()
     */
    public function refund($payment, $amount){
        $order = $payment->getOrder();
        $result = $this->callApi($payment, $amount, 'refund');
        if($result === false){
            $errorMsg = $this->_getHelper()->getMsgError('500');
            Mage::throwException($errorMsg);
        }
        return $this;
    }
    
    /**
     * método executado depois do reembolso
     * {@inheritDoc}
     * @see Mage_Payment_Model_Method_Abstract::processCreditmemo()
     */
    public function processCreditmemo($invoice, $payment){}
       
    /**
     * chamada da API para executar os procedimentos
     * @param unknown $payment
     * @param unknown $amount
     * @param unknown $type
     * @return number[]|NULL[]
     */
    public function callApi($payment, $amount, $type){
        
        try{
            
            if($type == 'authorize'){
                
                $response = $this->pay();
                
                if($this->_getHelper()->getStatusPayOrLoad($response['status']))                    
                    return ['status'=> $response['status'], 
                                    'transaction_id' => $response['id'], 
                                        'fraud' => 0];
                
                    
                    Mage::log($response, null, 'fail_'.$this->getCode().'.log');
                    
                    return false;
                    
            }elseif('refund'){
                /**A implementar*/
            }
            
         }catch(Exception $e){
            Mage::log($e->getMessage());
            Mage::throwException($this->_getHelper()->getMsgError('500'));
        }
        
        return false;
    }
    
    
    /**
     * prepara os dados para ser enviados ao MP
     * @return string[]|number[]|NULL[]|unknown[]|string[][]|NULL[][]
     */
    public function prepareDataBeforeSend(){
        
        $info = $this->getInfoInstance();
        
        $amount = $info->getData('amount_authorized');
                
        $data = new Varien_Object($info->getData('additional_information'));
        
        $this->_token = $this->_getHelper()->generateToken($data, $amount);
        
        $tokenId = $this->_token['id'];
        
        $payment_data = [
            "transaction_amount" => $amount,
            "token" => $tokenId,
            "description" => "Order:".$info->getOrder()->getData('increment_id'),
            "installments" => 1,
            "external_reference" => $info->getOrder()->getData('increment_id'), 
            "notification_url" => "http://www.magentoteste.dev/mercadopago/notify/index",
            "payment_method_id" => $data->getData('payment_method_id'),
            "payer" => [
                "email" => $info->getOrder()->getData('customer_email'),
                'identification' => [
                    "type" => !$info->getOrder()->getData('customer_is_guest') ? 'registered' : 'guest'
                ]                        
            ],
            "additional_info" => [
                'ip_address'  => $info->getOrder()->getData('remote_ip'),
                'items'       => $this->_getHelper()->getQuoteItems(),
                "payer" => [
                    "first_name" => $info->getOrder()->getData('customer_firstname'),
                    "last_name"  => $info->getOrder()->getData('customer_lastname'),
                ]
            ]
        ];
        
        return $payment_data;
    }
    
    /**
     * envia as informações para pagamento ao MP
     * @return unknown|boolean
     */
    public function pay(){
        
        try{
            
            $data = $this->prepareDataBeforeSend();
            
            $payment = $this->_getHelper()
                            ->getClient()
                            ->post("/v1/payments", $data);
            
            return $payment;
                            
        }catch(Exception $e){
            Mage::log($e->getMessage());
            Mage::throwException($this->_getHelper()->getMsgError('500'));
        }
        
        return false;
    }
        
    /**
     * retorna uma instancia da helper do módulo
     * {@inheritDoc}
     * @see Jbp_Mercadopago_Helper_Data::_getHelper()
     */
    public function _getHelper(){
        return Mage::helper('mercadopago');
    }
    
}