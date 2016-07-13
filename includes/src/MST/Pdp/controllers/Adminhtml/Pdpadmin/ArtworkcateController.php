<?php

class MST_Pdp_Adminhtml_Pdpadmin_ArtworkcateController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed() {
        return true;
    }
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('pdp/pdp')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Categories'), Mage::helper('adminhtml')->__('Manage Categories'));

        return $this;
    }
    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('pdp/adminhtml_artworkcate'));
        $this->renderLayout();
    }
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('pdp/artworkcate')->load($id);
        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
				
            }
            Mage::register('artworkcate_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('pdp/artworkcate');
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Artwork Category'), Mage::helper('adminhtml')->__('Manage Categories'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('pdp/adminhtml_artworkcate_edit'))
                ->_addLeft($this->getLayout()->createBlock('pdp/adminhtml_artworkcate_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pdp')->__('Artwork category does not exist'));
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
		
       
		$artworks = null;
		if (isset($data['links'])) {
			$artworks = Mage::helper('adminhtml/js')->decodeGridSerializedInput($data['links']['images']);
			//$data['artworks'] = array_keys($artworks);
		}
		/* echo "<pre>";
		print_r($artworks);
		die(); */
		if(isset($data['thumbnail']['delete']))
		{
			$data['thumbnail'] = '';
			if(isset($data['thumbnail']['value']))
			{
				$oldImage = $data['thumbnail']['value'];
				$tempOldImages = explode('/',$oldImage);
				$oldImage = $tempOldImages[3];
				$pathDelete = Mage::getBaseDir('media').DS."pdp".DS."images".DS."artworks_cat".DS;
				Mage::helper('pdp/upload')->removeImage($oldImage,$pathDelete);
				$pathDelete .= "resized".DS; 
				Mage::helper('pdp/upload')->removeImage($oldImage,$pathDelete);
			}
		}
		elseif(isset($_FILES['thumbnail']['name']) && $_FILES['thumbnail']['name'] != '')
		{
			$path = Mage::getBaseDir('media').DS."pdp".DS."images".DS."artworks_cat";
			$beforName = 'Image-';
			$fileImage = $_FILES['thumbnail'];
			$oldImage = isset($data['thumbnail']['value']) ? $data['thumbnail']['value'] : '';
			if($oldImage != '')
			{
				$tempOldImages = explode('/',$oldImage);
				$oldImage = $tempOldImages[3];
			}
			$option['value'] = $oldImage;
			$option['ok_resized'] = 1;
			$option['allow_type_image'] = array('jpg', 'jpeg', 'gif', 'png');
			$strImage = Mage::helper('pdp/upload')->uploadImage($fileImage,'thumbnail',$path,$beforName,$option);
			$data['thumbnail'] = $strImage;
		}
		else
		{
			unset($data['thumbnail']);
		}
		//check parent id
		$idCurrent = $this->getRequest()->getParam('id',0);
		if($idCurrent > 0)
		{
			if($data['parent_id'] == $idCurrent)
			{
				unset($data['parent_id']);
			}
			elseif($data['parent_id']  > 0)
			{
				$curretArtworkcate = Mage::getModel('pdp/artworkcate')->load($idCurrent);
				
				if($curretArtworkcate)
				{
					$curParent = $curretArtworkcate->getParentId();
					//check child
					$ChildArtworkcates = Mage::getModel('pdp/artworkcate')->getCollection()
						->addFieldToFilter('parent_id',$idCurrent);
					if(count($ChildArtworkcates))
					{
						foreach($ChildArtworkcates as $ChildArtworkcate)
						{
							if($data['parent_id']  == $ChildArtworkcate->getId())
							{
								//update parent_id for item
								Mage::getModel('pdp/artworkcate')->setParentId($curParent)->setId($ChildArtworkcate->getId())->save();
							}
						}
					}
				}
				
			}
			
		}
		if ($data) {
            $model = Mage::getModel('pdp/artworkcate');
            $model->setData($data)->setId($this->getRequest()->getParam('id'));
            try {
                $model->save();
				
				//Update artworks info-------------------
				//Reset category of all artworks, and then assign again.
				if ($artworks && is_array($artworks)) {
					$categoryId = $model->getId();
					$artworksModel = Mage::getModel('pdp/images');
					$artworksCollection = $artworksModel->getCollection();
					$artworksCollection->addFieldToFilter('category', $categoryId);
					foreach ($artworksCollection as $item) {
						$oldInfo = $artworksModel->load($item->getImageId());
						$oldInfo->setCategory(0);
						$oldInfo->setPosition(0);
						$oldInfo->save();
					}
					foreach ($artworks as $artworkId => $position) {
						$newModel = $artworksModel->load($artworkId);
						$newModel->setCategory($categoryId);
						$newModel->setPosition($position['position']);
						$newModel->save();
					}
				}
				//Update artworks info-------------------
				
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pdp')->__('category was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
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
                $model = Mage::getModel('pdp/artworkcate')->load($this->getRequest()->getParam('id'));
                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Category was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
    public function massDeleteAction() {
        $itemIds = $this->getRequest()->getParam('artworkcate');
        if (!is_array($itemIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($itemIds as $itemId) {
                    $model = Mage::getModel('pdp/artworkcate')->load($itemId);
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
        $menuIds = $this->getRequest()->getParam('artworkcate');
        if (!is_array($menuIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select menu(s)'));
        } else {
            try {
                foreach ($menuIds as $menuId) {
                    $seatcover = Mage::getSingleton('pdp/artworkcate')
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
	
	public function imageAction(){
		$this->loadLayout();
		$this->getLayout()->getBlock('artwork_grid')
		->setImages($this->getRequest()->getPost('images', null));
		$this->renderLayout();
	}
	public function imagegridAction(){
		$this->loadLayout();
		$this->getLayout()->getBlock('artwork_grid')
		->setImages($this->getRequest()->getPost('images', null));
		$this->renderLayout();
	}
	public function exportCsvAction()
    {
        $fileName   = 'categories.csv';
        $content    = $this->getLayout()->createBlock('pdp/adminhtml_artworkcate_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }
	public function exportXmlAction()
    {
        $fileName   = 'categories.xml';
        $content    = $this->getLayout()->createBlock('pdp/adminhtml_artworkcate_grid')
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
}