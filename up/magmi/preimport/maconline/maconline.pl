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
$row->[1] =~ s/"/''/g;$row->[1] =~ s/,/./g;$row->[5] =~ s/"/''/g;$row->[6] =~ s/"/''/g;			
			$precio= ceil($row->[0] / 1.19);
			$costo = $precio;
                        #Row 9 es el peso del producto....luego la suma es un amortiguador
binmode(STDOUT, ":utf8");
print "$precio,$precio,$costo,$row->[2],$row->[2],3,3,\"$row->[1]\",25,2,$row->[3],$row->[4],$row->[4],$row->[4],Apple,1,IVA,4,\"$row->[6]\",\"$row->[5]\"\n";
									     }
                                                       }    
       $j++;

$csv->eof or $csv->error_diag ();
close $fh;

#print "[++] TOTAL: $j\n";
