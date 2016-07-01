<?php


#phpinfo();
#die();

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

require_once 'app/Mage.php';

Mage::app();

Mage::setIsDeveloperMode(true);

Mage::dispatchEvent('udropship_init_config_rewrites', array());

Mage::helper('udropship/protected')->arrayCompare(array(),array());
Unirgy_Dropship_Helper_Protected::validateLicense('Unirgy_Dropship');
#Unirgy_Dropship_Helper_Protected::validateLicense('Unirgy_DropshipPo');
/*
Mage::helper('udropship/protected');
Mage::helper('udpo/protected');
Mage::helper('udbatch/protected');
Mage::helper('udmulti/protected');
Mage::helper('udmultiprice/protected');
Mage::helper('udmspro/protected');
Mage::helper('umicrosite/protected');
Mage::helper('udprod/protected');
*/

//die(Mage::getStoreConfig('udropship/vendor/master_password') . ' - OK');
die(Mage::getConfig()->getNode()->asNiceXml());
die('OK');


