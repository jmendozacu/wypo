<?php

class Unirgy_DropshipVendorAskQuestion_Block_Adminhtml_Question_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_blockGroup = 'udqa';
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_question';

        $this->_updateButton('save', 'label', Mage::helper('udropship')->__('Save Question'));
        $this->_updateButton('save', 'id', 'save_button');
        $this->_updateButton('delete', 'label', Mage::helper('udropship')->__('Delete Question'));

        if( $this->getRequest()->getParam('vendorId', false) ) {
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('adminhtml/udropshipadmin_vendor/edit', array('id' => $this->getRequest()->getParam('vendorId', false))) .'\')' );
        }

        if( $this->getRequest()->getParam('customerId', false) ) {
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('adminhtml/customer/edit', array('id' => $this->getRequest()->getParam('customerId', false))) .'\')' );
        }

        if( $this->getRequest()->getParam('ret', false) == 'pending' ) {
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/*/pending') .'\')' );
            $this->_updateButton('delete', 'onclick', 'deleteConfirm(\'' . Mage::helper('udropship')->__('Are you sure you want to do this?') . '\', \'' . $this->getUrl('*/*/delete', array(
                $this->_objectId => $this->getRequest()->getParam($this->_objectId),
                'ret'           => 'pending',
            )) .'\')' );
            Mage::register('ret', 'pending');
        }

        if( $this->getRequest()->getParam($this->_objectId) ) {
            $questionData = Mage::getModel('udqa/question')
                ->load($this->getRequest()->getParam($this->_objectId));
            Mage::register('question_data', $questionData);
        }
    }

    public function getHeaderText()
    {
        if( Mage::registry('question_data') && Mage::registry('question_data')->getId() ) {
            return Mage::helper('udropship')->__("Edit Question");
        } else {
            return Mage::helper('udropship')->__('New Question');
        }
    }
}
