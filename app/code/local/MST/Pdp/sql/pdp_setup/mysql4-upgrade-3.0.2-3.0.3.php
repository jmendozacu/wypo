<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE {$this->getTable('pdp/artworkcate')} ADD `thumbnail` varchar(500) DEFAULT NULL;
ALTER TABLE {$this->getTable('pdp/artworkcate')} ADD `parent_id` smallint(6) DEFAULT NULL;
ALTER TABLE {$this->getTable('pdp/images')} ADD `status` smallint(6) DEFAULT NULL;
ALTER TABLE {$this->getTable('pdp/images')} ADD `thumbnail` varchar(500) DEFAULT NULL;
ALTER TABLE {$this->getTable('pdp/images')} ADD `description` varchar(500) DEFAULT NULL;
");
$installer->endSetup();