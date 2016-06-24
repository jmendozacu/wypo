<?php
class MST_Pdp_Model_Customerdesign extends Mage_Core_Model_Abstract {
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'pdp/customerdesign' );
	}
	public function saveTemplate($data) {
        //Check if customer login
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton("customer/session")->getCustomer();
            $data['customer_id'] = $customer->getId();
            $data['created_time'] = now();
            $model = Mage::getModel('pdp/customerdesign');
            $model->setData($data);
            $model->save();
            return true;
        } else {
            //It might session timeout 
            return 'guest';
        }
		return false;
	}
	public function getCustomerDesign($customerId) {
		
	}
    public function updateDesignDetails($data) {
        if(isset($data['id']) && $data['id'] != "") {
            $data['update_time'] = now();
            $this->load($data['id'])->setData($data)->save();
            return $this->getId();
        }
        return false;
    }
}