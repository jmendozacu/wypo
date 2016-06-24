<?php

class MST_Pdp_Block_Adminhtml_Artworkcate_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('artwork_category_form', array('legend' => Mage::helper('pdp')->__('Artwork Category Information')));
        $fieldset->addField('title', 'text', array(
            'label' => Mage::helper('pdp')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'title',
        ));
        /**
		$fieldset->addField('thumbnail', 'image', array(
		  'name' => 'thumbnail',
		  'label' => Mage::helper('pdp')->__('Thumbnail'),
		   'required'  => true,
        ));**/
		/* $fieldset->addField('parent_id', 'note', array(
            'label' => Mage::helper('pdp')->__('Parent Id'),
            'name' => 'parent_id',
            'text' => $this->getLayout()->createBlock('pdp/adminhtml_artworkcate')->setTemplate('pdp/artworkcate/artworkcate_categories.phtml')->toHtml(),
        )); */
		$imageTypes = Mage::getModel('pdp/pdp')->getImagesTypes();
		$fieldset->addField('image_types', 'select', array(
          'label'     => Mage::helper('pdp')->__('Image Types'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'image_types',
          'values' => $imageTypes,
        ));
		$fieldset->addField('parent_id', 'hidden', array(
            'label' => Mage::helper('pdp')->__('Parent Id'),
            'name' => 'parent_id',
        ));
		$fieldset->addField('position', 'text', array(
            'label' => Mage::helper('pdp')->__('Position'),
            'required' => false,
            'name' => 'position',
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
        if (Mage::getSingleton('adminhtml/session')->getArtworkcateData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getArtworkcateData());
            Mage::getSingleton('adminhtml/session')->setArtworkcateData(null);
        } elseif (Mage::registry('artworkcate_data')) {
			$arWorkData = Mage::registry('artworkcate_data')->getData();
			if(isset($arWorkData['thumbnail']))
			{
				$arWorkData['thumbnail'] = 'pdp/images/artworks_cat/'.$arWorkData['thumbnail'];
			}
            $form->setValues($arWorkData);
        }
        return parent::_prepareForm();
    }
}