<?php
require_once(Mage::getModuleDir('controllers','Mage_Adminhtml').DS.'Sales'.DS.'Order'.DS.'CreditmemoController.php');
$apiPath = Mage::getBaseDir('lib').DS.'SuperFacturaAPI'.DS.'api.php';
require_once($apiPath);
class Ecom_Sales_Adminhtml_Sales_Order_CreditmemoController extends Mage_Adminhtml_Sales_Order_CreditmemoController {
	public function saveAction()
    {
        $data = $this->getRequest()->getPost('creditmemo');
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $creditmemo = $this->_initCreditmemo();
            if ($creditmemo) {
                if (($creditmemo->getGrandTotal() <=0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                    Mage::throwException(
                        $this->__('Credit memo\'s total must be positive.')
                    );
                }

                $comment = '';
                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                    if (isset($data['comment_customer_notify'])) {
                        $comment = $data['comment_text'];
                    }
                }

                if (isset($data['do_refund'])) {
                    $creditmemo->setRefundRequested(true);
                }
                if (isset($data['do_offline'])) {
                    $creditmemo->setOfflineRequested((bool)(int)$data['do_offline']);
                }

                $creditmemo->register();
                if (!empty($data['send_email'])) {
                    $creditmemo->setEmailSent(true);
                }

                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $this->_saveCreditmemo($creditmemo);
				
				/* MEMO PDF API */
				$order = $creditmemo->getOrder();
				$billingAddress = $order->getBillingAddress();
				$shippingAddress = $order->getShippingAddress();
				$invoiceData = array();
				$CodRef = 1;
				$b=0;
				foreach ($creditmemo->getAllItems() as $item){
					if ($item->getOrderItem()->getParentItem()) {
						continue;
					}
					$memoOpt='';
					$options = $item->getOrderItem()->getProductOptions();
					if(isset($options['additional_options'])){
						foreach ($options['additional_options'] as $option) {
						$memoOpt .= '<b>'.$option['label'].':</b> '.$option['value'].'<br />';
						}
					}
					$product_data[$b]= array(
					'NmbItem' => $item->getName(),
					'DscItem' => $memoOpt,
					'QtyItem' => (int)$item->getQty(),
					'PrcItem' => $item->getPrice(),
					);
					$b++;
				}
				if($creditmemo->getShippingAmount() && $creditmemo->getShippingAmount()>0){
					$product_data[$b+1]= array(
					'NmbItem' => $order->getShippingDescription(),
					'DscItem' => '',
					'QtyItem' => 1,
					'PrcItem' => $creditmemo->getShippingAmount(),
					);
				}
				if($creditmemo->getInvoice()){
					if($creditmemo->getInvoice()->getGrandTotal()==$creditmemo->getGrandTotal()){
						$CodRef = 1;
					}else{
						$CodRef = 3;
					}
					$invoiceData = array(
						'NroLinRef' => '1',
						'TpoDocRef' => '33',
						'FolioRef' => $creditmemo->getInvoice()->getIncrementId(),
						'FchRef' => date('Y-m-d', strtotime($creditmemo->getInvoice()->getCreatedAt())),
						'CodRef' => $CodRef,
					);
				}else{
					if ($order->hasInvoices()) {
						$invoice = $order->getInvoiceCollection()->getFirstItem();
						if($invoice->getGrandTotal()==$creditmemo->getGrandTotal()){
							$CodRef = 1;
						}else{
							$CodRef = 3;
						}
						$invoiceData = array(
							'NroLinRef' => '1',
							'TpoDocRef' => '33',
							'FolioRef' => $invoice->getIncrementId(),
							'FchRef' => date('Y-m-d', strtotime($invoice->getCreatedAt())),
							'CodRef' => $CodRef,
						);
					}
				}
				$datos = array(
					'Encabezado' => array(
						'IdDoc' => array(
							'TipoDTE' => 61,
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
					'Referencia' => array($invoiceData),
				);
				$api = new SuperFacturaAPI('nm@wypo.cl', 'K94679nM');
				$resultado = $api->SendDTE($datos, 'cer', array('getPDF' => true));
				if($resultado['ok']){
					if($resultado['folio']){
						$pdf = $resultado['pdf'];
						file_put_contents(Mage::getBaseDir('media').'/creditmemos/'.$resultado['folio'].'.pdf', $pdf);
						$creditmemo->setIncrementId($resultado['folio']);
					}		
				}
				/* MEMO PDF API END */
				
                $creditmemo->sendEmail(!empty($data['send_email']), $comment);
                $this->_getSession()->addSuccess($this->__('The credit memo has been created.'));
                Mage::getSingleton('adminhtml/session')->getCommentText(true);
				if($resultado['folio']){
					$resource = Mage::getSingleton('core/resource');
					$writeConnection = $resource->getConnection('core_write');
					$query = 'UPDATE sales_flat_creditmemo SET increment_id='.$resultado['folio'].' WHERE entity_id='.$creditmemo->getId();
					$queryone = 'UPDATE sales_flat_creditmemo_grid SET increment_id='.$resultado['folio'].' WHERE entity_id='.$creditmemo->getId();
					try{
						$writeConnection->query($query);
						$writeConnection->query($queryone);
					}catch(Exception $e){
						$this->_getSession()->addError($e->getMessage());
					}
				}
                $this->_redirect('*/sales_order/view', array('order_id' => $creditmemo->getOrderId()));
                return;
            } else {
                $this->_forward('noRoute');
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Cannot save the credit memo.'));
        }
        $this->_redirect('*/*/new', array('_current' => true));
    }
}