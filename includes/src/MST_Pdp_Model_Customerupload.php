<?php
/**
* Magento Support Team.
* @category   MST
* @package    MST_Pdp
* @version    2.0
* @author     Magebay Developer Team <info@magebay.com>
* @copyright  Copyright (c) 2009-2013 MAGEBAY.COM. (http://www.magebay.com)
*/
class MST_Pdp_Model_Customerupload extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('pdp/customerupload');
    }
    public function saveCustomerUploadImage($data) {
        $customerId = 0;
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerId = Mage::getSingleton("customer/session")->getCustomer()->getId();
            $data['customer_id'] = $customerId;
        }
        $this->setData($data)->save();
    }
    public function getCustomerUploadImage() {
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerId = Mage::getSingleton("customer/session")->getCustomer()->getId();
            $collection = $this->getCollection();
            $collection->addFieldToFilter("customer_id", $customerId);
            return $collection;
        }
        return false;
    }
}