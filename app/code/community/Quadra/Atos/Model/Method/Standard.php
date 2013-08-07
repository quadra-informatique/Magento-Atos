<?php

/**
 * 1997-2013 Quadra Informatique
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is available
 * through the world-wide-web at this URL: http://www.opensource.org/licenses/OSL-3.0
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to ecommerce@quadra-informatique.fr so we can send you a copy immediately.
 *
 * @author Quadra Informatique <ecommerce@quadra-informatique.fr>
 * @copyright 1997-2013 Quadra Informatique
 * @version Release: $Revision: 3.0.1 $
 * @license http://www.opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
class Quadra_Atos_Model_Method_Standard extends Quadra_Atos_Model_Method_Abstract {

    protected $_code                = 'atos_standard';
    protected $_formBlockType       = 'atos/form_standard';
    protected $_infoBlockType       = 'atos/info_standard';
    protected $_redirectBlockType   = 'atos/redirect_standard';

    /**
     * Payment Method features
     * @var bool
     */
    protected $_isInitializeNeeded      = true;
    protected $_canUseForMultishipping  = false;

    /**
     * First call to the Atos server
     */
    public function callRequest() {
        // Affectation des paramètres obligatoires
	$parameters = "merchant_id=" . $this->getConfig()->getMerchantId();
	$parameters .= " merchant_country=" . $this->getConfig()->getMerchantCountry();
	$parameters .= " amount=" . $this->_getAmount();
	$parameters .= " currency_code=" . $this->getConfig()->getCurrencyCode($this->_getQuote()->getQuoteCurrencyCode());

	// Initialisation du chemin du fichier pathfile
	$parameters .= " pathfile=" . $this->getConfig()->getPathfile();

	// Affectation dynamique des autres paramètres
        $parameters .= " normal_return_url=" . $this->getConfig()->getNormalReturnUrl();
        $parameters .= " cancel_return_url=" . $this->getConfig()->getCancelReturnUrl();
        $parameters .= " automatic_response_url=" . $this->getConfig()->getAutomaticResponseUrl();
        $parameters .= " language=" . $this->getConfig()->getLanguageCode();
        $parameters .= " payment_means=" . $this->getPaymentMeans();

        if ($this->_getCaptureDay() > 0)
            $parameters .= " capture_day=" . $this->_getCaptureDay();

        $parameters .= " capture_mode=" . $this->_getCaptureMode();
        $parameters .= " customer_id=" . $this->_getCustomerId();
        $parameters .= " customer_email=" . $this->_getCustomerEmail();
        $parameters .= " customer_ip_address=" . $this->_getCustomerIpAddress();
        $parameters .= " data=" . str_replace(',', '\;', $this->getConfig()->getSelectedDataFieldKeys());
        $parameters .= " order_id=" . $this->_getOrderId();

        // Initialisation du chemin de l'executable request
	$binPath = $this->getConfig()->getBinRequest();

        $sips = $this->getApiRequest()->doRequest($parameters, $binPath);

        if (($sips['code'] == "") && ($sips['error'] == "")) {
            $this->_error = true;
            $this->_message = Mage::helper('atos')->__('<br /><center>Call request file error</center><br />Executable file request not found (%s)', $binPath);
        } elseif ($sips['code'] != 0) {
            $this->_error = true;
            $this->_message = Mage::helper('atos')->__('<br /><center>Call payment API error</center><br />Error message: %s', $sips['error']);
        } else {
            // Active debug
            $this->_message = $sips['error'] . '<br />';
            $this->_response = $sips['message'];
        }
    }

    /**
     * Get Payment Means
     *
     * @return string
     */
    public function getPaymentMeans() {
        return str_replace(',', ',2,', Mage::getStoreConfig('payment/atos_standard/cctypes')) . ',2';
    }

    /**
     * Get capture day
     *
     * @return int
     */
    protected function _getCaptureDay() {
        return (int) Mage::getStoreConfig('payment/atos_standard/capture_day');
    }

    /**
     * Get capture mode
     *
     * @return string
     */
    protected function _getCaptureMode() {
        return $this->getConfig()->getPaymentAction(Mage::getStoreConfig('payment/atos_standard/payment_action'));
    }

}