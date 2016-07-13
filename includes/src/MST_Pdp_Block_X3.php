<?php
class MST_Pdp_Block_X3 extends MST_Pdp_Block_Pdp {
   protected $_templateLimitItem = 12;
   public function getX3JsUrl() {
        $x3JsBase = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS) . "pdp/x3/";
        return $x3JsBase;
    }
    protected function getCurrentDesignJson() {
        $viewMode = $this->getViewMode();
        $productId = $this->getCurrentProductId();
        $jsonString = $this->getJsonContentFromParam();
        if ($jsonString == "") {
            if ($viewMode == "backend") {
                $jsonString = $this->_helper->getAdminTemplates($productId);
            } else {
                if($this->getShareId() != null) {
                    $jsonString = $this->_helper->getShareJsonString($this->_shareId);
                } else {
                    $jsonString = $this->_helper->getAdminTemplates($productId);
                }
            }	
        }
        return $jsonString;
    }
    protected function getSidesConfig() {
        return $this->_helper->getSidesConfig($this->getCurrentProductId());
    }
    protected function getAllImageCategories() {
        $categories = Mage::getModel("pdp/artworkcate")->getArtworkCateCollection();
        $categoryArr = array();
        if($categories->count()) {
            foreach($categories as $_category) {
                $categoryArr[] = $_category->getData();
            }  
        }
        if(!empty($categoryArr)) {
            return json_encode($categoryArr);   
        }
        return '';
    }
    protected function getImageBackgroundCategories() {
        $list = Mage::getModel("pdp/artworkcate")->getImageBackgroundCategories();
        if($list->count()) {
            return $list;   
        }
        return array();
    }
    protected function getProductIncludeColors() {
        $productId = $this->getCurrentProductId();
        $productConfigs = Mage::helper("pdp")->getProductConfig($productId);
        //Filter color if admin selected
        if($productConfigs['selected_color'] != "") {
            $selectedColors = json_decode($productConfigs['selected_color'], true);
            $_includeColors = array();
            foreach($selectedColors as $colorId => $position) {
                $_includeColors[] = $colorId;
            }
            return $_includeColors;
        }
        return false;
    }
    protected function getProductIncludeFonts() {
        $productId = $this->getCurrentProductId();
        $productConfigs = Mage::helper("pdp")->getProductConfig($productId);
        //Filter font if admin selected
        if($productConfigs['selected_font'] != "") {
            $selectedFonts = json_decode($productConfigs['selected_font'], true);
            $_includeFonts = array();
            foreach($selectedFonts as $fontId => $position) {
                $_includeFonts[] = $fontId;
            }
            return $_includeFonts;
        }
        return false;
    }
    protected function isShowTemplateTab() {
        $templates = $productTemplates = Mage::getModel('pdp/admintemplate')->getProductTemplates($this->getCurrentProductId());
        if($templates->count() >= 1) {
            return true;
        } else {
            //if has 1 template only and don't set to default design, then show template tab
            /*$defaultTemplateData = Mage::getModel("pdp/admintemplate")->getDefaultDesignData($this->getCurrentProductId());
            if(empty($defaultTemplateData) && $templates->count() == 1) {
                return true;
            }*/
        }
        return false;
    }
    public function pagingTemplateCollection($current_page, $productId) {
        $_LIMIT = $this->_templateLimitItem;
		$collection = Mage::getModel('pdp/admintemplate')->getProductTemplates($productId);
		$collection_counter = Mage::getModel('pdp/admintemplate')->getProductTemplates($productId);
		$size = ceil(count($collection_counter) / $_LIMIT);
		if ($current_page <= $size) {
			$collection->setCurPage($current_page);
			$collection->setPageSize($_LIMIT);
			return $collection;
		}
	}
}