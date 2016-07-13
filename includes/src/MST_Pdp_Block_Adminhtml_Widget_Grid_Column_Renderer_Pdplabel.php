<?php
class MST_Pdp_Block_Adminhtml_Widget_Grid_Column_Renderer_Pdplabel extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $formatLink = '';
        $id = trim($row->getData('entity_id'));
        $collection = Mage::getModel('pdp/productstatus')->getCollection();
        $collection->addFieldToFilter( 'product_id', $id );
        $dt = $collection->getFirstItem();
        if($dt['id']){
            if($dt['status'] == 1){
                $formatLink = '<span class="pdc-badge on">On</span>';
            }else{
                $formatLink = '<span class="pdc-badge off">Off</span>';
            }
        }else{
            $formatLink = '';
        }
        return $formatLink;
    }
}