<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE {$this->getTable('mst_pdp_admin_template')} ADD COLUMN(
        template_name varchar(500) NOT NULL default '',
        template_thumbnail varchar(500) NOT NULL default '',
        is_default smallint(2) NOT NULL DEFAULT '0',
        template_position int(11) NOT NULL DEFAULT '0'
    );
");
$installer->endSetup();
