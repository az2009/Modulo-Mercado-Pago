<?php
class Jbp_Mercadopago_Block_Form_Cc extends Mage_Payment_Block_Form_Ccsave {
    public function _construct(){
        $this->setTemplate('jbp/mercadopago/ccsave.phtml');
    }
}