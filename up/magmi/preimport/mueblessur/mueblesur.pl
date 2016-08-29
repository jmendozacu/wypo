#!/usr/bin/perl
#use strict;
use utf8;
use Text::CSV_XS;
use open ":encoding(utf8)";
use POSIX;
use DBI;
my $i = 0;

my $csv = Text::CSV_XS->new({ binary => 1});
$csv->eol ("\n");
open my $fh, "<$ARGV[0]"; 

print "price,vendor_price,vendor_cost,sku,vendor_sku,qty,stock_qty,name,udropship_vendor,weight,categoryname,image,small_image,thumbnail,manufacturer,status,tax_class_id,visibility,short_description,description\n";

my $j = 0;
while (my $row = $csv->getline ($fh)) { if($row->[0] eq "" || $row->[0] eq "0" || $row->[1] eq "" || $row->[2] eq "" || $row->[3] eq "" ||$row->[4] eq "" || $row->[5] eq "") {} else {

			$costo = $row->[0];
                        #Row 9 es el peso del producto....luego la suma es un amortiguador
                                        if($row->[0] < 10000) {$price = $row->[0]; $row->[0] = ceil($price * 1.45 );} else {
                                        if($row->[0] < 25000) {$price = $row->[0]; $row->[0] = ceil($price * 1.30 );} else {
                                        if($row->[0] < 50000) {$price = $row->[0]; $row->[0] = ceil($price * 1.25 );} else {
                                        if($row->[0] < 100000) {$price = $row->[0]; $row->[0] = ceil($price * 1.20 );} else {
                                        if($row->[0] < 550000000000) {$price = $row->[0]; $row->[0] = ceil($price * 1.15 );
														}}}}}
		binmode(STDOUT, ":utf8"); 
		print "$row->[0],$row->[0],$costo,$row->[2],$row->[2],5,5,\"$row->[3]\",5,100,\"$row->[4]\",$row->[5],$row->[5],$row->[5],,1,IVA,4,\"$row->[6]\",\"$row->[9]\"\n";

									     }

                                                       }    
					
       $j++;

$csv->eof or $csv->error_diag ();
close $fh;

#print "[++] TOTAL: $j\n";
