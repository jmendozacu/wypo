<?php

class Unirgy_DropshipVendorProduct_Model_Cookie extends Mage_Core_Model_Cookie
{
    public function isSecure()
    {
        if ($this->getStore()->isAdmin() || $this->_checkSecureFromRequest) {
            return $this->_getRequest()->isSecure();
        }
        return false;
    }
    protected $_checkSecureFromRequest = false;
    public function checkSecureFromRequest($flag)
    {
        $this->_checkSecureFromRequest = $flag;
        return $this;
    }
}