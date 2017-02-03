<?php
require_once Mage::getBaseDir('lib').'/Mercadopago/mercadopago.php';
 
class Jbp_Mercadopago_Helper_Data extends Mage_Payment_Helper_Data {
 
    /**
     * retorna uma instancia da SDK MP 
     * @return MP
     */
    public function getClient(){
        try{
            $mp = new MP($this->getAccessToken());
            return $mp;            
        }catch(Exception $e){
            Mage::log($e->getMessage());
        }
    }
    
    
    
    /**
     * pega os valores dos campos renderizados do form de Cc do Magento
     * e seta os valores de acordo com o  MP
     * @param unknown $info
     * @return string[]|NULL[]
     */
    public function prepareDataCard($info, $amount = null){
        
        return [
            'amount' => (double)$amount,
            'total_amount' => (double)$amount,
            'card_number' => $info->getCcNumber(),
            'security_code' => $info->getCcCid(),
            'card_expiration_month' => $info->getCcExpMonth(),
            'expiration_month' => $info->getCcExpMonth(),
            'card_expiration_year' => $info->getCcExpYear(),
            'expiration_year' => $info->getCcExpYear(),
            'card_holder_name' => $info->getCcOwner(),
            'doc_type' => 'CPF',
            'doc_number' => $info->getCcDocNumber(),
            'payment_method_id' => $this->getCodeCcCard($info->getCcType()),
            'site_id' => 'MLB'
        ];        
    }
    
    
    /**
     * gera o token do cartão via backend
     * @param unknown $data
     * @throws Exception
     */
    public function generateToken($data,$amount){
        
        try{
            
            $response = $this->getClient()
                              ->post('/v1/card_tokens/?public_key='.$this->getPublickey(),
                                    json_encode($data->getData(),true));
            
            if(count($response))
                $response = new Varien_Object($response);
                              
            if($response->getStatus() != 200 && 
                    $response->getStatus() != 201 && 
                        !empty($response->getResponse()['id']))
                                throw new Exception('fail generate token');
            
            return $response->getResponse();
            
        }catch(Exception $e){
            echo $e->getMessage();
            Mage::throwException('não foi possível gerar o token');
            Mage::log($e->getMessage());
        }
    }
    
    /**
     * retorna a chave PUBLIC KEY Mercado Pago
     * @return unknown
     */
    public function getPublickey(){
        $config = Mage::getStoreConfig('payment/mercadopago/config_public_key');
        return $config;
    }
    
    /**
     * retorna a chave ACCESS TOKEN Mercado Pago
     * @return unknown
     */
    public function getAccessToken(){
        $config = Mage::getStoreConfig('payment/mercadopago/config_access_token');
        return $config;
    }
    
    /**
     * retorna os status autorizados para 
     * efetuar o pagamento ou criar o pedido
     * @param unknown $status
     * @return boolean
     */
    public function getStatusPayOrLoad($status){
        return in_array($status, ['approved','authorized','in_process','in_mediation']);
    }
    
    /**
     * retorna os status pagamento rejeitado
     * @param unknown $status
     * @return boolean
     */
    public function getStatusRejected($status){
        return in_array($status, ['rejected','cancelled']);
    }
    
    /**
     * retorna os status pagamento devolvido
     * @param unknown $status
     * @return boolean
     */
    public function getStatusRefunded($status){
        return in_array($status, ['refunded','charged_back']);
    }
    
    /**
     * Retorna os itens do carrinho
     * @param unknown $id
     * @return NULL[][]
     */
    public function getQuoteItems(){        
        
        $quote  = Mage::getSingleton('checkout/session');        
        
        $item  = [];
        
        if(!$quote->getQuote()->hasItems())
            return $item;
        
        foreach($quote->getQuote()->getAllVisibleItems() as $items){
            $item[] = [
                'id'          => $items->getSku(),
                'title'       => $items->getName(),
                'quantity'    => $items->getQty(),
                "description" => $items->getName(),
                'unit_price'  => $items->getPrice(),
                "picture_url" => (string)Mage::helper('catalog/image')->init($items->getProduct(), 'image'),
            ];
        }
        return $item;
    }
    
    /**
     * retorna as mensagens do módulo
     * @param unknown $key
     * @return string
     */
    public function getMsgError($key){
        
        $msg = [
            '500' => 'Não foi possível processar a forma de pagamento',
            'payment_not_authorized' => 'Paramento não autorizado, revise a forma de pagamento',
        ];
        
        if(array_key_exists($key, $msg))
            return $msg[$key];
        return 'msg_not_found';
    }
    
    /**
     * retorna as labels corretas dos issuers MP
     * @param unknown $code
     * @return string
     */
    public function getCodeCcCard($code){
        switch ($code){
            case 'VI':
                return 'visa';
            break;
            
            case 'MC':
                return 'master';
                break;
        }
    }
    
    
       
    
}
