<?php
class MST_Pdp_Block_Adminhtml_Pdp_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /* public function __construct()
    {
        parent::__construct();
        $this->setId('pdpGrid');
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareLayout()
	{
	    $this->unsetChild('reset_filter_button');
	    $this->unsetChild('search_button');
	} */
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'clipartsGrid' );
		$this->setDefaultSort ( 'image_id' );
		$this->setDefaultDir ( 'ASC' );
		$this->setSaveParametersInSession ( true );
	}
	protected function _prepareCollection() {
		$collection = Mage::getModel ('pdp/images' )->getCollection ();
		$this->setCollection ( $collection );
		return parent::_prepareCollection ();
	}
	protected function _prepareColumns() {
		$this->addColumn ( 'image_id', array (
				'header' => Mage::helper ( 'pdp' )->__ ( 'ID' ),
				'align' => 'left',
				'width' => '50px',
				'index' => 'image_id' 
		) );
		$this->addColumn('thumbnail', array(
          'header'    => Mage::helper('pdp')->__('Thumbnail'),
          'align'     =>'left',
          'index'     => 'image',
          'renderer'  => 'pdp/adminhtml_pdp_renderer_thumbnail',
          'filter' => false,
		  'sortable' => false,
        ));
        $this->addColumn('Image', array(
          'header'    => Mage::helper('pdp')->__('Image'),
          'align'     =>'left',
          'index'     => 'image',
          'renderer'  => 'pdp/adminhtml_pdp_renderer_image',
          'filter' => false,
		  'sortable' => false,
        ));
		$this->addColumn ( 'image_name', array (
				'header' => Mage::helper ( 'pdp' )->__ ( 'Label' ),
				'align' => 'left',
				'index' => 'image_name'
		) );
		$this->addColumn ( 'category', array (
				'header' => Mage::helper ( 'pdp' )->__ ( 'Categories' ),
				'align' => 'left',
				'index' => 'category',
				'type' => 'options',
				'options'=> Mage::getModel('pdp/pdp')->getCategoriesGroup2(),
				'filter'=>'MST_Pdp_Block_Adminhtml_Widget_Grid_Column_Filter_Select',
				'renderer'  => 'MST_Pdp_Block_Adminhtml_Widget_Grid_Column_Renderer_Options',
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
		
		$this->addColumn ( 'action', array (
				'header' => Mage::helper ( 'pdp' )->__ ( 'Action' ),
				'width' => '100',
				'type' => 'action',
				'getter' => 'getId',
				'actions' => array (
						array (
								'caption' => Mage::helper ( 'pdp' )->__ ( 'Edit' ),
								'url' => array (
										'base' => '*/*/edit' 
								),
								'field' => 'id' 
						) 
				),
				'filter' => false,
				'sortable' => false,
				'index' => 'stores',
				'is_system' => true 
		) );
		
		$this->addExportType ( '*/*/exportCsv', Mage::helper ( 'pdp' )->__ ( 'CSV' ) );
		$this->addExportType ( '*/*/exportXml', Mage::helper ( 'pdp' )->__ ( 'XML' ) );
		
		return parent::_prepareColumns ();
	}
	protected function _prepareMassaction() {
		$this->setMassactionIdField ( 'id' );
		$this->getMassactionBlock ()->setFormFieldName ( 'pdp' );
		
		$this->getMassactionBlock ()->addItem ( 'delete', array (
				'label' => Mage::helper ( 'pdp' )->__ ( 'Delete' ),
				'url' => $this->getUrl ( '*/*/massDelete' ),
				'confirm' => Mage::helper ( 'pdp' )->__ ( 'Are you sure?' ) 
		) );
		$statuses = Mage::getSingleton ( 'pdp/artworkcate' )->getOptionArray ();
		array_unshift ( $statuses, array (
				'label' => '',
				'value' => '' 
		) );
		$this->getMassactionBlock ()->addItem ( 'status', array (
				'label' => Mage::helper ( 'pdp' )->__ ( 'Change status' ),
				'url' => $this->getUrl ( '*/*/massStatus', array (
						'_current' => true 
				) ),
				'additional' => array (
						'visibility' => array (
								'name' => 'status',
								'type' => 'select',
								'class' => 'required-entry',
								'label' => Mage::helper ( 'pdp' )->__ ( 'Status' ),
								'values' => $statuses 
						) 
				) 
		) );
		$this->getMassactionBlock ()->addItem ( 'category', array (
				'label' => Mage::helper ( 'pdp' )->__ ( 'Change category' ),
				'url' => $this->getUrl ( '*/*/massCategory', array (
						'_current' => true 
				) ),
				'additional' => array (
						'visibility' => array (
								'name' => 'category',
								'type' => 'select',
								'class' => 'required-entry',
								'label' => Mage::helper ( 'pdp' )->__ ( 'Category' ),
								'values' => Mage::getModel('pdp/pdp')->getCategoriesGroup2()
						) 
				) 
		) );
		return $this;
	}
	public function getRowUrl($row) {
		return $this->getUrl ( '*/*/edit', array (
				'id' => $row->getId () 
		) );
	}
}