<?php
$installer = $this;
$installer->startSetup();
$installer->run("
	ALTER TABLE {$this->getTable('mst_pdp_images')} ADD color_type smallint(2) NOT NULL DEFAULT '2';
");
$installer->endSetup(); 
