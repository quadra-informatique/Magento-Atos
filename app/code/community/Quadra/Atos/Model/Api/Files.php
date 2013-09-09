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
class Quadra_Atos_Model_Api_Files {

    public function generatePathfileFile($merchantId, $fileName, $directoryPath, $certificateType = '') {
        $content = '#########################################################################' . "\n";
        $content .= '#' . "\n";
        $content .= '#	Pathfile' . "\n";
        $content .= '#' . "\n";
        $content .= '#	Liste des fichiers paramètres utilisés par le module de paiement' . "\n";
        $content .= '#' . "\n";
        $content .= '#########################################################################' . "\n";
        $content .= '#' . "\n";
        $content .= '#' . "\n";
        $content .= '#-------------------------------------------------------------------------' . "\n";
        $content .= '# Activation (YES) / Désactivation (NO) du mode DEBUG' . "\n";
        $content .= '#-------------------------------------------------------------------------' . "\n";
        $content .= '#' . "\n";
        $content .= 'DEBUG!NO!' . "\n";
        $content .= '#' . "\n";
        $content .= '# ------------------------------------------------------------------------' . "\n";
        $content .= '# Chemin vers le répertoire des logos depuis le web alias  ' . "\n";
        $content .= '# Exemple pour le répertoire www.merchant.com/atos/payment/logo/' . "\n";
        $content .= '# indiquer:' . "\n";
        $content .= '# ------------------------------------------------------------------------' . "\n";
        $content .= '#' . "\n";
        $content .= 'D_LOGO!' . Mage::getBaseUrl('media') . 'atos/logo/!' . "\n";
        $content .= '#' . "\n";
        $content .= '# --------------------------------------------------------------------------' . "\n";
        $content .= '#  Fichiers paramètres liés à l\'api sips paiement	' . "\n";
        $content .= '# --------------------------------------------------------------------------' . "\n";
        $content .= '#' . "\n";
        $content .= 'D_PARAM!' . $directoryPath . '!' . "\n";
        $content .= '#' . "\n";
        $content .= '# Fichier des paramètres sips' . "\n";
        $content .= '#' . "\n";
        $content .= 'F_DEFAULT!D_PARAM!parmcom.' . $merchantId . '!' . "\n";
        $content .= '#' . "\n";
        $content .= '# Fichier paramètre commercant' . "\n";
        $content .= '#' . "\n";
        $content .= 'F_PARAM!D_PARAM!parmcom!' . "\n";
        $content .= '#' . "\n";
        $content .= '# Certificat du commercant' . "\n";
        $content .= '#' . "\n";
        $content .= 'F_CERTIFICATE!D_PARAM!certif!' . "\n";
        $content .= '#' . "\n";

        if ($certificateType != '' && $certificateType != $merchantId) {
            $content .= '# Type du certificat' . "\n";
            $content .= '#' . "\n";
            $content .= 'F_CTYPE!' . $certificateType . '!' . "\n";
            $content .= '#' . "\n";
        }

        $content .= '# --------------------------------------------------------------------------' . "\n";
        $content .= '# End of file' . "\n";
        $content .= '# --------------------------------------------------------------------------' . "\n";

        if (($fp = fopen($directoryPath . $fileName, 'w'))) {
            fputs($fp, $content);
            fclose($fp);
        }
    }

    public function generateParmcomFile($fileName, $directoryPath, $data) {
        $content = '###############################################################################' . "\n";
        $content .= '#' . "\n";
        $content .= '# Fichier des paramètres du commercant' . "\n";
        $content .= '#' . "\n";
        $content .= '# Remarque : Ce fichier paramètre est sous la responsabilité du commercant' . "\n";
        $content .= '#' . "\n";
        $content .= '###############################################################################' . "\n\n";
        $content .= '# URL de retour automatique de la reponse du paiement' . "\n";
        $content .= 'AUTO_RESPONSE_URL!' . $data['auto_response_url'] . '!' . "\n\n";
        $content .= '# URL de traitement d\'un paiement refusé' . "\n";
        $content .= 'CANCEL_URL!' . $data['cancel_url'] . '!' . "\n\n";
        $content .= '# URL de retour suite à un paiement accepté' . "\n";
        $content .= 'RETURN_URL!' . $data['return_url'] . '!' . "\n\n";
        $content .= '# Logo central' . "\n";
        $content .= 'ADVERT!!' . "\n\n";
        $content .= '# Nom du fichier en fond d\'écran des pages de paiement' . "\n";
        $content .= 'BACKGROUND!!' . "\n\n";
        $content .= '# Logo d\'annulation (affichage d\'un bouton si non renseigné)' . "\n";
        $content .= 'CANCEL_LOGO!!' . "\n\n";
        $content .= '# Liste des cartes acceptées par le commercant' . "\n";
        $content .= 'CARD_LIST!' . $data['card_list'] . '!' . "\n\n";
        $content .= '# Code devise (978=EURO)' . "\n";
        $content .= 'CURRENCY!' . $data['currency'] . '!' . "\n\n";
        $content .= '# Code langage de l\'acheteur (fr=francais)' . "\n";
        $content .= 'LANGUAGE!' . $data['language'] . '!' . "\n\n";
        $content .= '# Le logo du commercant (s\'affiche en haut à gauche de la page de paiement)' . "\n";
        $content .= 'LOGO!!' . "\n\n";
        $content .= '# Le logo du commercant (s\'affiche en haut à droite de la page de paiement)' . "\n";
        $content .= 'LOGO2!!' . "\n\n";
        $content .= '# Code pays du commercant' . "\n";
        $content .= 'MERCHANT_COUNTRY!' . $data['merchant_country'] . '!' . "\n\n";
        $content .= '# Code langage du commercant' . "\n";
        $content .= 'MERCHANT_LANGUAGE!' . $data['merchant_language'] . '!' . "\n\n";
        $content .= '# Liste des moyens de paiement acceptés' . "\n";
        $content .= 'PAYMENT_MEANS!' . $data['payment_means'] . '!' . "\n\n";
        $content .= '# Logo de retour a la boutique apres le paiement (bouton si non renseigné)' . "\n";
        $content .= 'RETURN_LOGO!!' . "\n\n";
        $content .= '# Logo de validation du paiement (affichage d\'un bouton si non renseigné)' . "\n";
        $content .= 'SUBMIT_LOGO!!' . "\n\n";
        $content .= '# Fichier template' . "\n";
        $content .= 'TEMPLATE!!' . "\n\n";
        $content .= '# END OF FILE' . "\n";

        if (($fp = fopen($directoryPath . $fileName, 'w'))) {
            fputs($fp, $content);
            fclose($fp);
        }
    }

}
