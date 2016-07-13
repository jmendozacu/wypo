<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipSellYours
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipSellYours_IndexController extends Unirgy_Dropship_Controller_VendorAbstract
{
    protected function _getC2CSession()
    {
        return Mage::getSingleton('udsell/session');
    }
    protected function _getVendorSession()
    {
        return Mage::getSingleton('udropship/session');
    }
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }
    public function becomeProAction()
    {
        $this->loadLayout()->renderLayout();
    }
    public function vendorAction()
    {
        $uSess = $this->_getVendorSession();
        $cSess = $this->_getCustomerSession();
        $sess  = $this->_getC2CSession();
        $vendor = $uSess->getVendor();
        $customer = $cSess->getCustomer();
        if (!$uSess->isLoggedIn() && $cSess->isLoggedIn() && $customer->getVendorId()) {
            $uSess->loginById($customer->getVendorId());
        }
        if ($uSess->authenticate($this)) {
            Mage::helper('udsell')->hookVendorCustomer($vendor, $customer);
            $redirectUrl = Mage::getUrl('udropship/vendor');
            if ($sess->getVendorRedirectUrl()) {
                $redirectUrl = $sess->getVendorRedirectUrl(true);
            }
            $this->_redirectUrl($redirectUrl);
        }
    }
    public function customerAction()
    {
        $uSess = $this->_getVendorSession();
        $cSess = $this->_getCustomerSession();
        $sess  = $this->_getC2CSession();
        $vendor = $uSess->getVendor();
        $customer = $cSess->getCustomer();
        if (!$cSess->isLoggedIn() && $uSess->isLoggedIn() && $vendor->getCustomerId()) {
            $cSess->loginById($vendor->getCustomerId());
        }
        if ($cSess->authenticate($this)) {
            Mage::helper('udsell')->hookVendorCustomer($vendor, $customer);
            $redirectUrl = Mage::helper('customer')->getAccountUrl();
            if ($sess->getCustomerRedirectUrl()) {
                $redirectUrl = $sess->getCustomerRedirectUrl(true);
            }
            $this->_redirectUrl($redirectUrl);
        }
    }

    public function mysellSearchAction()
    {
        $this->_getVendorSession()->setData('udsell_search_type', 1);
        $this->_getVendorSession()->setData('udsell_active_page', 'udsell');
        $this->_sellSearch();
    }
    public function sellSearchAction()
    {
        $this->_getVendorSession()->setData('udsell_search_type', 0);
        $this->_getVendorSession()->setData('udsell_active_page', 'udsell');
        $this->_sellSearch();
    }

    protected function _sellSearch()
    {
        if (!$this->_getVendorSession()->authenticate($this)) return;
        Mage::register('udsell_status_all',1);
        if ($this->getRequest()->getParam('q') || $this->getRequest()->getParam('c')) {
            $categoryId=$this->getRequest()->getParam('c');
            if (!$categoryId) {
                $categoryId = Mage::app()->getStore()->getRootCategoryId();
            }
            $category = Mage::getModel('catalog/category')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($categoryId);
            if ($category->getId()) {
                Mage::register('current_category', $category);
            }
            if ($this->getRequest()->getParam('type') == 'barcode') {
                Mage::getSingleton('catalogsearch/advanced')->addFilters(array('sku'=>$this->getRequest()->getParam('q')));
            } else {
                $query = Mage::helper('catalogsearch')->getQuery();
                $query->setStoreId(Mage::app()->getStore()->getId());
                if ($query->getQueryText()) {
                    if (Mage::helper('catalogsearch')->isMinQueryLength()) {
                        $query->setId(0)
                            ->setIsActive(1)
                            ->setIsProcessed(1);
                    }
                    else {
                        if ($query->getId()) {
                            $query->setPopularity($query->getPopularity()+1);
                        }
                        else {
                            $query->setPopularity(1);
                        }

                        if ($query->getRedirect()){
                            $query->save();
                            $this->getResponse()->setRedirect($query->getRedirect());
                            return;
                        }
                        else {
                            $query->prepare();
                        }
                    }
                }
            }
        }
        $this->_setTheme();
        $this->loadLayout();
        if (($head = $this->getLayout()->getBlock('header'))) {
            $head->setActivePage($this->_getActivePage());
        }
        $this->_initLayoutMessages('catalogsearch/session');
        $this->renderLayout();
    }

    public function sellAction()
    {
        if (!$this->_getVendorSession()->authenticate($this)) return;
        if ($product = $this->_initProduct()) {
            $hlp = Mage::helper('udsell');
            $uSess = $this->_getVendorSession();
            $vendor = $uSess->getVendor();
            $sellYoursFormData = array();
            if ($uSess->isLoggedIn()) {
                Mage::register('current_vendor', $vendor);
                $mvData = Mage::helper('udmulti')->getMultiVendorData(array($product->getId()))
                    ->getItemByColumnValue('vendor_id', $vendor->getId());
                $activeMvData = Mage::helper('udmulti')->getActiveMultiVendorData(array($product->getId()))
                    ->getItemByColumnValue('vendor_id', $vendor->getId());

                if ($mvData && $mvData->getId()) {

                    $gpData = Mage::helper('udmulti')->getMvGroupPrice(array($product->getId()));
                    $tpData = Mage::helper('udmulti')->getMvTierPrice(array($product->getId()));

                    $udmTierPrice = $udmGroupPrice = array();
                    foreach ($gpData as $__gpd) {
                        if ($mvData->getProductId() != $__gpd->getProductId() || $mvData->getVendorId() != $__gpd->getVendorId()) continue;
                        if ($__gpd->getData('all_groups')) {
                            $__gpd->setData('customer_group_id', Mage_Customer_Model_Group::CUST_GROUP_ALL);
                        }
                        $udmGroupPrice[] = $__gpd->getData();
                    }
                    foreach ($tpData as $__tpd) {
                        if ($mvData->getProductId() != $__tpd->getProductId() || $mvData->getVendorId() != $__tpd->getVendorId()) continue;
                        if ($__tpd->getData('all_groups')) {
                            $__tpd->setData('customer_group_id', Mage_Customer_Model_Group::CUST_GROUP_ALL);
                        }
                        $udmTierPrice[] = $__tpd->getData();
                    }
                    $mvData->setData('group_price', $udmGroupPrice);
                    $mvData->setData('tier_price', $udmTierPrice);

                    if (!$this->_getC2CSession()->getHideAlreadySellingMsg(true)) {
                        $this->_getC2CSession()->addNotice(
                            Mage::helper('udropship')->__('You already selling this item')
                        );
                    }
                    if (!$activeMvData || !$activeMvData->getId()) {
                        $this->_getC2CSession()->addNotice(
                            Mage::helper('udropship')->__('The request to sell this product need to be approved by admin')
                        );
                    }
                    $sellYoursFormData['udmulti'] = $mvData->getData();
                }
                if ($product->getTypeId()=='configurable') {
                    $simpleProds = Mage::helper('udprod')->getEditSimpleProductData($product, true, $vendor);
                    foreach ($simpleProds as $simpleProd) {
                        if (isset($simpleProd['udmulti']) && is_array($simpleProd['udmulti'])) {
                            $sellYoursFormData['udsell_cfgsell'][] = $simpleProd;
                        }
                    }
                }
            }
            if (($tmpMvData = $this->_fetchSellYoursFormData($product->getId()))) {
                $sellYoursFormData = $tmpMvData;
            }
            unset($sellYoursFormData['udsell_cfgsell']['$ROW']);
            Mage::register('sell_yours_data_'.$product->getId(), $sellYoursFormData);
            Mage::register('productId', $product->getId());
            Mage::getModel('catalog/design')->applyDesign($product, Mage_Catalog_Model_Design::APPLY_FOR_PRODUCT);

            $this->_setTheme();
            $this->_initProductLayout($product, true);
            if (($head = $this->getLayout()->getBlock('header'))) {
                $head->setActivePage($this->_getActivePage());
            }

            // update breadcrumbs
            if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                /*$breadcrumbsBlock->addCrumb('product', array(
                    'label'    => $product->getName(),
                    'link'     => $product->getProductUrl(),
                    'readonly' => true,
                ));
                $breadcrumbsBlock->addCrumb('udsell_sell', array('label' => Mage::helper('udropship')->__('Sell Yours')));*/
            }
            $this->_initLayoutMessages('udsell/session');
            $this->renderLayout();
        } elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }

    public function sellPostAction()
    {
        $hlp = Mage::helper('udsell');
        if ($product = $this->_initProduct()) {
            if (!$this->_getVendorSession()->authenticate($this)) {
                if ($this->getRequest()->isPost()) {
                    $this->_saveSellYoursFormData();
                }
            } else {
                $formData = array();
                if ($this->getRequest()->isPost()) {
                    $formData = $this->getRequest()->getPost();
                } else {
                    $formData = $this->_fetchSellYoursFormData();
                }
                if (!empty($formData) && is_array($formData)) {
                    Mage::helper('udsell')->processSellRequest($this->_getVendorSession()->getVendor(), $product, $formData);
                $this->_getC2CSession()->setHideAlreadySellingMsg(true);
                $this->_getC2CSession()->addSuccess(Mage::helper('udropship')->__('Your sell request was succesfully submitted'));
                } else {
                    $this->_getC2CSession()->addError(Mage::helper('udropship')->__('Empty sell request form data'));
                }
                $this->_redirect('*/*/sell', array('id'=>$product->getId()));
            }
        } elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }

    protected function _saveSellYoursFormData($data=null, $id=null)
    {
        Mage::helper('udsell')->saveSellYoursFormData($data, $id);
    }

    protected function _fetchSellYoursFormData($id=null)
    {
        return Mage::helper('udsell')->fetchSellYoursFormData($id);
    }

    protected function _getActivePage()
    {
        return $this->_getVendorSession()->getData('udsell_active_page');
    }

    public function productAction()
    {
        if ($product = $this->_initProduct()) {
            Mage::app()->getRequest()->setParam('alloffers',1);
            Mage::register('productId', $product->getId());
            Mage::getModel('catalog/design')->applyDesign($product, Mage_Catalog_Model_Design::APPLY_FOR_PRODUCT);

            $this->_setTheme();
            $this->_initProductLayout($product);
            if (($head = $this->getLayout()->getBlock('header'))) {
                $head->setActivePage('udsell');
            }

            // update breadcrumbs
            if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbsBlock->addCrumb('product', array(
                    'label'    => $product->getName(),
                    'link'     => $product->getProductUrl(),
                    'readonly' => true,
                ));
                $breadcrumbsBlock->addCrumb('udsell_cfgsell', array('label' => Mage::helper('udropship')->__('All Offers')));
            }
            $this->_initLayoutMessages('udsell/session');
            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('tag/session');
            $this->_initLayoutMessages('checkout/session');
            $this->renderLayout();
        } elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }

    protected function _initProduct()
    {
        Mage::dispatchEvent('udsell_controller_sell_before', array('controller_action'=>$this));
        $categoryId = (int) $this->getRequest()->getParam('c', false);
        $productId  = (int) $this->getRequest()->getParam('id');

        $product = $this->_loadProduct($productId);

        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            Mage::register('current_category', $category);
        }

        try {
            Mage::dispatchEvent('udsell_controller_sell_init', array('product'=>$product));
            Mage::dispatchEvent('udsell_controller_sell_init_after', array('product'=>$product, 'controller_action' => $this));
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $product;
    }

    protected function _loadProduct($productId)
    {
        if (!$productId) {
            return false;
        }

        $oldStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $product = Mage::getModel('catalog/product')
            ->load($productId);

        Mage::app()->getStore()->setId($oldStoreId);
        Mage::app()->setCurrentStore($oldStoreId);
        /* @var $product Mage_Catalog_Model_Product */
        if (!$product->getId() || !$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
            return false;
        }

        Mage::register('current_product', $product);
        Mage::register('product', $product);

        return $product;
    }

    protected function _initProductLayout($product, $isSell=false)
    {
        $update = $this->getLayout()->getUpdate();

        $update->addHandle('default');
        $this->addActionLayoutHandles();


        $update->addHandle(($isSell ? 'UDC2C_' : '').'PRODUCT_TYPE_'.$product->getTypeId());

        if ($product->getPageLayout()) {
            $this->getLayout()->helper('page/layout')
                ->applyHandle($product->getPageLayout());
        }

        $this->loadLayoutUpdates();
        if ($product->getPageLayout()) {
            $this->getLayout()->helper('page/layout')
                ->applyTemplate($product->getPageLayout());
        }
        $update->addUpdate($product->getCustomLayoutUpdate());
        $this->generateLayoutXml()->generateLayoutBlocks();
    }

}