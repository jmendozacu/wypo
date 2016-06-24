<?php

class MST_Pdp_Block_Adminhtml_Fonts_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('pdp_clipart_form', array('legend' => Mage::helper('pdp')->__('Clipart Information')));
        $fieldset->addField('display_text', 'text', array(
            'label' => Mage::helper('pdp')->__('Display Text'),
            'name' => 'display_text',
            'note' => 'Leave empty to use font name',
        ));
		$fieldset->addField('filename', 'file', array(
		  'name' => 'filename',
		  'label' => Mage::helper('pdp')->__('Upload Font File'),
		  'required' => false,
            'note' => 'Supported fonts : .ttf, .otf, .woff'
        ));
		$fieldset->addField('file_name_old', 'hidden', array(
			'name' => 'file_name_old',
		));
        if (Mage::getSingleton('adminhtml/session')->getFontsData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFontsData());
            Mage::getSingleton('adminhtml/session')->setFontsData(null);
        } elseif (Mage::registry('fonts_data')) {
			$data = Mage::registry('fonts_data');
			$data['filename'] = $data['name'].'.'.$data['ext'];
			$data['file_name_old'] = $data['name'].'.'.$data['ext'];
            $form->setValues($data);
        }
        return parent::_prepareForm();
    }
}