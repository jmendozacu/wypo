<?php
class MST_PDP_Block_Adminhtml_Template_Grid_Renderer_Font extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
	public function _getValue(Varien_Object $row)
    {
        if ($getter = $this->getColumn()->getGetter()) {
            $val = $row->$getter();
        }
        $displayText = $row->getDisplayText();
        if($displayText == "") {
            $displayText = $row->getName();
        }
		$out = '<style type="text/css">';
		$out .= '@font-face {';
			$out .= 'font-family: "'. $row->getName() .'"';
			$fontPath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'pdp/fonts/' . $row->getName() . '.' . $row->getExt();
			$out .= ';src: url("' . $fontPath .'")';
		
		$out .= '}';
		$out .= '</style>'; 
        $out .= '<span style="font-family: '. $row->getName() .'">'. $displayText .'</span>';
        return $out;
    }
}