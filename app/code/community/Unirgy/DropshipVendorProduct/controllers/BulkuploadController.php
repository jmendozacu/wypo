<?php	
/*
* This is customized functionality for CSV bulk upload
*/

class Unirgy_DropshipVendorProduct_BulkuploadController extends Mage_Core_Controller_Front_Action
{
    public function csvAction()
    {
	  $session = Mage::getSingleton('udropship/session');
	  if(!isset($_FILES['upload_csv_file']['name']) || !$session->getVendor()){
		 $session->addError("You are not authorized to access this page.");	
		 $this->_redirect('udprod/vendor/products');
		 return;		  
	  }	
	  $path = Mage::getBaseDir().DS.'up'.DS.'magmi'.DS.'import';	  
	  $_csv_file_name = $_FILES['upload_csv_file']['name'];
	  $uploader = new Varien_File_Uploader('upload_csv_file');
	  $uploader->setAllowedExtensions(array('csv'));
	  $uploader->setAllowCreateFolders(true);
	  $uploader->setAllowRenameFiles(false);
	  $uploader->setFilesDispersion(false);
	  try{
		$_vendor_id = $session->getVendor()->getData("vendor_id");	
		$uploader->save($path,$_vendor_id."_".$session->getVendor()->getData("vendor_name").".csv");
		$session->addSuccess("Your CSV file uploaded successfully.");
		$vendor_obj = Mage::getModel('udropship/vendor')->load($_vendor_id);		
		$vendor_obj->setData("file_upload_datetime",date("Y-m-d H:i:s"));
		$vendor_obj->setData("last_uploaded_file_name",$_csv_file_name);
		unset($_FILES); 	
		$vendor_obj->save();		
	  }catch(Exception $e){
		$session->addError($e->getMessage());
	  }	  
	  $this->_redirectReferer();
    }
}