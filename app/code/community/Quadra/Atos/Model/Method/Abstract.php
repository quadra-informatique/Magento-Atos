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
 * @version Release: $Revision: 3.0.3 $
 * @license http://www.opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
abstract class Quadra_Atos_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract {

    protected $_response  = null;
    protected $_message   = null;
    protected $_error     = false;

    protected $_config;
    protected $_order;
    protected $_quote;

    /**
     * First call to the Atos server
     */
    abstract public function callRequest();

    /**
     * Get Payment Means
     *
     * @return string
     */
    abstract protected function _getPaymentMeans();

    /**
     * Get normal return URL
     *
     * @return string
     */
    abstract protected function _getNormalReturnUrl();

    /**
     * Get cancel return URL
     *
     * @return string
     */
    abstract protected function _getCancelReturnUrl();

    /**
     * Get automatic response URL
     *
     * @return string
     */
    abstract protected function _getAutomaticResponseUrl();

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    abstract function getOrderPlaceRedirectUrl();

    /**
     * Instantiate state and set it to state object
     * @param string $paymentAction
     * @param Varien_Object
     */
    public function initialize($paymentAction, $stateObject) {
        switch ($paymentAction) {
            case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE:
            case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE:
                $stateObject->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
                $stateObject->setStatus('pending_payment');
                $stateObject->setIsNotified(false);
                break;
            default:
                break;
        }
    }

    /**
     * Get redirect block type
     *
     * @return string
     */
    public function getRedirectBlockType() {
        return $this->_redirectBlockType;
    }

    /**
     * Get system response
     *
     * @return string
     */
    public function getSystemResponse() {
        return $this->_response;
    }

    /**
     * Get system message
     *
     * @return string
     */
    public function getSystemMessage() {
        return $this->_message;
    }

    /**
     * Has system error
     *
     * @return boolean
     */
    public function hasSystemError() {
        return $this->_error;
    }

    /**
     * Get config model
     *
     * @return Quadra_Atos_Model_Config
     */
    public function getConfig() {
        if (empty($this->_config)) {
            $config = Mage::getSingleton('atos/config');
            $this->_config = $config->initMethod($this->_code);
        }
        return $this->_config;
    }

    /**
     * Get Atos API Request Model
     *
     * @return Quadra_Atos_Model_Api_Request
     */
    public function getApiRequest() {
        return Mage::getSingleton('atos/api_request');
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote|boolean
     */
    protected function _getQuote() {
        if (empty($this->_quote)) {
            $quoteId = Mage::getSingleton('atos/session')->getQuoteId();
            $this->_quote = Mage::getModel('sales/quote')->load($quoteId);
        }
        return $this->_quote;
    }

    /**
     * Get current order
     *
     * @return Mage_Sales_Model_Order|boolean
     */
    protected function _getOrder() {
        if (empty($this->_order)) {
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
        }

        return $this->_order;
    }

    /**
     * Get order amount
     *
     * @return string
     */
    protected function _getAmount() {
        if ($this->_getOrder())
            $total = $this->_getOrder()->getTotalDue();
        else
            $total = 0;

        return number_format($total, 2, '', '');
    }

    /**
     * Get customer ID
     *
     * @return int
     */
    protected function _getCustomerId() {
        if ($this->_getOrder())
            return (int) $this->_getOrder()->getCustomerId();
        else
            return 0;
    }

    /**
     * Get customer e-mail
     *
     * @return string
     */
    protected function _getCustomerEmail() {
        if ($this->_getOrder())
            return $this->_getOrder()->getCustomerEmail();
        else
            return 'undefined';
    }

    /**
     * Get customer IP address
     *
     * @return string
     */
    protected function _getCustomerIpAddress() {
        return $this->_getQuote()->getRemoteIp();
    }

    /**
     * Get order inrement id
     *
     * @return string
     */
    protected function _getOrderId() {
        return $this->_getOrder()->getIncrementId();
    }

    /**
     * Get binary request file path
     *
     * @return string
     */
    protected function _getBinRequest() {
        return Mage::getStoreConfig('atos_api/config_bin_files/request_path');
    }

    protected function _debug($data, $title = '') {
        if (strlen($title))
            Mage::log($title, Zend_Log::DEBUG, 'payment_' . $this->getCode() . '.log', true);
        Mage::log($data, Zend_Log::DEBUG, 'payment_' . $this->getCode() . '.log', true);
    }

    public function debugRequest($data) {
        $this->_debug($data, "Request data:");
    }

    public function debugResponse($data, $from = '') {
        ksort($data);
        $this->_debug($data, "{$from} response data:");
    }

}
