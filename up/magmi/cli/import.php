<?php
        // Infomation.
        $host           =       '192.168.0.202';
        $user           =       'peta2';
        $pass           =       'K94679nM';
        $database       =       'wypo';
        // Connect to the mysql database server.
        $connect = @mysql_connect ($host, $user, $pass);

if ( $connect )
        {
                // Execute Script => ONLY SET qty=0 FOR Vendor which is importing CSV file.
                if ( ! @mysql_query ( "UPDATE wypo.cataloginventory_stock_item SET qty = 0 where product_id not in (SELECT distinct e.entity_id FROM wypo.catalog_product_entity AS e LEFT JOIN wypo.catalog_product_entity_int AS table_distribuidor ON (table_distribuidor.entity_id = e.entity_id) AND (table_distribuidor.attribute_id='118') WHERE (e.entity_type_id = '4') AND  table_distribuidor.value is null OR table_distribuidor.value = '1069');"))
                {
                        die ( mysql_error() );
                }
                else {
                        echo "Ok al llevar stock a 0.";
                }
        }
        else {
                trigger_error ( mysql_error(), E_USER_ERROR );
        }
chdir('/home/www.wypo.cl/html/up/magmi/cli/');

$output3 = shell_exec('php magmi.cli.php -profile=vendor -mode=update');  //Use profile "vendor_name", same as importing CSV-File.

        if ( $connect )
        {
                // ejecutar script.
                if ( ! @mysql_query ( "UPDATE wypo.cataloginventory_stock_item SET is_in_stock=0 WHERE qty=0;")) //Just for Vendor !
                {
                        die ( mysql_error() );
                }
                else {
                        echo "ok. en llevar productos sin stock (qty=0) a Disponibilidad=Agotado";
                }
        }
        else {
                trigger_error ( mysql_error(), E_USER_ERROR );
        }

if ( $connect )
        {
                // ejecutar script. TODOS LOS PRODUCTOS CON STOCK 0 y Catalog & Search SE CONVIERTE EN BUSCAR (VALUE =3) => Just for Vendor !
                if ( ! @mysql_query ( "UPDATE wypo.catalog_product_entity_int,wypo.cataloginventory_stock_item SET catalog_product_entity_int.value = 3 WHERE cataloginventory_stock_item.product_id = catalog_product_entity_int.entity_id AND cataloginventory_stock_item.qty = 0 and attribute_id = 91 and catalog_product_entity_int.store_id=0 AND catalog_product_entity_int.value = 4;"))
                {
                        die ( mysql_error() );
                }
                else {
                       echo "<br>ok. en convertir todos los productos sin stock a Buscar con qty=0.";
                }
        }
        else {
                trigger_error ( mysql_error(), E_USER_ERROR );
        }




?>
