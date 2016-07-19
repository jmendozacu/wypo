<?php

class Unirgy_DropshipMicrosite_Model_Catalog_SearchLayer extends Mage_CatalogSearch_Model_Layer
{
    public function prepareProductCollection($collection)
    {
        parent::prepareProductCollection($collection);

        Mage::helper('umicrosite')->addVendorFilterToProductCollection($collection);

        return $this;
    }
    public $udApplied=false;
    public function apply()
    {
        $this->udApplied=true;
        return parent::apply();
    }
}