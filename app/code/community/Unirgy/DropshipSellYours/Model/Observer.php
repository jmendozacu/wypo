<?php

class Unirgy_DropshipSellYours_Model_Observer
{
    public function umicrosite_check_permission($observer)
    {
        return $this;
        if (!Mage::getStoreConfig('udropship/microsite/use_basic_pro_accounts')
            || !$observer->getVendor() || !$observer->getVendor()->getId()
        ) {
            return $this;
        }
        switch ($observer->getAction()) {
            case 'microsite':
            case 'adminhtml':
            case 'new_product':
                if ($observer->getVendor()->getAccountType()!='pro') {
                    $observer->getTransport()->setRedirect(
                        Mage::app()->getStore()->getUrl('udsell/index/becomePro')
                    );
                    $observer->getTransport()->setAllowed(false);
                }
                break;
        }
        return $this;
    }
    public function udropship_adminhtml_vendor_edit_prepare_form($observer)
    {
        $id = $observer->getEvent()->getId();
        $form = $observer->getEvent()->getForm();
        $fieldset = $form->getElement('vendor_form');

        $fieldset->addField('is_featured', 'select', array(
            'name'      => 'is_featured',
            'label'     => Mage::helper('udropship')->__('Is Featured'),
            'options'   => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(),
        ));

        return $this;
        if (Mage::getStoreConfig('udropship/microsite/use_basic_pro_accounts')) {
            $fieldset->addField('account_type', 'select', array(
                'name'      => 'account_type',
                'label'     => Mage::helper('udropship')->__('Account Type'),
                'options'   => Mage::getSingleton('udsell/source')->setPath('account_type')->toOptionHash(),
            ));
        }
    }

    public function controller_front_init_before($observer)
    {
        $this->_initConfigRewrites();
    }
    public function udropship_init_config_rewrites()
    {
        $this->_initConfigRewrites();
    }
    protected function _initConfigRewrites()
    {
        if (
        Mage::helper('udropship')->compareMageVer('1.7.0.0', '1.12.0.0')
        ) {
            if (
            Mage::helper('udropship')->compareMageVer('1.9.0.1', '1.14.0.0')
            ) {
                Mage::getConfig()->setNode('global/models/catalogsearch_resource/rewrite/fulltext', 'Unirgy_DropshipSellYours_Model_Rewrite1901_CatalogSearch_Resource_Fulltext');
            } else {
                Mage::getConfig()->setNode('global/models/catalogsearch_resource/rewrite/fulltext', 'Unirgy_DropshipSellYours_Model_Rewrite1700_CatalogSearch_Resource_Fulltext');
            }

            Mage::getConfig()->setNode('global/models/catalogsearch_resource/rewrite/fulltext_engine', 'Unirgy_DropshipSellYours_Model_Rewrite1700_CatalogSearch_Resource_Fulltext_Engine');
            Mage::getConfig()->setNode('global/models/catalogsearch_resource/rewrite/fulltext_collection', 'Unirgy_DropshipSellYours_Model_Rewrite1700_CatalogSearch_Resource_Fulltext_Collection');
        }
    }
}