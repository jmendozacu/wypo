<?php
class MST_Pdp_Block_Adminhtml_Fonts_Renderer_Preview extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if($row->getName()==""){
            return '<p>Sample Text</p>';
        }
        else{
            return '<p class="'.$row->getName().'">Sample Text</p>';
        }
    }
}

?>