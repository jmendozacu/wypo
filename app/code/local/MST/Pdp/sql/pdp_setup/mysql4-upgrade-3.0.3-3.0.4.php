<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE {$this->getTable('mst_pdp_multisides')} ADD COLUMN(
        canvassize varchar(50) NOT NULL default '',
        canvaswidth varchar(50) NOT NULL default '',
        canvasheight varchar(50) NOT NULL default '',
        use_mask int(2) NOT NULL DEFAULT '2',
        mask_photo varchar(500) NOT NULL default ''
    );
");
$installer->endSetup(); 
