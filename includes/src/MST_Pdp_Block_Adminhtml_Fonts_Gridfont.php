<?php
class MST_Pdp_Block_Adminhtml_Fonts_Gridfont extends Mage_Adminhtml_Block_Widget_Grid
{
    
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'selectFontGrid' );
		$this->setDefaultSort ( 'font_id' );
		$this->setDefaultDir ( 'ASC' );
		$this->setSaveParametersInSession ( false );
        $this->setUseAjax(true);
        $this->setDefaultFilter(array('in_products' => 1)); // By default we have added a filter for the rows, that in_products value to be 1
	}
	protected function _prepareCollection() {
		$collection = Mage::getModel ('pdp/fonts' )->getCollection ();
		$this->setCollection ( $collection );
		return parent::_prepareCollection ();
	}
    protected function _addColumnFilterToCollection($column) {
		// Set custom filter for in product flag
		//$categoryId = $this->getRequest()->getParam('id');
		if ($column->getId() == 'in_products') {
			$ids = $this->_getSelectedFonts();
			if (empty($ids)) {
				$ids = 0;
			}
			if ($column->getFilter()->getValue()) {
				$this->getCollection()->addFieldToFilter('font_id', array('in'=>$ids));
			} else {
				if($ids) {
					$this->getCollection()->addFieldToFilter('font_id', array('nin'=>$ids));
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
			'index'     => 'font_id',
            'values'            => $this->_getSelectedFonts(),
			'type'  => 'checkbox',
		));
		$this->addColumn ( 'name', array (
				'header' => Mage::helper ( 'pdp' )->__ ( 'Font Name' ),
				'align' => 'left',
				'index' => 'name'
		) );
        $this->addColumn ( 'font_preview', array (
				'header' => Mage::helper ( 'pdp' )->__ ( 'Font Preview' ),
				'align' => 'left',
				'index' => 'name',
				'renderer' => 'pdp/adminhtml_template_grid_renderer_font',
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
        $url = Mage::helper("adminhtml")->getUrl("adminhtml/pdpadmin_fonts/selectfontgrid", array('_current' => true));
        return $url;
    }
    public function _getSelectedFonts() {
        $images = array_keys($this->getSelectedFonts());
		return $images;
    }
    public function getSelectedFonts() {
        $tm_id = $this->getRequest()->getParam('productid');
		if(!isset($tm_id)) {
			$tm_id = 0;
		}
		$pdcProducts = Mage::getModel('pdp/productstatus')->getCollection();
        $pdcProducts->addFieldToFilter('product_id', $tm_id);
		$custIds = array();
		if($pdcProducts->count()) {
			$selectedFont = $pdcProducts->getFirstItem()->getSelectedFont();
            if($selectedFont != null && $selectedFont != "") {
                $custIds = json_decode($selectedFont, true);    
            }
		}
		return $custIds;
    }
}