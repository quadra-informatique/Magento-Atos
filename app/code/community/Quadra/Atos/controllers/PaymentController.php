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
class Quadra_Atos_PaymentController extends Mage_Core_Controller_Front_Action {

    /**
     * Get Atos Api Response Model
     *
     * @return Quadra_Atos_Model_Api_Response
     */
    public function getApiResponse() {
        return Mage::getSingleton('atos/api_response');
    }

    /**
     * Get current Atos Payment Method
     *
     * @return Quadra_Atos_Model_Method_Standard|Quadra_Atos_Model_Method_Several|Quadra_Atos_Model_Method_Euro|Quadra_Atos_Model_Method_Aurore
     */
    public function getAtosMethod() {
        $quoteId = (int) $this->getAtosSession()->getQuoteId();
        $quote = Mage::getModel('sales/quote')->load($quoteId);
        $method = $quote->getPayment()->getMethod();

        switch ($method) {
            case 'atos_standard' :
                $atosMethod = Mage::getSingleton('atos/method_standard');
                break;
            case 'atos_several' :
                $atosMethod = Mage::getSingleton('atos/method_several');
                break;
            case 'atos_aurore' :
                $atosMethod = Mage::getSingleton('atos/method_aurore');
                break;
            case 'atos_euro' :
                $atosMethod = Mage::getSingleton('atos/method_euro');
                break;
            default :
                $atosMethod = Mage::getSingleton('atos/method_standard');
        }

        return $atosMethod;
    }

    /**
     * Get Atos/Sips Standard config
     *
     * @return Quadra_Atos_Model_Config
     */
    public function getConfig() {
        return Mage::getSingleton('atos/config');
    }

    /**
     * Get checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession() {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get customer session
     *
     * @return Mage_Customer_Model_Session
     */
    public function getCustomerSession() {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get Atos/Sips Standard session
     *
     * @return Quadra_Atos_Model_Session
     */
    public function getAtosSession() {
        return Mage::getSingleton('atos/session');
    }

    /**
     * When a customer chooses Atos/Sips Standard on Checkout/Payment page
     */
    public function redirectAction() {
        $this->getAtosSession()->setQuoteId($this->getCheckoutSession()->getLastQuoteId());
        $quote = $this->getCheckoutSession()->getQuote();
        $method = $quote->getPayment()->getMethod();
        $this->getResponse()->setBody($this->getLayout()->createBlock($this->getAtosMethod($method)->getRedirectBlockType(), 'atos_redirect')->toHtml());
        $this->getCheckoutSession()->unsQuoteId();
        $this->getCheckoutSession()->unsRedirectUrl();
    }

    /**
     * When a customer cancel payment from Atos/Sips Standard.
     */
    public function cancelAction() {
        if (!array_key_exists('DATA', $_REQUEST)) {
            // Set redirect message
            $this->getAtosSession()->setRedirectMessage($this->__('An error occured: no data received.'));
            // Log error
            $errorMessage = $this->__('Customer #%s returned successfully from Atos/Sips payment platform but no data received for order #%s.', $this->getCustomerSession()->getCustomerId(), $this->getCheckoutSession()->getLastRealOrderId());
            Mage::helper('atos')->logError(get_class($this), __FUNCTION__, $errorMessage);
            // Redirect
            $this->_redirect('*/*/failure');
            return;
        }

        // Get Sips Server Response
        $response = $this->_getAtosResponse($_REQUEST['DATA']);
        // Set redirect URL
        $response['redirect_url'] = '*/*/failure';
        // Set redirect message
        $this->getAtosSession()->setRedirectMessage($this->__('Process payment was cancelled, your order was automatically cancelled.'));

        // Cancel order
        if ($response['hash']['order_id']) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($response['hash']['order_id']);
            if ($order->getId()) {
                $order->cancel()
                        ->addStatusToHistory($order->getStatus(), $this->__('Order was canceled by customer'))
                        ->save();
            }
        }

        // Save Atos/Sips response in session
        $this->getAtosSession()->setResponse($response);

        // Debug mode is active
        if ($response['hash']['response_code'] == 0 && !empty($response['hash']['error'])) {
            $this->_redirect('*/*/debug');
            return;
        }

        $this->_redirect($response['redirect_url'], array('_secure' => true));
    }

