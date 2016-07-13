<?php

class MST_Pdp_Block_Adminhtml_Artworkcate extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_artworkcate';
        $this->_blockGroup = 'pdp';
        $this->_headerText = Mage::helper('pdp')->__('Manage Categories');
        $this->_addButtonLabel = Mage::helper('pdp')->__('Add New Category');
        parent::__construct();
		$this->_addButton('importcliparts', array(
			'label'     => $this->__('Import Categories'),
			'onclick'   => 'setLocation(\'' . $this->getUrl('*/adminhtml_importcategories/edit').'\')',
			'class'     => 'importcliparts',
		));
    }
	/**
	* get artWork categoriers tre
	* @params int $parentId = 0
	**/
	function getArtWorkCategoriyTres($parenId = 0,$prefix = '')
	{
		$id = $this->getRequest()->getParam('id',0);
		$selectedId = 0;
		if($id > 0)
		{
			$artworkcate = Mage::getModel('pdp/artworkcate')->load($id);
			if($artworkcate->getId())
			{
				$selectedId = $artworkcate->getParentId();
			}
		}
		$categoriers = Mage::getModel('pdp/artworkcate')->getCollection()
				->addFieldToFilter('parent_id',$parenId);
		if(count($categoriers))
		{
			$prefix .= '--';
			foreach($categoriers as $category)
			{
				$selected ='';
				if($selectedId == $category->getId())
				{
					$selected = ' selected="selected"';
				}
				$text = $prefix.$category->getTitle();
				echo '<option value="'.$category->getId().'" '.$selected.'>'.$text.'</option>';
				$this->getArtWorkCategoriyTres($category->getId(),$prefix);
			}
		}
		$prefix = '--';
		
	}
}