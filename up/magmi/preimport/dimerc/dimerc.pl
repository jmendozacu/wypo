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

print "price,vendor_price,vendor_cost,sku,vendor_sku,qty,stock_qty,name,udropship_vendor,weight,categoryname,image,small_image,thumbnail,manufacturer,status,tax_class_id,visibility,short_description,description,\"tier_price:_all_\",custom_stock_status,hide_default_stock_status\n";

my $j = 0;
while (my $row = $csv->getline ($fh)) { if($row->[0] eq "" || $row->[0] eq "0" || $row->[1] eq "" || $row->[2] eq "" || $row->[3] eq "" ||$row->[4] eq "" || $row->[5] eq "") {} else {

$row->[5] =~ s/a/A/g;$row->[5] =~ s/b/B/g;$row->[5] =~ s/c/C/g;$row->[5] =~ s/d/D/g;$row->[5] =~ s/e/E/g;$row->[5] =~ s/f/F/g;$row->[5] =~ s/g/G/g;$row->[5] =~ s/h/H/g;$row->[5] =~ s/i/I/g;$row->[5] =~ s/j/J/g;$row->[5] =~ s/k/K/g;$row->[5] =~ s/l/L/g;$row->[5] =~ s/m/M/g;$row->[5] =~ s/n/N/g;$row->[5] =~ s/o/O/g;$row->[5] =~ s/p/P/g;$row->[5] =~ s/q/Q/g;$row->[5] =~ s/r/R/g;$row->[5] =~ s/s/S/g;$row->[5] =~ s/t/T/g;$row->[5] =~ s/u/U/g;$row->[5] =~ s/v/V/g;$row->[5] =~ s/w/W/g;$row->[5] =~ s/x/X/g;$row->[5] =~ s/y/Y/g;$row->[5] =~ s/z/Z/g;

			$costo = $row->[0];
                        #Row 9 es el peso del producto....luego la suma es un amortiguador
if($row->[0] < 100) {$price = $row->[0]; $row->[0] = ceil($price * 1.15);$qty=2000;$cant1=100;$cant2=200;$cant3=500;$precio1=$row->[0]*0.95;$precio2=$row->[0]*0.92;$precio3=$row->[0]*0.90;}else {
if($row->[0] < 300) {$price = $row->[0]; $row->[0] = ceil($price * 1.15);$qty=2000;$cant1=100;$cant2=200;$cant3=500;$precio1=$row->[0]*0.95;$precio2=$row->[0]*0.92;$precio3=$row->[0]*0.90;} else {
if($row->[0] < 900) {$price = $row->[0]; $row->[0] = ceil($price * 1.15 );$qty=1000;$cant1=50;$cant2=100;$cant3=200;$precio1=$row->[0]*0.95;$precio2=$row->[0]*0.92;$precio3=$row->[0]*0.90;} else {
if($row->[0] < 2000){$price = $row->[0]; $row->[0] = ceil($price * 1.15 );$qty=500;$cant1=40;$cant2=90; $cant3=150;$precio1=$row->[0]*0.95;$precio2=$row->[0]*0.92;$precio3=$row->[0]*0.90;} else {
if($row->[0] < 4000){$price = $row->[0]; $row->[0] = ceil($price * 1.15 );$qty=500;$cant1=30;$cant2=80; $cant3=120;$precio1=$row->[0]*0.95;$precio2=$row->[0]*0.92;$precio3=$row->[0]*0.90;} else {
if($row->[0] < 7000){$price = $row->[0]; $row->[0] = ceil($price * 1.10 );$qty=100;$cant1=20;$cant2=50; $cant3=100;$precio1=$row->[0]*0.95;$precio2=$row->[0]*0.92;$precio3=$row->[0]*0.90;} else {
if($row->[0] < 10000){$price= $row->[0]; $row->[0] = ceil($price * 1.10 );$qty=100;$cant1=15;$cant2=30; $cant3=50;$precio1= $row->[0]*0.95;$precio2=$row->[0]*0.92;$precio3=$row->[0]*0.90;} else {
if($row->[0] < 15000){$price= $row->[0]; $row->[0] = ceil($price * 1.10 );$qty=100;$cant1=15;$cant2=30; $cant3=40;$precio1= $row->[0]*0.95;$precio2=$row->[0]*0.92;$precio3=$row->[0]*0.90;} else {
if($row->[0] < 20000){$price= $row->[0]; $row->[0] = ceil($price * 1.10 );$qty=50;$cant1=10;$cant2=20; $cant3=35;$precio1= $row->[0]*0.95;$precio2=$row->[0]*0.92;$precio3=$row->[0]*0.90;} else {
if($row->[0] < 25000){$price= $row->[0]; $row->[0] = ceil($price * 1.10 );$qty=40;$cant1=10;$cant2=20; $cant3=30;$precio1= $row->[0]*0.95;$precio2=$row->[0]*0.92;$precio3=$row->[0]*0.90;} else {
if($row->[0]<55000000){$price=$row->[0]; $row->[0] = ceil($price * 1.10 );$qty=10;$cant1=5;$cant2=10; $cant3=15;$precio1= $row->[0]*0.95;$precio2=$row->[0]*0.92;$precio3=$row->[0]*0.90; }}}}}}}}}}}
binmode(STDOUT, ":utf8");
$precio1=ceil($precio1);
$precio2=ceil($precio2);
$precio3=ceil($precio3); 
print "$row->[0],$row->[0],$costo,$row->[2],$row->[2],$qty,$qty,\"$row->[3]\",2,1,$row->[7],$row->[9],$row->[9],$row->[9],$row->[5],1,IVA,4,\"$row->[3]\",\"$row->[3]\",:,Despacho en 24 hrs.,1\n";
#print "$row->[0],$row->[0],$costo,$row->[2],$row->[2],$qty,$qty,\"$row->[3]\",2,1,$row->[7],$row->[9],$row->[9],$row->[9],$row->[5],1,IVA,4,\"$row->[3]\",\"$row->[3]\",\"$cant1:-5%;$cant2:-10%;$cant3:-15%\",Despacho en 24 hrs.,1\n";
#print "$row->[0],$row->[0],$costo,$row->[2],$row->[2],$qty,$qty,\"$row->[3]\",2,1,$row->[7],$row->[9],$row->[9],$row->[9],$row->[5],1,IVA,4,\"$row->[3]\",\"$row->[3]\",\"$cant1:__MAGMI_DELETE__;$cant2:__MAGMI_DELETE__;$cant3:__MAGMI_DELETE__\",Despacho en 24 hrs.,1\n";
									     }
                                                       }    
       $j++;

$csv->eof or $csv->error_diag ();
close $fh;

#print "[++] TOTAL: $j\n";
