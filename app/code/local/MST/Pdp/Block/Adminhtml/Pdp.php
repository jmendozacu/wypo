<?php

class MST_Pdp_Block_Adminhtml_Pdp extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	protected $_default_page_size = 20;
    /* public function __construct()
    {
		
        $this->_controller = 'adminhtml_pdp';
        $this->_blockGroup = 'pdp';
        $_helper = Mage::helper('pdp');
        /*
		$this->_headerText = Mage::helper('pdp')->__('');
        $this->_addButtonLabel = Mage::helper('pdp')->__('Add Design');
        $this->_addButton('mst_reset', array( 'label' => Mage::helper('adminhtml')->__('Add New Design'), 'class' => 'reset scalable', 'id'=>'reset_menu', 'onclick'=>"location.reload()" ));
        $this->_addButton('save', array( 'label' => Mage::helper('adminhtml')->__('Save Item'), 'class' => 'save scalable', 'id'=>'save_menu', 'onclick'=>"editForm.submit();" ));
       
		parent::__construct(); 
    } */
	public function __construct()
    {
        $this->_controller = 'adminhtml_pdp';
        $this->_blockGroup = 'pdp';
        $this->_headerText = Mage::helper('pdp')->__('Manage Images');
        $this->_addButtonLabel = Mage::helper('pdp')->__('Add New Image');
        parent::__construct();
		 $this->_addButton('importcliparts', array(
			'label'     => $this->__('Import Images'),
			'onclick'   => 'setLocation(\'' . $this->getUrl('*/adminhtml_importcliparts/edit').'\')',
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
			//get data from clipart
			$image = Mage::getModel('pdp/images')->load($id);
			if($image->getId())
			{
				$selectedId = $image->getCategory();
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
	public function getImageCollectionPaging($current_page, $page_size, $url, $category){
		$collection = Mage::getModel('pdp/pdp')->getImageCollectionByCategory($category);
		$collection_counter = Mage::getModel('pdp/pdp')->getImageCollectionByCategory($category);
		$total = count($collection_counter);
		$viewPerPage = Mage::helper('pdp')->getViewPerPage();
		return Mage::helper('pdp')->pagingCollection($current_page, $page_size, $viewPerPage, $collection, $total, $url, $category);
	}
}