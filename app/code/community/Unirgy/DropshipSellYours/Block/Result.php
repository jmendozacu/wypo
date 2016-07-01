<?php

class Unirgy_DropshipSellYours_Block_Result extends Mage_Core_Block_Template
{
    public function setListCollection()
    {
        if (Mage::app()->getRequest()->getParam('q') || Mage::registry('current_category')) {
            $this->setListOrders();
            $this->getChild('search_result_list')
                ->setCollection($this->_getProductCollection());
        }
    }

    public function getListBlock()
    {
        return $this->getChild('search_result_list');
    }

    public function setListOrders()
    {
        $category = Mage::getSingleton('catalog/layer')
            ->getCurrentCategory();
        /* @var $category Mage_Catalog_Model_Category */
        $availableOrders = $category->getAvailableSortByOptions();
        unset($availableOrders['position']);
        $availableOrders = array_merge(array(
            'relevance' => Mage::helper('udropship')->__('Relevance')
        ), $availableOrders);

        $this->getListBlock()
            ->setAvailableOrders($availableOrders)
            ->setDefaultDirection('desc')
            ->setSortBy('relevance');

        return $this;
    }

    protected function _getProductCollection()
    {
        $col = $this->getSearchModel()->getProductCollection();

        if (Mage::registry('current_category')) {
            $col->addCategoryFilter(Mage::registry('current_category'));
        }
        $col->addAttributeToFilter('type_id', array('in'=>array('simple','configurable','downloadable','virtual')));
        $sess = Mage::getSingleton('udropship/session');
        if ($sess->getData('udsell_search_type')) {
            $col->addAttributeToFilter('entity_id', array('in'=>$sess->getVendor()->getVendorTableProductIds()));
        }
        return $col;
    }
    public function getSearchModel()
    {
        $isEE = Mage::helper('udropship')->isEE();
        $isSearchEE = false;
        if ($isEE) {
            $isSearchEE = Mage::helper('enterprise_search')->isThirdPartSearchEngine() && Mage::helper('enterprise_search')->isActiveEngine();
        }
        if ($this->getRequest()->getParam('type') == 'barcode'
            || !$this->getRequest()->getParam('q')
        ) {
            if ($isSearchEE) {
                return Mage::getSingleton('enterprise_search/catalog_layer');
            } else {
                return Mage::getSingleton('catalogsearch/advanced');
            }
        } else {
            if ($isSearchEE) {
                return Mage::getSingleton('enterprise_search/search_layer');
            } else {
                return Mage::getSingleton('catalogsearch/layer');
            }
        }
    }

    public function getResultCount()
    {
        if (!$this->getData('result_count')) {
            $size = $this->getSearchModel()->getProductCollection()->getSize();
            $this->setResultCount($size);
        }
        return $this->getData('result_count');
    }

    protected function _prepareLayout()
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $sess = Mage::getSingleton('udropship/session');
            $searchUrlKey = $sess->getData('udsell_search_type') ? 'mysellSearch' : 'sellSearch';
            if ($sess->getData('udsell_search_type')) {
                $breadcrumbsBlock->addCrumb('sellyours', array(
                    'label'=>Mage::helper('udropship')->__('My Sell List'),
                    'title'=>Mage::helper('udropship')->__('My Sell List'),
                    'link'=>$this->getUrl('udsell/index/mysellSearch')
                ));
            } else {
                $breadcrumbsBlock->addCrumb('sellyours', array(
                    'label'=>Mage::helper('udropship')->__('Sell Yours'),
                    'title'=>Mage::helper('udropship')->__('Sell Yours'),
                    'link'=>$this->getUrl('udsell/index/sellSearch')
                ));
            }

            if (Mage::registry('current_category')) {
                $cat = Mage::registry('current_category');
                $pathIds = explode(',', $cat->getPathInStore());
                array_shift($pathIds);
                $cats = Mage::helper('udropship/catalog')->getCategoriesCollection($pathIds);
                foreach ($cats as $c) {
                    $breadcrumbsBlock->addCrumb('sellyours_cat'.$c->getId(), array(
                        'label'=>$c->getName(),
                        'title'=>$c->getName(),
                        'link'=>$this->getUrl('udsell/index/'.$searchUrlKey, array('_current'=>true, 'c'=>$c->getId()))
                    ));
                }
                $breadcrumbsBlock->addCrumb('sellyours_cat'.$cat->getId(), array(
                    'label'=>$cat->getName(),
                    'title'=>$cat->getName(),
                    'link'=>$this->getUrl('udsell/index/'.$searchUrlKey, array('_current'=>true, 'c'=>$cat->getId()))
                ));
            }

            if (($q = $this->getRequest()->getParam('q'))) {
                $breadcrumbsBlock->addCrumb('sellyours_query', array(
                    'label'=>htmlspecialchars($q),
                    'title'=>htmlspecialchars($q),
                    'link'=>$this->getUrl('*/*/*', array('_current'=>true))
                ));
            }

        }
    }
}