<?php
class Jbp_Mercadopago_Model_Recorrente 
    extends Mage_Payment_Model_Method_Abstract
        implements Mage_Payment_Model_Recurring_Profile_MethodInterface{
        
        protected $_formBlockType = 'mercadopago/form_mercadopago';
        protected $_code             = 'mercadopago_recorrente';
        protected $_canSaveCc        = false;
        protected $_canAuthorize     = true;
        protected $_canCapture       = true;
        protected $_canVoid          = true;
        protected $_canCancelInvoice = true;
        protected $_canRefund        = true;
        protected $_isGateway        = true;
        protected $_canReviewPayment = true;
        protected $_canManageRecurringProfiles  = true;
            
           /**
     * Validate data
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     * @throws Mage_Core_Exception
     */
    public function validateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile){}

    /**
     * Submit to the gateway
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     * @param Mage_Payment_Model_Info $paymentInfo
     */
    public function submitRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile, Mage_Payment_Model_Info $paymentInfo){}

    /**
     * Fetch details
     *
     * @param string $referenceId
     * @param Varien_Object $result
     */
    public function getRecurringProfileDetails($referenceId, Varien_Object $result){}

    /**
     * Check whether can get recurring profile details
     *
     * @return bool
     */
    public function canGetRecurringProfileDetails(){}

    /**
     * Update data
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     */
    public function updateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile){}

    /**
     * Manage status
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     */
    public function updateRecurringProfileStatus(Mage_Payment_Model_Recurring_Profile $profile){}
            
}
    