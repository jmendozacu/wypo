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
 * @package    Unirgy_DropshipSplit
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipSplit_Block_Adminhtml_Vendor_Shipping extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('vendor_form');
        //$this->setTemplate('udropship/vendor/form.phtml');
    }

    protected function _prepareForm()
    {
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('udropship');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('vendor_form', array(
            'legend'=>Mage::helper('udropship')->__('Vendor Info')
        ));

        $fieldset->addField('reg_id', 'hidden', array(
            'name'      => 'reg_id',
        ));
        $fieldset->addField('password_hash', 'hidden', array(
            'name'      => 'password_hash',
        ));

        $fieldset->addField('vendor_name', 'text', array(
            'name'      => 'vendor_name',
            'label'     => Mage::helper('udropship')->__('Vendor Name'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $fieldset->addField('status', 'select', array(
            'name'      => 'status1',
            'label'     => Mage::helper('udropship')->__('Status'),
            'class'     => 'required-entry',
            'required'  => true,
            'options'   => Mage::getSingleton('udropship/source')->setPath('vendor_statuses')->toOptionHash(),
        ));

        $fieldset->addField('carrier_code', 'select', array(
            'name'      => 'carrier_code',
            'label'     => Mage::helper('udropship')->__('Used Carrier'),
            'class'     => 'required-entry',
            'required'  => true,
            'options'   => Mage::getSingleton('udropship/source')->setPath('carriers')->toOptionHash(true),
        ));

        $fieldset->addField('email', 'text', array(
            'name'      => 'email',
            'label'     => Mage::helper('udropship')->__('Vendor Email'),
            'class'     => 'required-entry validate-email',
            'required'  => true,
            'note'      => Mage::helper('udropship')->__('Email is also used as username'),
        ));
/*
        $fieldset->addField('password', 'password', array(
            'name'      => 'password',
            'label'     => Mage::helper('udropship')->__('Log In Password'),
            'note'      => Mage::helper('udropship')->__('Login disabled if empty'),
        ));
*/
        $fieldset->addField('telephone', 'text', array(
            'name'      => 'telephone',
            'label'     => Mage::helper('udropship')->__('Vendor Telephone'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $templates = Mage::getSingleton('adminhtml/system_config_source_email_template')->toOptionArray();
        $templates[0]['label'] = Mage::helper('udropship')->__('Use Default Configuration');
        $fieldset->addField('email_template', 'select', array(
            'name'      => 'email_template',
            'label'     => Mage::helper('udropship')->__('Notification Template'),
            'values'   => $templates,
        ));

        $fieldset->addField('vendor_shipping', 'hidden', array(
            'name' => 'vendor_shipping',
        ));
/*
        $fieldset->addField('url_key', 'text', array(
            'name'      => 'url_key',
            'label'     => Mage::helper('udropship')->__('URL friendly identifier'),
        ));
*/
        $countries = Mage::getModel('adminhtml/system_config_source_country')
            ->toOptionArray();
        //unset($countries[0]);


        $countryId = Mage::registry('vendor_data') ? Mage::registry('vendor_data')->getCountryId() : null;
        if (!$countryId) {
            $countryId = Mage::getStoreConfig('general/country/default');
        }

        $regionCollection = Mage::getModel('directory/region')
            ->getCollection()
            ->addCountryFilter($countryId);

        $regions = $regionCollection->toOptionArray();

        if ($regions) {
            $regions[0]['label'] = Mage::helper('udropship')->__('Please select state...');
        } else {
            $regions = array(array('value'=>'', 'label'=>''));
        }

        $fieldset = $form->addFieldset('address_form', array(
            'legend'=>Mage::helper('udropship')->__('Shipping Origin Address')
        ));

        $fieldset->addField('vendor_attn', 'text', array(
            'name'      => 'vendor_attn',
            'label'     => Mage::helper('udropship')->__('Attention To'),
        ));

        $fieldset->addField('street', 'textarea', array(
            'name'      => 'street',
            'label'     => Mage::helper('udropship')->__('Street'),
            'class'     => 'required-entry',
            'required'  => true,
            'style'     => 'height:50px',
        ));

        $fieldset->addField('city', 'text', array(
            'name'      => 'city',
            'label'     => Mage::helper('udropship')->__('City'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $fieldset->addField('zip', 'text', array(
            'name'      => 'zip',
            'label'     => Mage::helper('udropship')->__('Zip / Postal code'),
        ));

        $country = $fieldset->addField('country_id', 'select',
            array(
                'name' => 'country_id',
                'label' => Mage::helper('udropship')->__('Country'),
                'title' => Mage::helper('udropship')->__('Please select Country'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $countries,
            )
        );

        $fieldset->addField('region_id', 'select',
            array(
                'name' => 'region_id',
                'label' => Mage::helper('udropship')->__('State'),
                'title' => Mage::helper('udropship')->__('Please select State'),
                'values' => $regions,
            )
        );

        if ($vendor) {
            $form->setValues($vendor->getData());
        }

        if (!$id) {
            $country->setValue($countryId);
        }

        return parent::_prepareForm();
    }

}