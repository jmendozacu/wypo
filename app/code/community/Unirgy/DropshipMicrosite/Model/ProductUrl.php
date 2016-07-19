<?php

class Unirgy_DropshipMicrosite_Model_ProductUrl extends Mage_Catalog_Model_Product_Url
{
    public function getProductUrl($product, $useSid = null)
    {
        Mage::app()->getStore()->useVendorUrl(true);
        $url = parent::getUrl($product, $useSid);
        Mage::app()->getStore()->resetUseVendorUrl();
        return $url;
    }
}