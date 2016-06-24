<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE {$this->getTable('mst_pdp_multisides')} CHANGE mask_photo thumbnail varchar(500);
");
$installer->endSetup();
