<?php
require_once(Mage::getModuleDir('controllers','Mage_Adminhtml').DS.'Sales'.DS.'Order'.DS.'InvoiceController.php');
$apiPath = Mage::getBaseDir('lib').DS.'SuperFacturaAPI'.DS.'api.php';
require_once($apiPath);
class Ecom_Sales_Adminhtml_Sales_Order_InvoiceController extends Mage_Adminhtml_Sales_Order_InvoiceController {
    public function saveAction()
    {
		$data = $this->getRequest()->getPost('invoice');
        $orderId = $this->getRequest()->getParam('order_id');

        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $invoice = $this->_initInvoice();
            if ($invoice) {

                if (!empty($data['capture_case'])) {
                    $invoice->setRequestedCaptureCase($data['capture_case']);
                }

                if (!empty($data['comment_text'])) {
                    $invoice->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                }

                $invoice->register();

                if (!empty($data['send_email'])) {
                    $invoice->setEmailSent(true);
                }

                $invoice->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $invoice->getOrder()->setIsInProcess(true);

                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $shipment = false;
                if (!empty($data['do_shipment']) || (int) $invoice->getOrder()->getForcedDoShipmentWithInvoice()) {
                    $shipment = $this->_prepareShipment($invoice);
                    if ($shipment) {
                        $shipment->setEmailSent($invoice->getEmailSent());
                        $transactionSave->addObject($shipment);
                    }
                }
                $transactionSave->save();

                if (isset($shippingResponse) && $shippingResponse->hasErrors()) {
                    $this->_getSession()->addError($this->__('The invoice and the shipment  have been created. The shipping label cannot be created at the moment.'));
                } elseif (!empty($data['do_shipment'])) {
                    $this->_getSession()->addSuccess($this->__('The invoice and shipment have been created.'));
                } else {
                    $this->_getSession()->addSuccess($this->__('The invoice has been created.'));
                }

                // send invoice/shipment emails
                $comment = '';
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
                try {
					/* PDF from API */
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
						'NmbItem' => $item->getName(),
						'DscItem' => $invoiceOpt,
						'QtyItem' => (int)$item->getQty(),
						'PrcItem' => $item->getPrice(),
						);
						$b++;
					}
					if($invoice->getShippingAmount() && $invoice->getShippingAmount()>0){
						$product_data[$b+1]= array(
						'NmbItem' => $order->getShippingDescription(),
						'DscItem' => '',
						'QtyItem' => 1,
						'PrcItem' => $invoice->getShippingAmount(),
						);
					}
						$datos = array(
						'Encabezado' => array(
						'IdDoc' => array(
						'TipoDTE' => 33,
						'FchEmis' => date('Y-m-d', strtotime($order->getCreatedAt())),
						),
						'Emisor' => array(
						'RUTEmisor' => '76622517-9',
						),
						'Receptor' => array(
						'RUTRecep' => $billingAddress->getVatId(),
						'RznSocRecep' => $billingAddress->getCompany(),
						'GiroRecep' => $billingAddress->getFax(),
						'DirRecep' => $billingAddress->getStreet1().', '.$billingAddress->getStreet2().', '.$billingAddress->getStreet3(),
						'CmnaRecep' => $billingAddress->getRegion(),
						'CiudadRecep' => $billingAddress->getCity(),
						),
						),
						'Detalles' => $product_data,
						);
					$api = new SuperFacturaAPI('nm@wypo.cl', 'K94679nM');
					$resultado = $api->SendDTE(
					$datos,
					'cer',
					array('getPDF' => true)
					);
					if($resultado['ok']){
						if($resultado['folio']){
							$pdf = $resultado['pdf'];
							file_put_contents(Mage::getBaseDir('media').'/invoices/'.$resultado['folio'].'.pdf', $pdf);
							$invoice->setIncrementId($resultado['folio']);
						}		
					}
					/* API PDF END */
                    $invoice->sendEmail(!empty($data['send_email']), $comment);
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError($this->__('Unable to send the invoice email.'));
                }
                if ($shipment) {
                    try {
                        $shipment->sendEmail(!empty($data['send_email']));
                    } catch (Exception $e) {
                        Mage::logException($e);
                        $this->_getSession()->addError($this->__('Unable to send the shipment email.'));
                    }
                }
                Mage::getSingleton('adminhtml/session')->getCommentText(true);
				if($resultado['folio']){
					$resource = Mage::getSingleton('core/resource');
					$writeConnection = $resource->getConnection('core_write');
					$query = 'UPDATE sales_flat_invoice SET increment_id='.$resultado['folio'].' WHERE entity_id='.$invoice->getId();
					$queryone = 'UPDATE sales_flat_invoice_grid SET increment_id='.$resultado['folio'].' WHERE entity_id='.$invoice->getId();
					try{
						$writeConnection->query($query);
						$writeConnection->query($queryone);
					}catch(Exception $e){
						$this->_getSession()->addError($e->getMessage());
					}
				}
                $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
            } else {
                $this->_redirect('*/*/new', array('order_id' => $orderId));
            }
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Unable to save the invoice.'));
            Mage::logException($e);
        }
        $this->_redirect('*/*/new', array('order_id' => $orderId));
    }
	
}