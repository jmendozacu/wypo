<?php
class MST_Pdp_TemplateController extends Mage_Core_Controller_Front_Action {
	public function getDesignTemplateAction() {
		$current_page = $_POST['current_page'];
		$productId = $_POST['product_id'];
        if(!$productId) {
            return false;
        }
		$pdpObject = new MST_Pdp_Block_X3();
		$collection = $pdpObject->pagingTemplateCollection($current_page, $productId);
		if ( count($collection) > 0) {
			$data = array();
			foreach ($collection as $template) {
				$data[] = $template->getData();
			}
			$this->getResponse()->setBody(json_encode($data));
		} else {
			$this->getResponse()->setBody("nomore");
		}
	}	
    public function getDesignContentAction() {
        $params = $this->getRequest()->getPost();
        if(isset($params['json_filename']) && $params['json_filename']) {
            $designContent = Mage::helper("pdp")->getPDPJsonContent($params['json_filename']);
            if($designContent) {
                echo $designContent;
            }
        }
    }
}