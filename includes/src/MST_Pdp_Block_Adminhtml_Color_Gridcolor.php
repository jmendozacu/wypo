<?php
class MST_Pdp_Block_Adminhtml_Color_Gridcolor extends Mage_Adminhtml_Block_Widget_Grid
{
    
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'selectColorGrid' );
		$this->setDefaultSort ( 'color_id' );
		$this->setDefaultDir ( 'ASC' );
		$this->setSaveParametersInSession ( false );
        $this->setUseAjax(true);
        $this->setDefaultFilter(array('in_products' => 1)); // By default we have added a filter for the rows, that in_products value to be 1
	}
	protected function _prepareCollection() {
		$collection = Mage::getModel ('pdp/color' )->getCollection ();
		$this->setCollection ( $collection );
		return parent::_prepareCollection ();
	}
    protected function _addColumnFilterToCollection($column) {
		// Set custom filter for in product flag
		//$categoryId = $this->getRequest()->getParam('id');
		if ($column->getId() == 'in_products') {
			$ids = $this->_getSelectedColors();
			if (empty($ids)) {
				$ids = 0;
			}
			if ($column->getFilter()->getValue()) {
				$this->getCollection()->addFieldToFilter('color_id', array('in'=>$ids));
			} else {
				if($ids) {
					$this->getCollection()->addFieldToFilter('color_id', array('nin'=>$ids));
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
			'width'     => '50px',
			'index'     => 'color_id',
            'values'            => $this->_getSelectedColors(),
			'type'  => 'checkbox',
		));
		$this->addColumn ( 'color_name', array (
				'header' => Mage::helper ( 'pdp' )->__ ( 'Color Name' ),
				'align' => 'left',
				'index' => 'color_name'
		) );
        $this->addColumn ( 'color_code', array (
				'header' => Mage::helper ( 'pdp' )->__ ( 'Color Code' ),
				'align' => 'left',
				'index' => 'color_code'
		) );
        $this->addColumn ( 'color_preview', array (
				'header' => Mage::helper ( 'pdp' )->__ ( 'Color Preview' ),
				'align' => 'center',
				'index' => 'color_code',
				'renderer' => 'pdp/adminhtml_template_grid_renderer_color',
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
        $url = Mage::helper("adminhtml")->getUrl("adminhtml/pdpadmin_color/selectcolorgrid", array('_current' => true));
        return $url;
    }
    public function _getSelectedColors() {
        $images = array_keys($this->getSelectedColors());
		return $images;
    }
    public function getSelectedColors() {
        $tm_id = $this->getRequest()->getParam('productid');
		if(!isset($tm_id)) {
			$tm_id = 0;
		}
		$pdcProducts = Mage::getModel('pdp/productstatus')->getCollection();
        $pdcProducts->addFieldToFilter('product_id', $tm_id);
		$custIds = array();
		if($pdcProducts->count()) {
			$selectedColor = $pdcProducts->getFirstItem()->getSelectedColor();
            if($selectedColor != null && $selectedColor != "") {
                $custIds = json_decode($selectedColor, true);    
            }
		}
		return $custIds;
    }
}