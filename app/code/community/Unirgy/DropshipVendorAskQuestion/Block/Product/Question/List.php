<?php

class Unirgy_DropshipVendorAskQuestion_Block_Product_Question_List extends Mage_Catalog_Block_Product_View_Description
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($toolbar = $this->getLayout()->getBlock('udqa.product.list.toolbar')) {
            $toolbar->setCollection($this->getQuestionsCollection());
            $this->setChild('toolbar', $toolbar);
        }

        return $this;
    }
    protected $_questionsCollection;
    public function getQuestionsCollection()
    {
        if (is_null($this->_questionsCollection)) {
            $this->_questionsCollection = Mage::helper('udqa')->getProductQuestionsCollection();
        }
        return $this->_questionsCollection;
    }
    public function getProductUrl($question)
    {
        return $this->getUrl('catalog/product/view', array('id'=>$question->getProductId()));
    }
}