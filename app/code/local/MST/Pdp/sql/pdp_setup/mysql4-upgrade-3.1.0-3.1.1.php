<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE {$this->getTable('mst_pdp_product_status')} MODIFY note TEXT;
");
$installer->endSetup();
