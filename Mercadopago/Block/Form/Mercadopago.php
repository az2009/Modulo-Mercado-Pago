<?php
class Jbp_Mercadopago_Block_Form_Mercadopago extends Mage_Payment_Block_Form {
    
    /**
     * Define o template do bloco do formulário do método de pagamento
     * {@inheritDoc}
     * @see Mage_Payment_Block_Info::_construct()
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('jbp/mercadopago/form.phtml');
    }
    
}