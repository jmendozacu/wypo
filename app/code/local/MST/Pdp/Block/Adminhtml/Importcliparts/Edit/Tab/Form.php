<?php
class MST_Pdp_Block_Adminhtml_Importcliparts_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('importcolor_form', array('legend' => Mage::helper('pdp')->__('Import Form')));
       $fieldset->addField('file_csv', 'file', array(
            'label' => Mage::helper('pdp')->__('CSV File'),
            'class' => 'required-entry',
            'required' => false,
            'name' => 'file_csv',
        ));
           /* $fieldset->addField('clear_data', 'select', array(
            'label' => Mage::helper('pdp')->__('Clear Data'),
            'name' => 'clear_data',
            'values' => array(
				array(
                    'value' => 2,
                    'label' => Mage::helper('pdp')->__('No'),
                ),
                array(
                    'value' => 1,
                    'label' => Mage::helper('pdp')->__('Yes'),
                )
            ),
        )); */
        $afterElementHtml = '<p class="nm"><strong>NOTE</strong>:<i>' . 'Please upload all images to this folder: <strong>media/pdp/images/artworks/*</strong> before submit this form' . '</i></p>';
        $fieldset->addField('field_name', 'note', array(
            'after_element_html' => $afterElementHtml,
        ));
        return parent::_prepareForm();

    }

}