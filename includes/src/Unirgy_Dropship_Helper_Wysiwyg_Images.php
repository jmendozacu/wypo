<?php

class Unirgy_Dropship_Helper_Wysiwyg_Images extends Mage_Cms_Helper_Wysiwyg_Images
{
    public function getStorageRoot()
    {
        $udSess = Mage::getSingleton('udropship/session');
        $io = new Varien_Io_File();
        $storageRoot = Mage::getConfig()->getOptions()->getMediaDir();
        if (realpath($storageRoot)) {
            $storageRoot = realpath($storageRoot);
        }
        $parts = array(
            Mage_Cms_Model_Wysiwyg_Config::IMAGE_DIRECTORY,
            'udvendor-'.$udSess->getVendorId()
        );
        foreach ($parts as $part) {
            $storageRoot .= DS . $part;
            $io->mkdir($storageRoot);
        }
        return $storageRoot;
    }
    public function isUsingStaticUrlsAllowed()
    {
        return true;
    }
}