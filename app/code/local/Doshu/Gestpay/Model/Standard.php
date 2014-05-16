<?php
	
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'GestPayCrypt.inc.php';

    class Doshu_Gestpay_Model_Standard extends Mage_Payment_Model_Method_Abstract {  
    
  		protected $_code = 'gestpay';
		protected $_isGateway               = true;
		protected $_canAuthorize            = true;
		protected $_canCapture              = true;
		protected $_canCapturePartial       = false;
		protected $_canRefund               = false;
		protected $_canVoid                 = true;
		protected $_canUseInternal          = true;
		protected $_canUseCheckout          = true;
		protected $_canUseForMultishipping  = true;
		protected $_canSaveCc = false;
		protected $_isInitializeNeeded      = true;

		protected $_formBlockType = 'gestpay/form';
    	protected $_infoBlockType = 'gestpay/info';
    	
    	protected $_currencyMap = array(
    		'EUR' => 242
    	);

		public function getOrderPlaceRedirectUrl() {
			//when you click on place order you will be redirected on this url, if you don't want this action remove this method
			return Mage::getUrl('gestpay/standard/redirect', array('_secure' => true));
		}
		
		public function getPaymentUrl($order) {
		
			$orderData = $order->getData();
		
			$shopLogin = Mage::getStoreConfig('payment/gestpay/shop_login');
			$orderNumber = $order->getRealOrderId();
			$orderAmount = number_format($order->getGrandTotal(), 2, '.', '');
			$service = Mage::getStoreConfig('payment/gestpay/service_url');
			$urlOk = Mage::getStoreConfig('payment/gestpay/urlok');
			$urlKo = Mage::getStoreConfig('payment/gestpay/urlko');

			$currencyCode = $this->_currencyMap[Mage::app()->getStore()->getCurrentCurrencyCode()];
			
			$crypt = new GestPayCryptHS();
			$domainName = parse_url($service);
			$crypt->DomainName = $domainName['host'];
	
			$crypt->SetShopLogin($shopLogin); // Es. 9000001
			$crypt->SetShopTransactionID($orderNumber); // Identificativo transazione. Es. "34az85ord19"
			$crypt->SetAmount($orderAmount); // Importo. Es.: 1256.50
			$crypt->SetCurrency($currencyCode); // Codice valuta. 242 = euro
	
			if ($crypt->Encrypt()) {
				$url = $service."?a=".$crypt->GetShopLogin()."&b=".$crypt->GetEncryptedString();
				return $url;
			}
			else
				die("Errore: ".$crypt->GetErrorCode().": ".$crypt->GetErrorDescription()."\n");
			
		}
		
		
		public function isSuccessResponse($request) {
			
			$crypt = new GestPayCrypt();
			if (!empty($_GET["a"]) && !empty($_GET["b"])) {
				$crypt->SetShopLogin($_GET["a"]);
				$crypt->SetEncryptedString($_GET["b"]);
				if($crypt->Decrypt()) {
					switch ($crypt->GetTransactionResult()) {
						case "XX":
							return true;
							break;
						case "KO":
							return false;
							break;
						case "OK":
							return true;
							break;
						default:
							return true;
							break;
					}
				}
			}
			
			return false;
			
		}

    }  
