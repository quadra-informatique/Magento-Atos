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
 * @version Release: $Revision: 3.0.2 $
 * @license http://www.opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
class Quadra_Atos_Block_Redirect_Several extends Mage_Core_Block_Abstract {

    protected function _toHtml() {
        $method = Mage::getModel('atos/method_several');
        $method->callRequest();

        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $html .= '<html>';
        $html .= '<head></head>';
        $html .= '<body>';
        $html .= '<div align="center">'.$this->__('Please, select your payment method:').'</div>';

        if ($method->hasSystemError()) {
            // Has error
            $html .= $method->getSystemMessage();
        } else {
            // Active debug in pathile
            $html .= $method->getSystemMessage();
            $html .= $method->getSystemResponse();
        }

        $html .= '</body>';
        $html .= '</html>';

        return $html;
    }

}
