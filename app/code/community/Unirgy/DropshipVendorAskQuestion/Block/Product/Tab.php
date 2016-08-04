<?php

class Unirgy_DropshipVendorAskQuestion_Block_Product_Tab extends Mage_Catalog_Block_Product_View_Description
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        Mage::getSingleton('customer/session')->setData('udqa_question_form_show_captcha',1);
        $this->getLayout()->createBlock('page/html_pager', 'udqa.product.list.toolbar');

        $this->setChild('udqa.list',
            $this->getLayout()->createBlock('udqa/product_question_list', 'udqa.product.list')->setTemplate('udqa/product/list.phtml')
        );
        $this->setChild('udqa.qa',
            $this->getLayout()->createBlock('udqa/product_question', 'udqa.product.question')->setTemplate('udqa/product/question.phtml')
                ->setChild('form.additional.info',
                    $this->getLayout()->createBlock('core/text_list', 'form.additional.info')
                        ->insert(
                            $this->getLayout()->createBlock('captcha/captcha', 'captcha')->setFormId('udqa_question_form')->setImgWidth(230)->setImgHeight(50),
                            '', false, 'captcha'
                        )
                )
        );
        return $this;
    }
}
