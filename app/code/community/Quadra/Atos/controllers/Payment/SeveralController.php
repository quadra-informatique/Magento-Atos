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
require_once('Quadra/Atos/controllers/PaymentController.php');

class Quadra_Atos_Payment_SeveralController extends Quadra_Atos_PaymentController {

    /**
     * Get current Atos Method Instance
     *
     * @return Quadra_Atos_Model_Method_Several
     */
    public function getMethodInstance() {
        return Mage::getSingleton('atos/method_several');
    }

}