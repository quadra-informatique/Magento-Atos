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
class Quadra_Atos_Model_Config extends Varien_Object {

    const PAYMENT_ACTION_CAPTURE = 'AUTHOR_CAPTURE';
    const PAYMENT_ACTION_AUTHORIZE = 'VALIDATION';

    const STATUS_ACCEPTED = 'payment_accepted';
    const STATUS_REFUSED = 'payment_refused';

    protected $_method;
    protected $_merchantId;

    public function initMethod($method) {
        if (empty($this->_method)) {
            $this->_method = $method;
        }
        return $this;
    }

    /**
     * Mapper from Atos/Sips Standard payment actions to Magento payment actions
     *
     * @return string|null
     */
    public function getPaymentAction($action) {
        switch ($action) {
            case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE:
                return self::PAYMENT_ACTION_AUTHORIZE;
            case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE :
                return self::PAYMENT_ACTION_CAPTURE;
        }
    }

    /**
     * Payment actions source getter
     *
     * @return array
     */
    public function getPaymentActions() {
        $paymentActions = array(
            Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE => Mage::helper('adminhtml')->__('Author Capture'),
            Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE => Mage::helper('adminhtml')->__('Validation')
        );
        return $paymentActions;
    }

    /**
     * Get certificate
     *
     * @return string
     */
    public function getCertificate() {
        return Mage::getStoreConfig('atos_api/' . $this->_method . '/certificate_path');
    }

    /**
     * Get pathfile path
     *
     * @return string
     */
    public function getPathfile() {
        $fileName = 'pathfile.' . $this->getMerchantId();
        $directoryPath = Mage::getBaseDir('lib') . DS . 'atos' . DS . 'param' . DS;
        $path = $directoryPath . $fileName;

        if (!file_exists($path)) {
            Mage::getSingleton('atos/api_files')->generatePathfileFile(
                $this->getMerchantId(), $fileName, $directoryPath, pathinfo($this->getCertificate(), PATHINFO_EXTENSION)
            );
        }

        if (!file_exists($directoryPath . 'parmcom.' . $this->getMerchantId())) {
            $data = array(
                'auto_response_url' => $this->getAutomaticResponseUrl(),
                'cancel_url' => $this->getCancelReturnUrl(),
                'return_url' => $this->getNormalReturnUrl(),
                'card_list' => implode(',', Mage::getModel('atos/adminhtml_system_config_source_payment_cctype')->getCardValues()),
                'currency' => $this->getCurrencyCode(Mage::app()->getStore()->getCurrentCurrencyCode()),
                'language' => $this->getLanguageCode(),
                'merchant_country' => $this->getMerchantCountry(),
                'merchant_language' => $this->getLanguageCode(),
                'payment_means' => implode(',2,', Mage::getModel('atos/adminhtml_system_config_source_payment_cctype')->getCardValues()) . ',2'
            );

            Mage::getSingleton('atos/api_files')->generateParmcomFile('parmcom.' . $this->getMerchantId(), $directoryPath, $data);
        }

        return $path;
    }

    /**
     * Get merchant ID
     *
     * @return string
     */
    public function getMerchantId() {
        if (empty($this->_merchantId)) {
            $matches = array();
            preg_match("/certif.[a-z]{2}.[0-9]+/", $this->getCertificate(), $matches);
            if (isset($matches[0])) {
                $merchantId = explode('.', $matches[0]);
                if (array_key_exists('2', $merchantId))
                    $this->_merchantId = $merchantId[2];
            }
        }
        return $this->_merchantId;
    }

    /**
     * Get merchant country code
     *
     * @return string
     */
    public function getMerchantCountry() {
        $countries = Mage::getStoreConfig('general/country');
        $currentCountryCode = strtolower($countries['default']);
        $atosConfigCountries = $this->getMerchantCountries();

        if (count($atosConfigCountries) === 1) {
            return strtolower($atosConfigCountries[0]);
        }

        if (array_key_exists($currentCountryCode, $atosConfigCountries)) {
            $code = array_keys($atosConfigCountries);
            $key = array_search($currentCountryCode, $code);

            return strtolower($code[$key]);
        }

        return 'fr';
    }

    /**
     * Get Atos/Sips authorized countries
     *
     * @return array
     */
    public function getMerchantCountries() {
        $countries = array();
        foreach (Mage::getConfig()->getNode('global/payment/atos/merchant_country')->asArray() as $data) {
            $countries[$data['code']] = $data['name'];
        }

        return $countries;
    }

    /**
     * Get currency code
     *
     * @return string|boolean
     */
    public function getCurrencyCode($currentCurrencyCode) {
        $atosConfigCurrencies = $this->getCurrencies();

        if (array_key_exists($currentCurrencyCode, $atosConfigCurrencies))
            return $atosConfigCurrencies[$currentCurrencyCode];
        else
            return false;
    }

    /**
     * Get Atos/Sips authorized currencies
     *
     * @return array
     */
    public function getCurrencies() {
        $currencies = array();
        foreach (Mage::getConfig()->getNode('global/payment/atos/currencies')->asArray() as $data) {
            $currencies[$data['iso']] = $data['code'];
        }

        return $currencies;
    }

    /**
     * Get language code
     *
     * @return string
     */
    public function getLanguageCode() {
        $language = substr(Mage::getStoreConfig('general/locale/code'), 0, 2);
        $atosConfigLanguages = $this->getLanguages();

        if (count($atosConfigLanguages) === 1) {
            return strtolower($atosConfigLanguages[0]);
        }

        if (array_key_exists($language, $atosConfigLanguages)) {
            $code = array_keys($atosConfigLanguages);
            $key = array_search($language, $code);

            return strtolower($code[$key]);
        }

        return 'fr';
    }

    /**
     * Get Atos/Sips authorized languages
     *
     * @return array
     */
    public function getLanguages() {
        $languages = array();
        foreach (Mage::getConfig()->getNode('global/payment/atos/languages')->asArray() as $data) {
            $languages[$data['code']] = $data['name'];
        }

        return $languages;
    }

    /**
     * Get selected data field
     *
     * @return string
     */
    public function getSelectedDataFieldKeys() {
        return str_replace(',', '\;', Mage::getStoreConfig('atos_api/' . $this->_method . '/data_field'));
    }

    /**
     * Get Atos/Sips keywords data field
     *
     * @return array
     */
    public function getDataFieldKeys() {
        $types = array();
        foreach (Mage::getConfig()->getNode('global/payment/atos/data_field')->asArray() as $data) {
            $types[$data['code']] = $data['name'];
        }

        return $types;
    }

    /**
     * Get binary request file path
     *
     * @return string
     */
    public function getBinRequest() {
        return Mage::getStoreConfig('atos_api/config_bin_files/request_path');
    }

    /**
     * Get binary response file path
     *
     * @return string
     */
    public function getBinResponse() {
        return Mage::getStoreConfig('atos_api/config_bin_files/response_path');
    }

    /**
     * Get if must check IP
     *
     * @return int
     */
    public function getCheckByIpAddress() {
        return (int) Mage::getStoreConfig('atos_api/' . $this->_method . '/check_ip_address');
    }

    /**
     * Get authorized IPs
     *
     * @return array
     */
    public function getAuthorizedIps() {
        return explode(',', Mage::getStoreConfig('atos_api/' . $this->_method . '/authorized_ips'));
    }

}
