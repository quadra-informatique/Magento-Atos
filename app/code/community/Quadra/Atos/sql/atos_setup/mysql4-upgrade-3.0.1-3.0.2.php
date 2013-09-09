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
$installer = $this;
$installer->startSetup();

$installer->run("INSERT IGNORE INTO `{$installer->getTable('sales/order_status')}` (`status`, `label`) VALUES ('payment_accepted', 'Payment accepted by Sips')");
$installer->run("INSERT IGNORE INTO `{$installer->getTable('sales/order_status')}` (`status`, `label`) VALUES ('payment_refused', 'Payment refused by Sips')");

$installer->run("INSERT IGNORE INTO `{$installer->getTable('sales/order_status_state')}` (`status`, `state`, `is_default`) VALUES ('payment_accepted', 'processing', 0)");
$installer->run("INSERT IGNORE INTO `{$installer->getTable('sales/order_status_state')}` (`status`, `state`, `is_default`) VALUES ('payment_refused', 'payment_review', 0)");

$installer->endSetup();