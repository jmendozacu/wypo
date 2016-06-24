<?php

class MST_Pdp_Block_Adminhtml_Fonts_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('pdp_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('pdp')->__('Font Information'));
    }
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label' => Mage::helper('pdp')->__('Font Details'),
            'title' => Mage::helper('pdp')->__('Font Details'),
            'content' => $this->getLayout()->createBlock('pdp/adminhtml_fonts_edit_tab_form')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}