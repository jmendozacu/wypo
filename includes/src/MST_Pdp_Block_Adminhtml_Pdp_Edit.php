<?php

class MST_Pdp_Block_Adminhtml_Pdp_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'pdp';
        $this->_controller = 'adminhtml_pdp';

        $this->_updateButton('save', 'label', Mage::helper('pdp')->__('Save Image'));
        $this->_updateButton('delete', 'label', Mage::helper('pdp')->__('Delete Image'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
            ), -100);
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('news_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'news_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'news_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
	
	protected function _prepareLayout()
    {
		//if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) { 
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true); 
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true); 
		//} 
		parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        if (Mage::registry('cliparts_data')->getId()) {
            return Mage::helper('pdp')->__("Edit Image '%s'", $this->htmlEscape(Mage::registry('cliparts_data')->getImageName()));
        } else {
            return Mage::helper('pdp')->__('Add Image');
        }
    }
}