<?php
class MST_Pdp_Model_Admintemplate extends Mage_Core_Model_Abstract {
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'pdp/admintemplate' );
	}
	public function saveAdminTemplate($data) {
		$model = Mage::getModel('pdp/admintemplate');
		$collection = $this->getCollection();
        if(!$data['template_id'] || !(isset($data['template_id']))) {
            //Add new design template
            $data['template_id'] = NULL;
            $data['created_time'] = now();
            $data['update_time'] = $data['created_time'];
        } else {
            //Edit template
            $data['update_time'] = now();
        }
        $model->setData($data)->setId($data['template_id']);
        $model->save();
		/**$collection->addFieldToFilter('product_id', $data['product_id']);
		if (count($collection) > 0) {
			$tempId = $collection->getFirstItem()->getId();
			$model->setData($data)->setId($tempId)->save();
		} else {
			$model->setData($data);
			$model->save();
		}**/
        return $model;
	}
    protected function _getTemplates($productId) {
        $collection = $this->getCollection();
        $collection->addFieldToFilter("product_id", $productId);
        $collection->setOrder("is_default", "DESC");
        $collection->setOrder("template_position", "ASC");
        $collection->setOrder("id", "DESC");
        return $collection;
    }
    public function getProductTemplates($productId) {
        if($productId) {
            $collection = $this->_getTemplates($productId);
            return $collection;
        }
        return false;
    }
    public function updateTemplateData($data) {
        if(isset($data['id'])) {
            if(isset($data['is_default'])) {
                $this->_resetDefaultTemplate($data['product_id']);
                $data['is_default'] = 1;
            }
            $this->setData($data)->setId($data['id'])->save();
        }
    }
    protected function _resetDefaultTemplate($productId) {
        $collection = $this->getProductTemplates($productId);
        $collection->addFieldToFilter("is_default", 1);
        if($collection->count()) {
            foreach($collection as $template) {
                $template->setIsDefault("0")->save();
            }
        }
        return true;
    }
    //Return a string, json filename
    public function getDefaultDesign($productId) {
        $data = $this->getDefaultDesignData($productId);
        if(!empty($data)) {
            return $data['pdp_design'];
        }
        return "";
    }
    //return an array
    public function getDefaultDesignData($productId) {
        $data = array();
        $collection = $this->_getTemplates($productId);
        $collection->addFieldToFilter("is_default", 1);
        if($collection->count()) {
            $data = $collection->getFirstItem()->getData();
        }
        return $data;
    }
}