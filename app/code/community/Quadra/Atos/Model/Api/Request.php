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
class Quadra_Atos_Model_Api_Request {

    public function doRequest($parameters, $binPath) {
        $sips_result = shell_exec("$binPath $parameters");

        //On separe les differents champs et on les met dans une variable tableau
        $sips_values = explode('!', $sips_result);

        // Récupération des paramètres
        $sips = array(
            'code' => $sips_values[1],
            'error' => $sips_values[2],
            'message' => $sips_values[3],
            'command' => "$binPath $parameters",
            'output' => $sips_result
        );

        if (!isset($sips['code'])) {
            Mage::logException(new Exception($sips_result));
        }

        if ($sips['code'] == '-1') {
            Mage::logException(new Exception($sips['error']));
        }

        return $sips;
    }

}
