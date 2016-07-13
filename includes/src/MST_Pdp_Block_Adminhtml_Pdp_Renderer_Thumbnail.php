<?php
class MST_Pdp_Block_Adminhtml_Pdp_Renderer_Thumbnail extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $filename = $row->getFilename();
        if($row->getThumbnail() != "") {
            $filename = $row->getThumbnail();
        }
        if($filename == ""){
            return "";
        } else {
            return "<img src='".Mage::getBaseUrl("media").'pdp/images/artworks/'.$filename."' width='60' height='60'/>";
        }
    }
}

?>