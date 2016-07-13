<?php
class MST_Pdp_Block_Adminhtml_Importcategories_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('importcolor_form', array('legend' => Mage::helper('pdp')->__('Import Form')));
       $fieldset->addField('file_csv', 'file', array(
            'label' => Mage::helper('pdp')->__('File'),
            'class' => 'required-entry',
            'required' => false,
            'name' => 'file_csv',
        ));
        return parent::_prepareForm();

    }

}