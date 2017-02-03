<?php
class Jbp_Mercadopago_Block_Info_Mercadopago extends Mage_Payment_Block_Info
{
    /**
     * Define o template do bloco de informaçẽos do método de pagamento
     * {@inheritDoc}
     * @see Mage_Payment_Block_Info::_construct()
     */
    protected function _construct(){
        parent::_construct();
        $this->setTemplate('jbp/mercadopago/info.phtml');
    }
    
    /**
     * prepara as informações do método de pagamento para serem lançadas 
     * no bloco do frontend checkout/info/payment
     * {@inheritDoc}
     * @see Mage_Payment_Block_Info::_prepareSpecificInformation()
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation)
        {
          return $this->_paymentSpecificInformation;
        }

        $info = $this->getInfo();
    
        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);
        $transport->addData([
            'Campos' => ''
        ]);

    return $transport;
  }
}