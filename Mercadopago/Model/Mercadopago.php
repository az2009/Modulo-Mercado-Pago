<?php
class Jbp_Mercadopago_Model_Mercadopago extends Mage_Payment_Model_Method_Abstract {
    
    //código único do método de pagamento
    protected $_code  = 'mercadopago_comum';
    
    //namespace do bloco do formulário que aparecera na seção checkout/pagamento
    //método de pagamento standard
        //protected $_formBlockType = 'mercadopago/form_mercadopago';
    
    //método de pagamento Cc
        protected $_formBlockType = 'mercadopago/form_mercadopago';
        
    //namespace do bloco de informações que aparecera na seção checkout informações de pagamento
    protected $_infoBlockType = 'mercadopago/info_mercadopago';
    
    /**
     * retorna os dados do formulário de pagamento enviado
     * e seta como informação na instancia do método de pagamento  
     * {@inheritDoc}
     * @see Mage_Payment_Model_Method_Abstract::assignData()
     */
    public function assignData($data)
    {
        
        if(!$data instanceof Varien_Object){
            $data = new Varien_Object($data);
        }
        
        $info = $this->getInfoInstance();
        
        $info->setFormData($data);
        
        return $this;
    }
    
    /**
     * pega as informações enviadas do form e efetua as validações necessárias
     * {@inheritDoc}
     * @see Mage_Payment_Model_Method_Abstract::validate()
     */
    public function validate()
    {
        parent::validate();
        
        $info = $this->getInfoInstance();
        
        var_dump($info->getFormData());
        
        Mage::throwException('fbdf');
        
        return $this;
    }
    
    /**
     * URL que irá redirecionar o usuário após a ordem ser colocada 
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return 'redirec';
    }
    
}