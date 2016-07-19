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
 * @package    Unirgy_DropshipMicrosite
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

$this->startSetup();

$conn = $this->_conn;

$vt = $this->getTable('udropship_vendor');

/*
$t = $this->getTable('cms_page');
$conn->addColumn($t, 'udropship_vendor', 'int(11) unsigned');
$conn->addConstraint('FK_CMS_PAGE_VENDOR', $t, 'udropship_vendor', $vt, 'vendor_id');

$t = $this->getTable('cms_block');
$conn->addColumn($t, 'udropship_vendor', 'int(11) unsigned');
$conn->addConstraint('FK_CMS_BLOCK_VENDOR', $t, 'udropship_vendor', $vt, 'vendor_id');
*/
$t = $this->getTable('admin_user');
$conn->modifyColumn($t, 'username', 'varchar(128) NOT NULL DEFAULT \'\'');
$conn->addColumn($t, 'udropship_vendor', 'int(11) unsigned');
$conn->addConstraint('FK_ADMIN_USER_VENDOR', $t, 'udropship_vendor', $vt, 'vendor_id');

$this->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('udropship_vendor_registration')}`  (
`reg_id` int(10) unsigned NOT NULL auto_increment,
`store_id` smallint(5) unsigned NOT NULL,
`vendor_name` varchar(255) default NULL,
`telephone` varchar(255) default NULL,
`email` varchar(255) default NULL,
`password_enc` varchar(255) default NULL,
`password_hash` varchar(255) default NULL,
`carrier_code` varchar(64) default NULL,
`vendor_attn` varchar(255) default NULL,
`street` text,
`city` varchar(255) default NULL,
`zip` varchar(255) default NULL,
`region_id` int(10) unsigned default NULL,
`region` varchar(255) default NULL,
`country_id` char(2) default NULL,
`remote_ip` varchar(15) default NULL,
`registered_at` datetime default NULL,
`url_key` varchar(64) default NULL,
`comments` text,
`notes` text,
PRIMARY KEY  (`reg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();