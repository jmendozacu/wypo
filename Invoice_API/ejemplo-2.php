<?php

//ERROR: No se pudo firmar DTE (necesario para generar el PDF)

header('Content-type: text/html; charset=iso-8859-1'); // Opcional (sólo para asegurar charset correcto)

require_once('SuperFacturaAPI/api.php');
/* 
echo $_SERVER['DOCUMENT_ROOT'];
exit; */

// 1) Generate Array

$datos = array(
	'Encabezado' => array(
		'IdDoc' => array(
			'TipoDTE' => 33,
			'FchEmis' => '2016-08-23', //Order Date
		),
		'Emisor' => array(
			'RUTEmisor' => '76622517-9',
			
		),
		'Receptor' => array(
			'RUTRecep' => '10268889-9', 					//VAT from Billing Address
			'RznSocRecep' => 'Particular',				//Company Name from Billing Address
			'GiroRecep' => 'Giro',					//Fax from Billing Address
			'DirRecep' => 'Dirección',				//Address lines 1,2 & 3 from Billing Address
			'CmnaRecep' => 'Comuna',				//State from Billing Address
			'CiudadRecep' => 'Ciudad',				//City from Billing Address
		),
		
	),
	'Detalles' => array(
		array(
			'NmbItem' => 'Item 1',					//Name of Producto of Line 1
			'DscItem' => 'Descripción del item 1',
			'QtyItem' => 3,							//Qty bought of Product of Line 1
			'PrcItem' => 100,						//Price without Tax of Product of Line 1
		),
		array(
			'NmbItem' => 'Item 2',					//Name of Product of Line 2
			'DscItem' => 'Descripción del item 2',		
			'QtyItem' => 5,							//QTY bought of product of Line 2
			'PrcItem' => 65,						//Price without tax of Product of Line 2
		)
	),
	
);

// 2) Use API to Generate and Sent

$api = new SuperFacturaAPI('nm@wypo.cl', 'K94679nM');

$resultado = $api->SendDTE(
	$datos,	// Document Data
	'cer',	// Working environment: 'pro' = production y 'cer' = sandbox
	array(	// 
		'getPDF' => true	// Get the PDF document
	)
);

// 3) Process API exit

if($resultado['ok']) {
	// Get content of PDF document
	$pdf = $resultado['pdf'];
	//file_put_contents('/home/www.wypo.cl/html/Invoice_API/pdf123.pdf', $pdf);
	$pdfCedible = $resultado['pdfCedible'];
	
	echo "Folio: {$resultado['folio']}<br>"; // "folio" is the Invoice Number of the generated PDF document. Can you set this Invoice Number (folio) instead of the Standard Magento Invoice Number ?
	
	$size = strlen($pdf);
	echo "PDF: ($size bytes)<br>";

	if($pdfCedible) {
		$sizeCedible = strlen($pdfCedible);
		echo "PDF Cedible: ($sizeCedible bytes)<br>";
	}
	
	die('ok');

} else {
	die('Error');
}
