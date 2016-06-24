<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE {$this->getTable('mst_pdp_multisides_colors_images')} ADD filename_thumbnail varchar(500);
    ALTER TABLE {$this->getTable('mst_pdp_multisides_colors_images')} ADD overlay_thumbnail varchar(500);
");
$installer->endSetup();