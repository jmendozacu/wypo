<?php
/**
* Magento Support Team.
* @category   MST
* @package    MST_Pdp
* @version    2.0
* @author     Magebay Developer Team <info@magebay.com>
* @copyright  Copyright (c) 2009-2013 MAGEBAY.COM. (http://www.magebay.com)
*/
class MST_Pdp_Adminhtml_Pdpadmin_FontsController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed() {
        return true;
    }
    public function indexAction() {
       $this->_initAction()
        ->renderLayout();
    }
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('pdp/pdp')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Fonts Manager'), Mage::helper('adminhtml')->__('Fonts Manager'));
        return $this;
    }
	public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('pdp/fonts')->load($id);
        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
				
            }
            Mage::register('fonts_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('pdp/images');
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Fonts'), Mage::helper('adminhtml')->__('Manage Fonts'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('pdp/adminhtml_fonts_edit'))
                ->_addLeft($this->getLayout()->createBlock('pdp/adminhtml_fonts_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pdp')->__('Font does not exist'));
            $this->_redirect('*/*/');
        }
    }
    public function newAction()
    {
        $this->_forward('edit');
    }
	public function saveAction() {
		$data = $this->getRequest()->getPost();
		//david
		$main_domain = Mage::helper('pdp')->get_domain( $_SERVER['SERVER_NAME'] );		
		if ( $main_domain != 'dev' ) { 
			$rakes = Mage::getModel('pdp/act')->getCollection();
			$rakes->addFieldToFilter('path', 'pdp/act/key' );
			$valid = false;
			if ( count($rakes) > 0 ) {
				foreach ( $rakes as $rake )  {
					if ( $rake->getExtensionCode() == md5($main_domain.trim(Mage::getStoreConfig('pdp/act/key')) ) ) {
						$valid = true;	
					}
				}
			}
			if ( $valid == false )  {  
				Mage::getSingleton('adminhtml/session')->addError( base64_decode('UGxlYXNlIGVudGVyIGxpY2Vuc2Uga2V5ICE=') );
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('pdp/adminhtml_artworkcate/index');
				return;
			}
		}
		if ($data) {
			if (isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
                try
                {
                    $path = Mage::getBaseDir('media').DS."pdp".DS."fonts";
                    $fname = $_FILES['filename']['name']; //file name
					$data['original_filename'] = $fname;
                    if(isset($data['display_text']) && $data['display_text'] == '') {
                        $tempFontName = explode('.', $data['original_filename']);
                        $data['display_text'] = $tempFontName[0];
                    }
                    $ext = substr($fname , strrpos($fname , '.') + 1);
					$fname = str_replace(" ", "_", strtolower($fname));
                    $fnameArr = explode('.', $fname);
                    $data['ext'] = $fnameArr[1];
                    $data['name'] = $fnameArr[0];
                    //$fullname = $path.$fname;
                    $uploader = new Varien_File_Uploader('filename'); //load class
                    $allowFonts = array("ttf", "otf", "fnt", "fon", "woff", "dfont");
                    $uploader->setAllowedExtensions($allowFonts); //Allowed extension for file
                    $uploader->setAllowCreateFolders(true); //for creating the directory if not exists
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $uploader->save($path, $fname); //save the
					/**if(isset($data['file_name_old']) && $data['file_name_old'] != '')
					{
						if($data['file_name_old'] != $fname)
						{
							if(is_file($path.DS.$data['file_name_old']))
								@unlink($path.DS.$data['file_name_old']);
						}
					}**/
                }
                catch (Exception $e)
                {
						Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pdp')->__($fname." Invalid file format"));
						$this->_redirect('*/*/image');
						return;
                }
            }
            $model = Mage::getModel('pdp/fonts');
			$currentId = $this->getRequest()->getParam('id',0);
			if($currentId > 0 && !isset($data['ext']))
			{
				$oldData = $model->load($currentId)->getData();
				$data['name'] = $oldData['name'];
				$data['ext'] = $oldData['ext'];
				$data['original_filename'] = $oldData['original_filename'];
				if(isset($data['display_text']) && $data['display_text'] == '') {
                    $data['display_text'] = $data['name'];
                }
			}
            if(isset($data['name']) && $data['name'] != "") {
                $model->setData($data)->setId($this->getRequest()->getParam('id'));    
            } else {
                Mage::getSingleton('adminhtml/session')->addError("Please choose font to upload.");
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pdp')->__('Font successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/index');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pdp')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }
	    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('pdp/fonts')->load($this->getRequest()->getParam('id'));
                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Font was successfully deleted'));
                $this->_redirect('*/*/index');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/index');
    }
    public function massDeleteAction() {
        $itemIds = $this->getRequest()->getParam('fonts');
        if (!is_array($itemIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($itemIds as $itemId) {
                    $model = Mage::getModel('pdp/fonts')->load($itemId);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($itemIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    public function massStatusAction()
    {
        $menuIds = $this->getRequest()->getParam('fonts');
        if (!is_array($menuIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select menu(s)'));
        } else {
            try {
                foreach ($menuIds as $menuId) {
                    $seatcover = Mage::getSingleton('pdp/fonts')
                            ->load($menuId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($menuIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	/* end */

	function exportCsvAction()
    {
        $fileName   = 'fonts.csv';
        $content    = $this->getLayout()->createBlock('pdp/adminhtml_fonts_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }
	 public function exportXmlAction()
    {
        $fileName   = 'fonts.xml';
        $content    = $this->getLayout()->createBlock('pdp/adminhtml_fonts_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }
    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
    public function selectFontAction() {
       $this->loadLayout();
		$this->getLayout()->getBlock('font_grid')
		->setFonts($this->getRequest()->getPost('fonts', null));
		$this->renderLayout();
    }
    public function selectFontGridAction() {
        $this->loadLayout();
		$this->getLayout()->getBlock('font_grid')
		->setFonts($this->getRequest()->getPost('fonts', null));
		$this->renderLayout();
    }
    public function saveSelectedFontAction() {
        $params = $this->getRequest()->getParams();
        $response = array(
            'status' => 'error',
            'message' => 'Can not save selected font for this product!'
        );
        if (isset($params['selected_items']) && $params['productid']) {
			$selectedItems = Mage::helper('adminhtml/js')->decodeGridSerializedInput($params['selected_items']);
            $selectedItemsJSON = json_encode($selectedItems);
            //Save to product status model
            $result = Mage::getModel("pdp/productstatus")->saveSelectedItem($params['productid'], 'selected_font', $selectedItemsJSON);
            if($result) {
                $response = array(
                    'status' => 'success',
                    'message' => 'Color item successfully saved!'
                );
            }
		}
        echo json_encode($response);
    }
}