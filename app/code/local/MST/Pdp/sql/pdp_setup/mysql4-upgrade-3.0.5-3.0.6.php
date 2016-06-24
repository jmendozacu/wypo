<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE {$this->getTable('mst_pdp_product_status')} ADD COLUMN(
        selected_image TEXT NOT NULL default '',
        selected_color TEXT NOT NULL default '',
        selected_font TEXT NOT NULL default ''
    );
");
$installer->endSetup();
