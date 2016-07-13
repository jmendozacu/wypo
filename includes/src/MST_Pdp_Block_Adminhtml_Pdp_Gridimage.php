<?php
class MST_Pdp_Block_Adminhtml_Pdp_Gridimage extends Mage_Adminhtml_Block_Widget_Grid
{
    
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'selectImageForProductGrid' );
		$this->setDefaultSort ( 'id' );
		$this->setDefaultDir ( 'ASC' );
		$this->setSaveParametersInSession ( false );
        $this->setUseAjax(true);
        $this->setDefaultFilter(array('in_products' => 1)); // By default we have added a filter for the rows, that in_products value to be 1
	}
	protected function _prepareCollection() {
		$collection = Mage::getModel ('pdp/artworkcate' )->getCollection ();
		$this->setCollection ( $collection );
		return parent::_prepareCollection ();
	}
    protected function _addColumnFilterToCollection($column) {
		// Set custom filter for in product flag
		//$categoryId = $this->getRequest()->getParam('id');
		if ($column->getId() == 'in_products') {
			$ids = $this->_getSelectedImages();
			if (empty($ids)) {
				$ids = 0;
			}
			if ($column->getFilter()->getValue()) {
				$this->getCollection()->addFieldToFilter('id', array('in'=>$ids));
			} else {
				if($ids) {
					$this->getCollection()->addFieldToFilter('id', array('nin'=>$ids));
				}
			}
		} else {
			parent::_addColumnFilterToCollection($column);
		}
		return $this;
	}
	protected function _prepareColumns() {
        $this->addColumn('in_products', array(
			'header'    => Mage::helper('pdp')->__('ID'),
			//'width'     => '50px',
			'index'     => 'id',
            'values'            => $this->_getSelectedImages(),
			'type'  => 'checkbox',
		));
		$this->addColumn ( 'title', array (
				'header' => Mage::helper ( 'pdp' )->__ ( 'Category Title' ),
				'align' => 'left',
				'index' => 'title'
		) );
		$imageTypes =  Mage::getModel('pdp/pdp')->getImagesTypes();
		unset($imageTypes['']);
		$this->addColumn ( 'image_types', array (
				'header' => Mage::helper ( 'pdp' )->__ ( 'Image Types' ),
				'align' => 'left',
				'index' => 'image_types',
				'type' => 'options',
				'options'=> $imageTypes
		) );
		
		$this->addColumn ( 'status', array (
				'header' => Mage::helper ( 'pdp' )->__ ( 'Status' ),
				'align' => 'left',
				'width' => '80px',
				'index' => 'status',
				'type' => 'options',
				'options' => array (
						1 => 'Enabled',
						2 => 'Disabled' 
				) 
		) );
        $this->addColumn('position', array(
            'header'            => Mage::helper('pdp')->__('Position'),
            'name'              => 'position',
            'width'             => 60,
            'type'              => 'number',
            'validate_class'    => 'validate-number',
            'index'             => 'position',
            'editable'          => true,
            'edit_only'         => true
        ));
		return parent::_prepareColumns ();
	}
	
    public function getGridUrl() {
        $url = Mage::helper("adminhtml")->getUrl("adminhtml/pdpadmin_pdp/selectimagegrid", array('_current' => true));
        return $url;
    }
    public function _getSelectedImages() {
        $images = array_keys($this->getSelectedImages());
		return $images;
    }
    public function getSelectedImages() {
        $tm_id = $this->getRequest()->getParam('productid');
		if(!isset($tm_id)) {
			$tm_id = 0;
		}
		$pdcProducts = Mage::getModel('pdp/productstatus')->getCollection();
        $pdcProducts->addFieldToFilter('product_id', $tm_id);
		$custIds = array();
		if($pdcProducts->count()) {
			$selectedImage = $pdcProducts->getFirstItem()->getSelectedImage();
            if($selectedImage != null && $selectedImage != "") {
                $custIds = json_decode($selectedImage, true);   
            }
		}
		return $custIds;
    }
}