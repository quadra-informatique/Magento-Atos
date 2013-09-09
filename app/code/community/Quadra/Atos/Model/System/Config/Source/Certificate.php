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
class Quadra_Atos_Model_System_Config_Source_Certificate {

    protected $_options;

    public function toOptionArray() {
        if (!$this->_options) {
            $this->_options = array();
            $this->_options[] = array('value' => '', 'label' => Mage::helper('adminhtml')->__('-- Please select --'));
            $path = Mage::getBaseDir('base') . DS . 'lib' . DS . 'atos' . DS . 'param';

            if (is_dir($path)) {
                $dir = dir($path);
                while ($file = $dir->read()) {
                    if (preg_match("/^certif/i", $file)) {
                        $this->_options[] = array('value' => $path . DS . $file, 'label' => $file);
                    }
                }

                $dir->close();
            }
            sort($this->_options);
        }
        return $this->_options;
    }

}