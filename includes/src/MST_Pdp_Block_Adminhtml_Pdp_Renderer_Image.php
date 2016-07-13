<?php
class MST_Pdp_Block_Adminhtml_Pdp_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if($row->getFilename()==""){
            return "";
        }
        else{
            return "<img src='".Mage::getBaseUrl("media").'pdp/images/artworks/'.$row->getFilename()."' width='60' height='60'/>";
        }
    }
}

?>