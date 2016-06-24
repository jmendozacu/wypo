<?php
/**
* Magento Support Team.
* @category   MST
* @package    MST_Pdp
* @version    2.0
* @author     Magebay Developer Team <info@magebay.com>
* @copyright  Copyright (c) 2009-2013 MAGEBAY.COM. (http://www.magebay.com)
*/
class MST_Pdp_Adminhtml_Pdpadmin_PdpController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed() {
        return true;
    }
    public function indexAction() {
        $this->_forward("image");
    }
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('pdp/pdp')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Design Manager'), Mage::helper('adminhtml')->__('Design Manager'));
        return $this;
    }
	/* add by mai uoc */
	public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('pdp/images')->load($id);
        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
				
            }
            Mage::register('cliparts_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('pdp/images');
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Cliparts'), Mage::helper('adminhtml')->__('Manage Cliparts'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('pdp/adminhtml_pdp_edit'))
                ->_addLeft($this->getLayout()->createBlock('pdp/adminhtml_pdp_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pdp')->__('Clipart does not exist'));
            $this->_redirect('*/*/');
        }
    }
    public function newAction()
    {
        $this->_forward('edit');
    }
	public function saveAction() {
		$data = $this->getRequest()->getPost();
        //Zend_Debug::dump($data);die;
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
		
       
		$artworks = null;
		$strImage = '';
		//Delete request
        if(isset($data['filename']['delete'])) {
            $data['filename'] = '';
        }
        if(isset($data['thumbnail']['delete'])) {
            $data['thumbnail'] = '';
        }
        //Old filename value and thumbnail value
        if(isset($data['filename']) && is_array($data['filename'])) {
            $oldImage = $data['filename']['value'];
			if($oldImage != '') {
				$tempOldImages = str_replace('pdp/images/artworks/', '' ,$oldImage);
                $data['filename'] = $tempOldImages;
			}
        }
        
        if(isset($data['thumbnail']) && is_array($data['thumbnail'])) {
            $oldImage = $data['thumbnail']['value'];
			if($oldImage != '') {
				$tempOldImages = str_replace('pdp/images/artworks/', '' ,$oldImage);
                $data['thumbnail'] = $tempOldImages;
			}
        }
        $filename = Mage::helper('pdp')->saveImage('filename', 'pdp/images/artworks/');
        if($filename != "") {
            $data['filename'] = $filename;
            //Auto Create clipart thumbnail
            $basePath = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . 'artworks' . DS. $data['filename'];
            $newPath = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . "artworks" . DS . "resize" . DS;
            $_thumbOptions = array(
                'width' => 150,
                'height' => 150,
                'media-url' => 'resize/'
            );
            $artworkThumbnail = Mage::helper("pdp/upload")->resizeImage($basePath, $newPath, $_thumbOptions);
            if($artworkThumbnail) {
                $data['thumbnail'] = $artworkThumbnail;
            }
            //End auto create thumbnail
        }
        $thumbnail = Mage::helper('pdp')->saveImage('thumbnail', 'pdp/images/artworks/');
        if($thumbnail != "") {
            $data['thumbnail'] = $thumbnail;
        }
		//$data['category'] = $data['parent_id'];
        //Validate filename using php. Js might not work
        if(!isset($data['filename']) || $data['filename'] == "") {
            Mage::getSingleton('adminhtml/session')->addError("Image file is required. Please select an image to upload!");
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
        //Zend_Debug::dump($data);die;
		if ($data) {
            $model = Mage::getModel('pdp/images');
            $model->setData($data)->setId($this->getRequest()->getParam('id'));
            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pdp')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/image');
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
                $model = Mage::getModel('pdp/images')->load($this->getRequest()->getParam('id'));
                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('clipart was successfully deleted'));
                $this->_redirect('*/*/image');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/image');
    }
    public function massDeleteAction() {
        $itemIds = $this->getRequest()->getParam('pdp');
        if (!is_array($itemIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($itemIds as $itemId) {
                    $model = Mage::getModel('pdp/images')->load($itemId);
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
        $this->_redirect('*/*/image');
    }
    public function massStatusAction()
    {
        $menuIds = $this->getRequest()->getParam('pdp');
        if (!is_array($menuIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select menu(s)'));
        } else {
            try {
                foreach ($menuIds as $menuId) {
                    $seatcover = Mage::getSingleton('pdp/images')
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
        $this->_redirect('*/*/image');
    }
	function massCategoryAction()
	{
        $itemIds = $this->getRequest()->getParam('pdp');
		$categoryId = $this->getRequest()->getParam('category',0);
        if (!is_array($itemIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select menu(s)'));
        } else {
            try {
                foreach ($itemIds as $itemId) {
                    $seatcover = Mage::getSingleton('pdp/images')
                            ->load($itemId)
                            ->setCategory($categoryId)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($itemIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/image');
	}
	/* end */
    public function uploadAction()
    {
        //$this->_initAction();
		$this->loadLayout()->_setActiveMenu('pdp/pdp');
        $this->renderLayout();
    }
	
	public function imageAction()
    {
        //$this->_initAction();
	/* 	$this->loadLayout()->_setActiveMenu('pdp/pdp');
        $this->renderLayout(); */
		$this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('pdp/adminhtml_pdp'));
        $this->renderLayout();
    }
	public function fontAction()
    {
        //$this->_initAction();
		$this->loadLayout()->_setActiveMenu('pdp/pdp');
        $this->renderLayout();
    }
	public function designAction()
    {
        //$this->_initAction();
		$this->loadLayout()->_setActiveMenu('pdp/pdp');
        $this->renderLayout();
    }
	public function deleteImageAction ()
	{
		$data = $this->getRequest()->getParams();
		if ($data['image_id'] != "") {
			$image = Mage::getModel('pdp/images')->load($data['image_id']);
            $filename = $image->getFilename();
            $path = Mage::getBaseDir('media').DS.'pdp/images/';
            unlink($path . $filename);
            $image->delete();
			$this->getResponse()->setBody("delete_" . $data['image_id']);
		}
	}
    
    public function deleteFontAction ()
	{
		$data = $this->getRequest()->getParams();
		if ($data['font_id'] != "") {
			$font = Mage::getModel('pdp/fonts')->load($data['font_id']);
            $filename = $font->getFilename();
            $path = Mage::getBaseDir('media').DS.'pdp/fonts/';
            unlink($path . $filename);
            $font->delete();
			$this->getResponse()->setBody("delete_" . $data['font_id']);
		}
	}
	 public function exportCsvAction()
    {
        $fileName   = 'images.csv';
        $content    = $this->getLayout()->createBlock('pdp/adminhtml_pdp_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }
	 public function exportXmlAction()
    {
        $fileName   = 'images.xml';
        $content    = $this->getLayout()->createBlock('pdp/adminhtml_pdp_grid')
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
	public function saveColorAction()
	{
		$image_id = $_POST['image_id_color'];
		$hexCode = str_replace('#', '', $_POST['color']);
		if ($hexCode == "" || $image_id == "" || $_FILES['color_image']['name'] == "") {
			$url = Mage::helper("adminhtml")->getUrl("pdp/adminhtml_pdp/image/");
			Mage::app()->getResponse()->setRedirect($url);
			return;
		}
		if (!empty($_FILES['color_image']['name'])) {
			try {
				$imageName = $_FILES['color_image']['name'];
				$ext = substr($imageName, strrpos($imageName, '.') + 1);
				$filename = "ColorImage_" . time() . '.' . $ext;
				$uploader = new Varien_File_Uploader('color_image');
				$uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'bmp', 'svg+xml')); // or pdf or anything
				/* $size=filesize($_FILES['image']['tmp_name']);
				$test=getimagesize($_FILES['image']['tmp_name']); */
				$uploader->setAllowRenameFiles(false);
				$uploader->setFilesDispersion(false);
				$path = Mage::getBaseDir('media').DS.'pdp/images/';
				$uploader->save($path, $filename);
				$data['filename'] = $filename;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/image');
				return;
			}
		}
		$data['image_id'] = $image_id;
		$data['color'] = $hexCode;
		$data['filename'];
		Mage::getModel('pdp/pdp')->addColorImage($data);
		$url = Mage::helper("adminhtml")->getUrl("pdp/adminhtml_pdp/image/");
		Mage::app()->getResponse()->setRedirect($url);
	}
	public function saveArtworkColorAction() 
	{
		$data = $_REQUEST;
		$colorImage = $data['color-image'];
		$pdpModel = Mage::getModel('pdp/pdp');
		foreach ($colorImage as $key => $value) {
			$designColor = array();
			$inputName = 'artworkimage_' . $key;
            $artworkPath = "pdp" . DS . "images" . DS . "artworks" . DS;
			try {
				$filename = Mage::helper('pdp')->saveImage($inputName, $artworkPath);
				if ($filename != "") {
					$designColor['filename'] = $filename;
					$designColor['image_id'] = $data['image_id'];
					$designColor['sort'] = $data['sort'][$key];
					$designColor['color'] = $colorImage[$key];
					$pdpModel->addColorImage($designColor);
				}
			} catch(Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError("Can not upload cliparts");
				$url = Mage::helper("adminhtml")->getUrl("pdp/adminhtml_pdp/artworkcolor/image_id/" . $data['image_id']);
				Mage::app()->getResponse()->setRedirect($url);
				return;
			}
		}
		$url = Mage::helper("adminhtml")->getUrl("adminhtml/pdpadmin_pdp/image/");
		Mage::app()->getResponse()->setRedirect($url);
	}
	
	public function artworkColorAction() 
	{		
		$this->loadLayout()->_setActiveMenu('pdp/pdp');
		$this->renderLayout();
	}
	public function artworkColorInfoAction() 
	{
		$imageId = $this->getRequest()->getParam('image_id');
		if ($imageId != "") {
			$info = Mage::getModel('pdp/pdp')->getImageInfo($imageId);
			$info['add_color_url'] = Mage::helper("adminhtml")->getUrl("adminhtml/pdpadmin_pdp/artworkcolor/",array("image_id"=> $imageId));
			$this->getResponse()->setBody(json_encode($info));
		}
	}
    public function updateStatusAction() {
        $request = $this->getRequest()->getParams();
        Mage::getModel('pdp/productstatus')->setProductStatus($request);
    }
    public function selectImageAction() {
       $this->loadLayout();
		$this->getLayout()->getBlock('artwork_grid')
		->setImages($this->getRequest()->getPost('images', null));
		$this->renderLayout();
    }
    public function selectImageGridAction() {
        $this->loadLayout();
		$this->getLayout()->getBlock('artwork_grid')
		->setImages($this->getRequest()->getPost('images', null));
		$this->renderLayout();
    }
    public function saveSelectedImageAction() {
        $params = $this->getRequest()->getParams();
        $response = array(
            'status' => 'error',
            'message' => 'Can not save selected image for this product!'
        );
        if (isset($params['selected_items']) && $params['productid']) {
			$selectedItems = Mage::helper('adminhtml/js')->decodeGridSerializedInput($params['selected_items']);
            $selectedItemsJSON = json_encode($selectedItems);
            //Save to product status model
            $result = Mage::getModel("pdp/productstatus")->saveSelectedItem($params['productid'], 'selected_image', $selectedItemsJSON);
            if($result) {
                $response = array(
                    'status' => 'success',
                    'message' => 'Images item successfully saved!'
                );
            }
		}
        echo json_encode($response);
    }
}