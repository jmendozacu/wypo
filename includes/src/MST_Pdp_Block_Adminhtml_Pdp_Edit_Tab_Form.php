<?php

class MST_Pdp_Block_Adminhtml_Pdp_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('pdp_clipart_form', array('legend' => Mage::helper('pdp')->__('Clipart Information')));
        $fieldset->addField('image_name', 'text', array(
            'label' => Mage::helper('pdp')->__('Label'),
            'name' => 'image_name',
        ));
		$fieldset->addField('filename', 'image', array(
            'label' => Mage::helper('pdp')->__('Image File'),
		    'class' => 'required-entry',
            'required' => true,
            'name' => 'filename',
        ));
		/* $fieldset->addField('category', 'note', array(
            'label' => Mage::helper('pdp')->__('Category'),
            'name' => 'category',
            'text' => $this->getLayout()->createBlock('pdp/adminhtml_pdp')->setTemplate('pdp/artworkcate/artworkcate_categories.phtml')->toHtml(),
        )); */
		$fieldset->addField('category', 'select', array(
          'label'     => Mage::helper('pdp')->__('Category'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'category',
          'values' => Mage::getModel('pdp/pdp')->getCategoriesGroup(),

        ));
		$fieldset->addField('price', 'text', array(
            'label' => Mage::helper('pdp')->__('Price'),
            'required' => false,
            'name' => 'price',
        ));
		$fieldset->addField('position', 'text', array(
            'label' => Mage::helper('pdp')->__('Position'),
            'required' => false,
            'name' => 'position',
        ));
		/* $imageTypes = Mage::getModel('pdp/pdp')->getImagesTypes();
		$fieldset->addField('image_types', 'select', array(
          'label'     => Mage::helper('pdp')->__('Image Types'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'image_types',
          'values' => $imageTypes,
        )); */
		$fieldset->addField('thumbnail', 'image', array(
		  'name' => 'thumbnail',
		  'label' => Mage::helper('pdp')->__('Thumbnail'),
        ));
		$fieldset->addField('image_tag', 'text', array(
            'label' => Mage::helper('pdp')->__('Tags'),
            'required' => false,
            'name' => 'image_tag',
        ));
		$fieldset->addField('sort_description', 'textarea', array(
            'label' => Mage::helper('pdp')->__('Short description'),
            'required' => false,
            'name' => 'sort_description',
        ));
		$fieldset->addField('description', 'textarea', array(
            'label' => Mage::helper('pdp')->__('Description'),
            'required' => false,
            'name' => 'description',
        ));
        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('pdp')->__('Status'),
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('pdp')->__('Enabled'),
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('pdp')->__('Disabled'),
                ),
            ),
        ));
        if (Mage::getSingleton('adminhtml/session')->getClipartsData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getClipartsData());
            Mage::getSingleton('adminhtml/session')->setArtworkcateData(null);
        } elseif (Mage::registry('cliparts_data')) {
			$cliparts = Mage::registry('cliparts_data')->getData();
			if(isset($cliparts['filename']) && $cliparts['filename'] != "")
			{
				$cliparts['filename'] = 'pdp/images/artworks/'.$cliparts['filename'];
			}
			if(isset($cliparts['thumbnail']) && $cliparts['thumbnail'])
			{
				$cliparts['thumbnail'] = 'pdp/images/artworks/'.$cliparts['thumbnail'];
			}
            $form->setValues($cliparts);
        }
        return parent::_prepareForm();
    }
}