<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE {$this->getTable('pdp/artworkcate')} ADD `image_types` varchar(100) DEFAULT NULL;
");
$installer->endSetup();
