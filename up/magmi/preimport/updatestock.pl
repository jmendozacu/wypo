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
                                                $sth->execute();
                                                my $ref = $sth->fetchrow_hashref();
						$cambio = $ref->{'rate'};

my $csv = Text::CSV_XS->new({ binary => 1});
$csv->eol ("\n");
open my $fh, "<$ARGV[0]"; 

print "price,sku,qty,cost,special_price,store,mercadolivre_price,visibility,precio_neto,tb_mercadolivre_price\n";

my $j = 0;
while (my $row = $csv->getline ($fh)) {
		$row->[4] =~ s/,//g;
               $row->[7] =~ s/"/'/g;$row->[6] =~ s/"/'/g;;$row->[6] =~ s/,/ -/g;
		if(($row->[0] eq "") || ($row->[0] eq "0") || ($row->[0] < 0) || ($row->[2] eq "" )) {} else {
               if($row->[1] eq "") {} else {
	       if($row->[3] eq "") {} else {
 
                                            if($row->[6] eq "CL")                         {
                                            	$dsn= "DBI:mysql:database=peta3;host=192.168.0.202";
                                            	$dbh= DBI->connect($dsn,"peta2" ,"K94679nM");
                                            	my $sth = $dbh->prepare("SELECT rate FROM `directory_currency_rate` WHERE `currency_to` = 'CLP'");
                                            	$sth->execute();
					    	my $ref = $sth->fetchrow_hashref();$cambio = $ref->{'rate'};if($cambio < 400) {} else {
                                                $costo = ceil($row->[0]/$cambio);
						$monto= $row->[0];$row->[0]=$monto/$cambio;
                                            	$moneda = $row->[0]; $row->[0] = ceil($moneda * $row->[1] * $row->[2]);
						$precio_neto = ceil ($row->[0]*$cambio);
                                            	binmode(STDOUT, ":utf8"); if($row->[0] < $row->[6]) {} else {
								$precio_mercadolibre = ceil($row->[0]*$cambio*1.19*1.05);
								print "$row->[0],$row->[3],$row->[4],$costo,,\"admin,base\",$precio_mercadolibre,4,$precio_neto,$precio_mercadolibre\n";
}}}  else {

                                            if($row->[2] eq "Quintec" || $row->[2] eq "Quintec <") {
                                                $dsn= "DBI:mysql:database=peta3;host=192.168.0.202";
                                            	$dbh= DBI->connect($dsn,"peta2" ,"K94679nM");
                                            	my $sth = $dbh->prepare("SELECT rate FROM `directory_currency_rate` WHERE `currency_to` = 'CLP'");
                                            	$sth->execute();
                                            	my $ref = $sth->fetchrow_hashref();$cambio = $ref->{'rate'};if($cambio < 400) {} else {
						$costo = ceil($row->[0]/$cambio);
						$monto= $row->[0];$row->[0]=$monto/$cambio;
                                            	$moneda = $row->[0]; $row->[0] = ceil($moneda * $row->[1]);
						$precio_mercadolibre = ceil($row->[0]*$cambio*1.19*1.04);
						$precio_neto = ceil ($row->[0]*$cambio);
                                            	binmode(STDOUT, ":utf8"); print "$row->[0],$row->[3],$row->[4],$costo,,\"admin,base\",$precio_mercadolibre,4,$precio_neto,$precio_mercadolibre\n";
												}}										    
if($row->[6] eq "Intcomex") {
$costo = ceil($row->[0]);
$monto = ceil($costo*$row->[1]);
$precio_mercadolibre = ceil($monto*1.05*1.19*$cambio);
$precio_neto = ceil ($monto*$cambio);
binmode(STDOUT, ":utf8"); print "$monto,$row->[3],$row->[4],$costo,,\"admin,base\",$precio_mercadolibre,4,$precio_neto,$precio_mercadolibre\n";
}
else {
	
              if($row->[0] eq "0") {} else { if ($row->[4] eq "") {} else {	
		if($row->[2] eq "Quintec") {} else {
          	$costo = $row->[0];
		$moneda = $row->[0]; $row->[0] = ceil($moneda * $row->[1] * $row->[2] * 1.20); $special_price = ceil($row->[0] / 1.20); 
          	binmode(STDOUT, ":utf8"); 
								$precio_neto = ceil($row->[0]*$cambio/1.20);
								$precio_mercadolibre = ceil($special_price*$cambio*1.19*1.05);
     							     print "$row->[0],$row->[3],$row->[4],$costo,$special_price,\"admin,base\",$precio_mercadolibre,4,$precio_neto,$precio_mercadolibre\n";
									   
					  }}}}}}}}
		$j++;

		}

$csv->eof or $csv->error_diag ();
close $fh;

#print "[++] TOTAL: $j\n";