    /**
     * When customer returns from Atos/Sips payment platform
     */
    public function normalAction() {
        if (!array_key_exists('DATA', $_REQUEST)) {
            // Set redirect message
            $this->getAtosSession()->setRedirectMessage($this->__('An error occured: no data received.'));
            // Log error
            $errorMessage = $this->__('Customer #%s returned successfully from Atos/Sips payment platform but no data received for order #%s.', $this->getCustomerSession()->getCustomerId(), $this->getCheckoutSession()->getLastRealOrderId());
            Mage::helper('atos')->logError(get_class($this), __FUNCTION__, $errorMessage);
            // Redirect
            $this->_redirect('*/*/failure');
            return;
        }

        // Get Sips Server Response
        $response = $this->_getAtosResponse($_REQUEST['DATA']);

        // Check if merchant ID matches
        if ($response['hash']['merchant_id'] != $this->getConfig()->getMerchantId()) {
            // Set redirect message
            $this->getAtosSession()->setRedirectMessage($this->__('An error occured: merchant ID mismatch.'));
            // Log error
            $errorMessage = $this->__('Response Merchant ID (%s) is mismatch with configuration value (%s)', $response['hash']['merchant_id'], $this->getConfig()->getMerchantId());
            Mage::helper('atos')->logError(get_class($this), __FUNCTION__, $errorMessage);
            // Redirect
            $this->_redirect('*/*/failure');
            return;
        }

        // Treat response
        $order = Mage::getModel('sales/order');
        if ($response['hash']['order_id']) {
            $order->loadByIncrementId($response['hash']['order_id']);
        }

        switch ($response['hash']['response_code']) {
            case '00':
                if ($order->getId()) {
                    $order->addStatusToHistory($order->getStatus(), $this->__('Customer returned successfully from Atos/Sips payment platform.'))
                            ->save();
                }
                $this->getCheckoutSession()->getQuote()->setIsActive(false)->save();
                // Set redirect URL
                $response['redirect_url'] = 'checkout/onepage/success';
                break;
            default:
                // Log error
                $errorMessage = $this->__('Error: code %s.<br /> %s', $response['hash']['response_code'], $response['hash']['error']);
                Mage::helper('atos')->logError(get_class($this), __FUNCTION__, $errorMessage);
                // Add error on order message
                if ($order->getId()) {
                    $order->addStatusToHistory($order->getStatus(), $errorMessage)
                            ->save();
                }
                // Set redirect message
                $this->getAtosSession()->setRedirectMessage($this->__('(Response Code %s) Error with payment module', $response['hash']['response_code']));
                // Set redirect URL
                $response['redirect_url'] = '*/*/failure';
                break;
        }

        // Save Atos/Sips response in session
        $this->getAtosSession()->setResponse($response);

        // Debug mode is active
        if ($response['hash']['response_code'] == 0 && !empty($response['hash']['error'])) {
            $this->_redirect('*/*/debug');
            return;
        }

        $this->_redirect($response['redirect_url'], array('_secure' => true));
    }

    /**
     * When Atos/Sips returns
     */
    public function automaticAction() {
        if (!array_key_exists('DATA', $_REQUEST)) {
            // Log error
            $errorMessage = $this->__('Automatic response received but no data received for order #%s.', $this->getCheckoutSession()->getLastRealOrderId());
            Mage::helper('atos')->logError(get_class($this), __FUNCTION__, $errorMessage);
            return;
        }

        // Get Sips Server Response
        $response = $this->_getAtosResponse($_REQUEST['DATA']);

        // Check IP address
        if ($this->getAtosMethod()->getConfig()->getCheckByIpAddress()) {
            $ipAdresses = $response['atos_server_ip_adresses'];
            $authorizedIps = $this->getAtosMethod()->getConfig()->getAuthorizedIps();
            $isIpOk = false;

            foreach ($ipAdresses as $ipAdress) {
                if (in_array(trim($ipAdress), $authorizedIps)) {
                    $isIpOk = true;
                    break;
                }
            }

            if (!$isIpOk) {
                Mage::log(implde(', ', $ipAdresses) . ' tries to connect to our server' . "\n", null, 'atos.log');
                return;
            }
        }

        // Treat response
        $order = Mage::getModel('sales/order');
        if ($response['hash']['order_id']) {
            $order->loadByIncrementId($response['hash']['order_id']);
        }
        switch ($response['hash']['response_code']) {
            // Success order
            case '00':
                if ($order->getId()) {
                    $message = $this->__('Payment accepted by Sips');
                    $message .= ' - ' . Mage::getSingleton('atos/api_response')->describeResponse($response['hash']);
                    // Update state and status order
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, Quadra_Atos_Model_Config::STATUS_ACCEPTED, $message);
                    // Send confirmation email
                    if (!$order->getEmailSent()) {
                        $order->sendNewOrderEmail();
                    }
                    // Save order
                    $order->save();
                }
                break;
            // Rejected payment
            default:
                if ($order->getId()) {
                    $message = $this->__('Payment rejected by Sips');
                    $message .= ' - ' . Mage::getSingleton('atos/api_response')->describeResponse($response['hash']);
                    // Update state and status order
                    $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, Quadra_Atos_Model_Config::STATUS_REFUSED, $message);
                    // Save order
                    $order->save();
                }
                break;
        }
    }

    /**
     * When has error in treatment
     */
    public function failureAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('atos_failure')->setMessage($this->getAtosSession()->getRedirectMessage());
        $this->getAtosSession()->unsetAll();
        $this->renderLayout();
    }

    /**
     * When debug mode is active
     */
    public function debugAction() {
        $this->getResponse()->setBody(
                $this->getLayout()
                        ->createBlock('atos/debug', 'atos_debug')
                        ->setObject($this->getAtosSession()->getResponse())
                        ->toHtml());
    }

    public function saveAuroreDobAction() {
        $dob = Mage::app()->getLocale()->date($this->getRequest()->getParam('dob'), null, null, false)->toString('yyyy-MM-dd');
        try {
            $this->getAtosSession()->setCustomerDob($dob);
            $this->getResponse()->setBody('OK');
        } catch (Exception $e) {
            $this->getResponse()->setBody('KO - ' . $e->getMessage());
        }
    }

    /**
     * Treat Atos/Sips response
     */
    protected function _getAtosResponse($data) {
        $response = $this->getApiResponse()
                ->doResponse($data, array(
            'bin_response' => $this->getConfig()->getBinResponse(),
            'pathfile' => $this->getAtosMethod()->getConfig()->getPathfile()
        ));

        if (!isset($response['hash']['response_code'])) {
            $this->_redirect('*/*/failure');
            return;
        }

        if ($response['hash']['response_code'] == '-1') {
            $this->_redirect('*/*/failure');
            return;
        }

        return $response;
    }

}