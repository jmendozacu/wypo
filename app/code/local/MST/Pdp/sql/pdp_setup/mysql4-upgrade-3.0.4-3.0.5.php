<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE {$this->getTable('pdp/images')} ADD `image_types` varchar(500) DEFAULT NULL;
ALTER TABLE {$this->getTable('pdp/images')} ADD `sort_description` varchar(500) DEFAULT NULL;
ALTER TABLE {$this->getTable('pdp/images')} ADD `image_tag` varchar(500) DEFAULT NULL;
");
$installer->endSetup();