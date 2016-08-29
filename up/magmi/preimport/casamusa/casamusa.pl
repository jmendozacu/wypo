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

print "price,vendor_price,vendor_cost,sku,vendor_sku,qty,stock_qty,name,udropship_vendor,weight,categoryname,image,small_image,thumbnail,manufacturer,status,tax_class_id,visibility,short_description,description,custom_stock_status,hide_default_stock_status\n";

my $j = 0;
while (my $row = $csv->getline ($fh)) { if($row->[0] eq "" || $row->[0] eq "0" || $row->[1] eq "" || $row->[2] eq "" || $row->[3] eq "" ||$row->[4] eq "" || $row->[5] eq "") {} else {

			$costo = $row->[9];
binmode(STDOUT, ":utf8");
print "$row->[0],$row->[0],$costo,$row->[1],$row->[1],10,10,\"$row->[2]\",26,1,$row->[4],http://www.casamusa.cl/media/catalog/product/$row->[8],http://www.casamusa.cl/media/catalog/product/$row->[8],http://www.casamusa.cl/media/catalog/product/$row->[8],$row->[10],1,IVA,4,\"$row->[6]\",\"$row->[5]\",Despacho en 24 hrs.,1\n";
									     }
                                                       }    
       $j++;

$csv->eof or $csv->error_diag ();
close $fh;

#print "[++] TOTAL: $j\n";
