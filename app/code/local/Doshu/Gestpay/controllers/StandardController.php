<?php

	/*

	*/

	class Doshu_Gestpay_StandardController extends Mage_Core_Controller_Front_Action {
    
    	public function RedirectAction() {
    		
    		$session = Mage::getSingleton('checkout/session');
    		$orderId = $session->getLastRealOrderId();
        	$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        	$api = Mage::getModel('gestpay/standard');
        	$url = $api->getPaymentUrl($order);
        	header('Location: '.$url);
    		exit;
    	}
    	
    	
    	public function  successAction() {
        	$session = Mage::getSingleton('checkout/session');
        	$api = Mage::getModel('gestpay/standard');
        	if($api->isSuccessResponse($this->getRequest()) && $session->getLastRealOrderId()) {
        		$order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
        		$order->setState(Mage::getStoreConfig('payment/gestpay/order_status'), true)->save();
        		$order->sendNewOrderEmail();
        		Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
        		$this->_redirect('checkout/onepage/success', array('_secure'=>true));
        	}
        	else
        		$this->_redirect('checkout/onepage/failure', array('_secure'=>true));
    	}
    	
    	public function errorAction() {
		    $session = Mage::getSingleton('checkout/session');
		    if ($session->getLastRealOrderId()) {
		        $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
		        if ($order->getId()) {
		            $order->cancel()->save();
		        }
		    }
		    $this->_redirect('checkout/onepage/failure', array('_secure'=>true));
		}
	
	}

?>
