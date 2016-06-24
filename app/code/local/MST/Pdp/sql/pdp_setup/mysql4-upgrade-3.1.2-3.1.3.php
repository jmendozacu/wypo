<?php
$installer = $this;
$installer->startSetup();
$installer->run("
	CREATE TABLE IF NOT EXISTS {$this->getTable('mst_pdp_customer_upload_image')} (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `customer_id` int(11) unsigned NOT NULL,
		`filename` varchar(500) NOT NULL DEFAULT '',
		`thumbnail` varchar(500) NOT NULL DEFAULT '',
        `original_filename` varchar(500) NOT NULL DEFAULT '',
		`category` varchar(500) NOT NULL DEFAULT '',
        `tag` varchar(500) NOT NULL DEFAULT '',
		`position` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS {$this->getTable('mst_pdp_customer_upload_image_category')} (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`title` varchar(500) NOT NULL,
		`status` smallint(2) NOT NULL DEFAULT '1',
		`position` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");
$installer->endSetup(); 
