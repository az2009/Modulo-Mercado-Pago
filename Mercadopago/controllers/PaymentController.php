<?php
class Jbp_Mercadopago_PaymentController extends Mage_Core_Controller_Front_Action
{
  public function gatewayAction()
  {

    $order = new Mage_Sales_Model_Order();
    $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
    $order->loadByIncrementId($orderId);

    if ($orderId)
    {
      $arr_querystring = array(
        'flag' => 1,
        'orderId' => $orderId
      );

      Mage_Core_Controller_Varien_Action::_redirect('mercadopago/payment/response', array('_secure' => false, '_query'=> $arr_querystring));
    }
  }

  public function redirectAction()
  {
    $this->loadLayout();
//     $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','mercadopago',array('template' => 'mercadopago/redirect.phtml'));
//     $this->getLayout()->getBlock('content')->append($block);
//     $this->renderLayout();
  }

  public function responseAction()
  {
    if ($this->getRequest()->get("flag") == "1" && $this->getRequest()->get("orderId"))
    {
      $orderId = $this->getRequest()->get("orderId");
      $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
      $order->setState(Mage_Sales_Model_Order::STATE_NEW, true);
      $order->save();

      $order_id = Mage::getSingleton("checkout/session")->getLastRealOrderId();
      $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
      $order->sendNewOrderEmail();

      Mage::getSingleton('checkout/session')->unsQuoteId();
      
      Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=> false));
    }
    else
    {
      Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/error', array('_secure'=> false));
    }
  }


}
