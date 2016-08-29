#!/usr/bin/perl
#use strict;
use utf8;
use Text::CSV_XS;
use open ":encoding(utf8)";
use POSIX;
use DBI;
my $i = 0;

					    $dsn= "DBI:mysql:database=peta3;host=192.168.0.202";
                                            $dbh= DBI->connect($dsn,"peta2" ,"K94679nM");
                                            my $sth = $dbh->prepare("SELECT rate FROM `directory_currency_rate` WHERE `currency_to` = 'CLP'");
                                            $sth->execute();my $ref = $sth->fetchrow_hashref();$cambio = $ref->{'rate'};

my $csv = Text::CSV_XS->new({ binary => 1});
$csv->eol ("\n");
open my $fh, "<$ARGV[0]"; 

print "price,sku,qty,name,partnumber,attribute_set,category_ids,weight,description,image,small_image,thumbnail,manufacturer,cost,store,is_in_stock,status,tax_class_id,visibility,type,short_description,specifications,media_gallery,distribuidor,hide_default_stock_status,custom_stock_status,precio_neto,tb_mercadolivre_price\n";

my $j = 0;
while (my $row = $csv->getline ($fh)) { if($row->[0] eq "" || $row->[0] eq "0" ) {} else { if($row->[1] eq "") {} else {if($row->[2] eq "") {} else { if($row->[3] eq "") {} else { if($row->[4] eq "") {} else { if($row->[5] eq "") {} else {
	   $row->[5] =~ s/"/'/g;$row->[6] =~ s/"/'/g;$row->[11] =~ s/"/'/g;$row->[17] =~ s/"/'/g;$row->[15] =~ s/"/'/g;$row->[5]  =~ s/,/;/g;$row->[11] =~ s/,/;/g;$row->[17] =~ s/,/;/g;  $row->[15] =~ s/,/;/g;$row->[16] =~ s/"/'/g;$row->[16] =~ s/"/'/g;$row->[13] =~ s/HEWLETT-PACKARD/HP/g;$row->[13] =~ s/QNAP SYSTEMS, INC./QNAP/g;$row->[13] =~ s/MICRO STAR INTERNATIONAL/MSI/g;$row->[13] =~ s/KINGSTON VALUERAM/KINGSTON/g;$row->[13] =~ s/GIGA-BYTE/GIGABYTE/g;$row->[13] =~ s/FORZA POWER TECHNOLOGIES/FORZA/g;$row->[13] =~ s/KLIP XTREME/KLIPX/g;$row->[13] =~ s/NOKIA CELLULAR PHONES/NOKIA/g;$row->[13] =~ s/GENERIC - IAS PRODUCT DEVELOPMENT/GENERIC/g; $row->[18] =~ s/ProductImageCompressAll35/NeweggImage\/productimage/g;$row->[18] =~ s/"//g;$row->[19] =~ s/"//g;$row->[20] =~ s/"//g;$row->[21] =~ s/"//g;$row->[22] =~ s/"//g;$row->[23] =~ s/"//g;$row->[24] =~ s/"//g;$row->[25] =~ s/"//g;$row->[26] =~ s/"//g;$row->[27] =~ s/"//g;$row->[28] =~ s/"//g;$row->[13] =~ s/Hewlett-Packard/HP/g;$row->[13] =~ s/Elitegroup Computer Systems/ECS/g;$row->[13] =~ s/Micro Star International/MSI/g;$row->[13] =~ s/Hewlett Packard/HP/g;$row->[13] =~ s/Klip Xtreme/KLIPX/g;$row->[13] =~ s/NEC DISPLAY SOLUTIONS/NEC/g;$row->[13] =~ s/NEC DISPLAY/NEC/g; $row->[14] =~ s/<span class='highlights-see-all'>See All \+<\/span>//g;


$row->[13] =~ s/a/A/g;$row->[13] =~ s/b/B/g;$row->[13] =~ s/c/C/g;$row->[13] =~ s/d/D/g;$row->[13] =~ s/e/E/g;$row->[13] =~ s/f/F/g;$row->[13] =~ s/g/G/g;$row->[13] =~ s/h/H/g;$row->[13] =~ s/i/I/g;$row->[13] =~ s/j/J/g;$row->[13] =~ s/k/K/g;$row->[13] =~ s/l/L/g;$row->[13] =~ s/m/M/g;$row->[13] =~ s/n/N/g;$row->[13] =~ s/o/O/g;$row->[13] =~ s/p/P/g;$row->[13] =~ s/q/Q/g;$row->[13] =~ s/r/R/g;$row->[13] =~ s/s/S/g;$row->[13] =~ s/t/T/g;$row->[13] =~ s/u/U/g;$row->[13] =~ s/v/V/g;$row->[13] =~ s/w/W/g;$row->[13] =~ s/x/X/g;$row->[13] =~ s/y/Y/g;$row->[13] =~ s/z/Z/g;$row->[13] =~ s/LG ELECTRONICS/LG/g;

                                            $precio_pesos = ceil ($row->[0] * $row->[1] * $cambio * 1.19);

                                            if($row->[10] eq "Bhphoto") {
						if ($row->[12] eq "" || $row->[17] eq "" || $row->[12] eq "Apple") {} else {
									$moneda = $row->[0]; 

							$costo = $row->[0];
                                                                                                                                        #Row 9 es el peso del producto....luego la suma es un amortiguador
                                        if($row->[0] < 3) {$price = $row->[0]; $row->[0] = ceil($price * ( 2.3 + $row->[9] * 0.05));} else {
                                        if($row->[0] < 10) {$price = $row->[0]; $row->[0] = ceil($price * ( 1.75 + $row->[9] * 0.05));} else {
                                        if($row->[0] < 30) {$price = $row->[0]; $row->[0] = ceil($price * ( 1.35 + $row->[9] * 0.05));} else {
                                        if($row->[0] < 80) {$price = $row->[0]; $row->[0] = ceil($price * ( 1.30 + $row->[9] * 0.05));} else {
                                        if($row->[0] < 150) {$price = $row->[0]; $row->[0] = ceil($price * ( 1.28 + $row->[9] * 0.05));} else {
                                        if($row->[0] < 250) {$price = $row->[0]; $row->[0] = ceil($price * ( 1.25 + $row->[9] * 0.05));} else {
                                        if($row->[0] < 350) {$price = $row->[0]; $row->[0] = ceil($price * ( 1.23 + $row->[9] * 0.05));} else {
                                        if($row->[0] < 450) {$price = $row->[0]; $row->[0] = ceil($price * ( 1.21 + $row->[9] * 0.05));} else {
                                        if($row->[0] < 850) {$price = $row->[0]; $row->[0] = ceil($price * ( 1.15 + $row->[9] * 0.05));} else {
                                        if($row->[0] < 1550) {$price = $row->[0]; $row->[0] = ceil($price * ( 1.13 + $row->[9] * 0.05));} else {
                                        if($row->[0] < 550000000000) {$price = $row->[0]; $row->[0] = ceil($price * ( 1.10 + $row->[9] * 0.05));}}}}}}}}}}}
									
		$precio_mercadolibre = ceil ($row->[0] * $cambio * 1.19*1.05);
		$precio_neto = ceil ($row->[0] * $cambio);
		binmode(STDOUT, ":utf8"); print "$row->[0],$row->[3],3,\"$row->[5]\",$row->[6],$row->[7],$row->[8],$row->[9],\"$row->[14]\",$row->[17],$row->[17],$row->[17],$row->[12],$costo,base,1,Enabled,Productos Afectos a IVA,3,simple,\"$row->[14]\",\"$row->[15]\",\"$row->[24]\",Bhphoto,1,10,$precio_neto,$precio_mercadolibre\n";	

									     }

                                                       }                } 
				}	}
       $j++;
		}}}}
$csv->eof or $csv->error_diag ();
close $fh;

#print "[++] TOTAL: $j\n";
