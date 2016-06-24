<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE {$this->getTable('mst_pdp_fonts')} ADD COLUMN(
        original_filename varchar(500) NOT NULL default '',
        display_text TEXT NOT NULL default '',
        status smallint(6) NOT NULL DEFAULT '1',
        font_position smallint(6) NOT NULL DEFAULT '0'
    );
");
$installer->endSetup();
