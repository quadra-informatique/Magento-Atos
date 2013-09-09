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
class Quadra_Atos_Model_Session extends Mage_Core_Model_Session_Abstract {

    protected $_quoteId;
    protected $_response;
    protected $_redirectMessage;
    protected $_redirectTitle;

    /**
     * Class constructor. Initialize Atos/Sips Standard session namespace
     */
    public function __construct() {
        $this->init('atos');
    }

    /**
     * Unset all data associated with object
     */
    public function unsetAll() {
        parent::unsetAll();
        $this->_quoteId = null;
        $this->_response = null;
        $this->_redirectMessage = null;
        $this->_redirectTitle = null;
    }

    protected function _getQuoteIdKey() {
        return 'quote_id_' . Mage::app()->getStore()->getWebsiteId();
    }

    public function setQuoteId($quoteId) {
        $this->setData($this->_getQuoteIdKey(), $quoteId);
    }

    public function getQuoteId() {
        return $this->getData($this->_getQuoteIdKey());
    }

    protected function _getResponseKey() {
        return 'response_' . Mage::app()->getStore()->getWebsiteId();
    }

    public function setResponse($response) {
        $this->setData($this->_getResponseKey(), $response);
    }

    public function getResponse() {
        return $this->getData($this->_getResponseKey());
    }

    protected function _getRedirectMessageKey() {
        return 'redirect_message_' . Mage::app()->getStore()->getWebsiteId();
    }

    public function setRedirectMessage($message) {
        $this->setData($this->_getRedirectMessageKey(), $message);
    }

    public function getRedirectMessage() {
        return $this->getData($this->_getRedirectMessageKey());
    }

    protected function _getRedirectTitleKey() {
        return 'redirect_title_' . Mage::app()->getStore()->getWebsiteId();
    }

    public function setRedirectTitle($title) {
        $this->setData($this->_getRedirectTitleKey(), $title);
    }

    public function getRedirectTitle() {
        return $this->getData($this->_getRedirectTitleKey());
    }

}
