<?php
class MST_Pdp_Block_Adminhtml_Importcliparts_Edit extends Mage_Adminhtml_Block_Widget_Form_Container 
{
    public function __construct() {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'pdp';
        $this->_controller = 'adminhtml_importcolor';
        $this->_removeButton('back');
        $this->_removeButton('reset');
        $this->_removeButton('delete');
        $this->_updateButton('save', 'label', Mage::helper('pdp')->__('Import'));
    }

    public function getHeaderText() {
            return Mage::helper('pdp')->__("Import Images");
    }

}