<?php

class Unirgy_DropshipTierCommission_Block_Adminhtml_VendorStatementEditTabRefundRows extends Unirgy_Dropship_Block_Adminhtml_Vendor_Statement_Edit_Tab_RefundRows
{
    protected function _prepareColumns()
    {
        $this->addColumn('sku', array(
            'header'    => Mage::helper('udropship')->__('SKU'),
            'index'     => 'sku',
            'default'   => ''
        ));
        $this->addColumn('vendor_sku', array(
            'header'    => Mage::helper('udropship')->__('Vendor SKU'),
            'index'     => 'vendor_sku',
            'default'   => ''
        ));
        $this->addColumn('product', array(
            'header'    => Mage::helper('udropship')->__('Product'),
            'index'     => 'product',
            'default'   => ''
        ));
        $this->addColumnsOrder('sku', 'refund_increment_id');
        $this->addColumnsOrder('sku', 'vendor_sku');
        $this->addColumnsOrder('product', 'sku');
        return parent::_prepareColumns();
    }
}