<?php

class MST_Pdp_Block_Adminhtml_Fonts extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
    {
        $this->_controller = 'adminhtml_fonts';
        $this->_blockGroup = 'pdp';
        $this->_headerText = Mage::helper('pdp')->__('Manage Fonts');
        $this->_addButtonLabel = Mage::helper('pdp')->__('Add New font');
		$this->setTemplate('pdp/font-style.phtml');
        parent::__construct();
    }
	function getFonts()
	{
		$fonts = Mage::getModel('pdp/fonts')->getCollection()
				->setOrder('font_id','DESC');
		return $fonts;
	}
}