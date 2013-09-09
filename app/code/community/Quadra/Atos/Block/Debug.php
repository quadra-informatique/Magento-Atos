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
class Quadra_Atos_Block_Debug extends Mage_Core_Block_Abstract {

    protected function _toHtml() {
        $response = $this->getObject();

        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $html .= '<html>';
        $html .= '<head></head>';
        $html .= '<body>';

        $html .= $response['hash']['error'];

        $html .= '<table width="700" cellpadding="3" style="BORDER-RIGHT: #000000 1px solid; BORDER-TOP: #000000 1px solid; FONT-SIZE: 75%;  BORDER-LEFT: #000000 1px solid; BORDER-BOTTOM: #000000 1px solid; font-family: sans-serif; border-collapse: collapse;">';
        $html .= '<tbody><tr style="background-color: #9999cc"><td align="center">';
        $html .= '<b>' . $this->__('Sips Server Response') . '</b>';
        $html .= '</td></tr><tr></tr>';

        foreach ($response['hash'] as $key => $value)
            if ($key != 'error')
                $html .= "<tr><td>$key ($value)</td></tr>";

        $html .= '</tbody></table>';
        $html .= '<center><h3><a href="' . Mage::getUrl($response['redirect_url'], array('_secure' => true)) . '">' . $this->__('Click here to return to %s', Mage::app()->getWebsite()->getName()) . '</a></h3></center><br />';
        $html .= '</body>';
        $html .= '</html>';

        return $html;
    }

}
