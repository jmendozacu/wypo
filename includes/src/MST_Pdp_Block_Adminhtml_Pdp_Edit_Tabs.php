<?php

class MST_Pdp_Block_Adminhtml_pdp_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('pdp_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('pdp')->__('Image Information'));
    }
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label' => Mage::helper('pdp')->__('Image Details'),
            'title' => Mage::helper('pdp')->__('Image Details'),
            'content' => $this->getLayout()->createBlock('pdp/adminhtml_pdp_edit_tab_form')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}