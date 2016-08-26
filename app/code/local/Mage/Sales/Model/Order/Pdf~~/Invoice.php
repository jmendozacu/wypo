<?php
$apiPath = $_SERVER['DOCUMENT_ROOT'].'/SuperFacturaAPI.php';
require_once($apiPath);
class Mage_Sales_Model_Order_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Abstract
{
    /**
     * Draw header for item table
     *
     * @param Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y -15);
        $this->y -= 10;
        $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));

        //columns headers
        $lines[0][] = array(
            'text' => Mage::helper('sales')->__('Products'),
            'feed' => 35
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('SKU'),
            'feed'  => 290,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Qty'),
            'feed'  => 435,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Price'),
            'feed'  => 360,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Tax'),
            'feed'  => 495,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Subtotal'),
            'feed'  => 565,
            'align' => 'right'
        );

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 5
        );

        $this->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Return PDF document
     *
     * @param  array $invoices
     * @return Zend_Pdf
     */
    public function getPdf($invoices = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
                Mage::app()->setCurrentStore($invoice->getStoreId());
            }
            $page  = $this->newPage();
            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId())
            );
            /* Add document text and number */
            $this->insertDocumentNumber(
                $page,
                Mage::helper('sales')->__('Invoice # ') . $invoice->getIncrementId()
            );
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($invoice->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            $this->insertTotals($page, $invoice);
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
        }
        $this->_afterGetPdf();
		return $pdf;
    }
	
	public function getFacturaPdf($invoices = array())
    {
		foreach ($invoices as $invoice) {
			if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
                Mage::app()->setCurrentStore($invoice->getStoreId());
            }
            $order = $invoice->getOrder();
			$billingAddress = $order->getBillingAddress();
			$shippingAddress = $order->getShippingAddress();
			$b=0;
			foreach ($invoice->getAllItems() as $item){
				if ($item->getOrderItem()->getParentItem()) {
					continue;
				}
				$invoiceOpt='';
				$options = $item->getOrderItem()->getProductOptions();
				if(isset($options['additional_options'])){
					 foreach ($options['additional_options'] as $option) {
						$invoiceOpt .= '<b>'.$option['label'].':</b> '.$option['value'].'<br />';
					}
				}
				$product_data[$b]= array(
					'NmbItem' => $item->getName(), //Name of Producto of Line 1
					'DscItem' => $invoiceOpt,
					'QtyItem' => (int)$item->getQty(), //Qty bought of Product of Line 1
					'PrcItem' => $item->getPrice(), //Price without Tax of Product of Line 1
				);
				$b++;
			}
			$product_data[$b+1]= array(
				'NmbItem' => $order->getShippingDescription(),
				'DscItem' => '',
				'QtyItem' => 1,
				'PrcItem' => $invoice->getShippingAmount(),
			);
			$datos = array(
				'Encabezado' => array(
					'IdDoc' => array(
					'TipoDTE' => 33,
					'FchEmis' => date('Y-m-d', strtotime($order->getCreatedAt())), //Order Date
					),
					'Emisor' => array(
					'RUTEmisor' => '76622517-9',
					),
					'Receptor' => array(
					'RUTRecep' => $billingAddress->getVatId(), //VAT from Billing Address
					'RznSocRecep' => $billingAddress->getCompany(), //Company Name from Billing Address
					'GiroRecep' => $billingAddress->getFax(),					//Fax from Billing Address
					'DirRecep' => $billingAddress->getStreet1().', '.$billingAddress->getStreet2().', '.$billingAddress->getStreet3(), //Address lines 1,2 & 3 from Billing Address
					'CmnaRecep' => $billingAddress->getRegion(), //State from Billing Address
					'CiudadRecep' => $billingAddress->getCity(), //City from Billing Address
					),
				),
				'Detalles' => $product_data,
			);
		}
		$api = new SuperFacturaAPI('nm@wypo.cl', 'K94679nM');
		$resultado = $api->SendDTE(
			$datos,
			'cer',
			array('getPDF' => true)
		);
		
		if($resultado['ok']){
			$pdf = $resultado['pdf'];
			if($resultado['folio'] && strlen($pdf)){
				return $pdf;
			}
		}
		return false;
	}

    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }
	
}
