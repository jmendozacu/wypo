<?php
class MST_Pdp_Block_Adminhtml_Artworkcate_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if($row->getThumbnail()==""){
            return "";
        }
        else{
            //you can also use some resizer here...
            return "<img src='".Mage::getBaseUrl("media").'pdp/images/artworks_cat/'.$row->getThumbnail()."' width='60' height='60'/>";
        }
    }
}

?>