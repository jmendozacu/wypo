<?php
class MST_Pdp_Block_Adminhtml_Importcliparts_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('importcolor_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('pdp')->__('Import Images'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_section_import', array(
            'label' => Mage::helper('pdp')->__('Import Images'),
            'title' => Mage::helper('pdp')->__('Import Images'),
            'content' => $this->getLayout()->createBlock('pdp/adminhtml_importcliparts_edit_tab_form')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }

}