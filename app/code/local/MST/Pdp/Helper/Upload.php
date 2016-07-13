<?php
/**
* Magento Support Team.
* @category   MST
* @package    MST_Pdp
* @version    2.0
* @author     Magebay Developer Team <info@magebay.com>
* @copyright  Copyright (c) 2009-2013 MAGEBAY.COM. (http://www.magebay.com)
*/
require_once(Mage::getBaseDir("lib") . DS . "WideImage" . DS . "WideImage.php");
class MST_Pdp_Helper_Upload extends Mage_Core_Helper_Abstract {
    public $uploadDir;
    public $uploadMediaUrl;
    public function __construct() {
        $this->uploadDir = Mage::getBaseDir("media") . DS . "pdp" . DS . "images" . DS . "upload" . DS ;
        $this->uploadMediaUrl = Mage::getBaseUrl("media") . "pdp/images/upload/";
    }
    public function isImagickLoaded() {
        return extension_loaded('imagick');   
    }
    public function isGetImageSizeLoaded() {
        return function_exists('getimagesize');
    }
    //$size_str look like this: 200M, 200k, or 200g
    private function returnBytes ($size_str)
    {
        switch (substr ($size_str, -1))
        {
            case 'M': case 'm': return (float)$size_str * 1048576;
            case 'K': case 'k': return (float)$size_str * 1024;
            case 'G': case 'g': return (float)$size_str * 1073741824;
            default: return $size_str;
        }
    }
    //Return upload_max_filesize in byte, KB or M
    public function getUploadMaxFileSize($type) {
        $maxFileSize = ini_get("upload_max_filesize");
        $sizeInByte = (float) $this->returnBytes($maxFileSize);
        if($type) {
            switch ($type) {
                case 'K': case 'k' : return $sizeInByte / 1024;
                case 'M': case 'M' : return $sizeInByte / 1048576;
                default : return $sizeInByte;
            }
        }
    }
    /**
    return true if image is real
    return false otherwise
    **/
    public function isRealImage($imagePath) {
        //Security Considerations by check getimagesize
        if(is_array($this->getImageSize($imagePath))) {
           return true;
        } else {
            //Return true anyway, there is no function to check
            if(!$this->isGetImageSizeLoaded()) {
                return true;
            }
        }
        return false;
    }
    /**
    return array of image size
    return false otherwise
    **/
    public function getImageSize($imagePath) {
        if($this->isGetImageSizeLoaded()) {
            try {
                $result = list($width, $height, $type, $attr) = getimagesize($imagePath);
                if(is_array($result)) {
                    return $result;
                } 
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }
    /**
    @params $cropData ($width, $height, $top, $left)
    return array crop status
    **/
    public function cropImage($imagePath, $cropData) {
        $response = array();
        if(!$this->isRealImage($imagePath)) {
            $response['status'] = 'error';
            $response['message'] = 'Image file not valid. Please check again!';
            return $response;
        }
        if($this->isImagickLoaded()) {
            $cropResult = $this->cropImageUseImagick($imagePath, $cropData);
        } else {
            //GD crop might be
            $cropResult = $this->cropImageUseWideImage($imagePath, $cropData);
        }
        if (!$cropResult) {
            $response['status'] = 'error';
            $response['message'] = 'Can not crop this image! Please check again!';
            return $response;
        } else {
            $response['status'] = 'success';
            $response['message'] = 'Image have been cropped successfully!';
            $response['crop_image'] = $cropResult;
            return $response;
        }
    }
    /**
    @required Imagick
    return cropped image path if cropped success, FALSE otherwise
    **/
    private function cropImageUseImagick($imagePath, $cropData) {
        //Get filename
        $temp = explode(DS, $imagePath);
        $filename = end($temp);
        $outFile = "crop-img-" . time() . '_' . $filename;
        $newPath = $this->uploadDir . $outFile;
        try {
            $image = new Imagick($imagePath);
            $image->cropImage($cropData['w'], $cropData['h'], $cropData['x'], $cropData['y']);
            $result = $image->writeImage($newPath);
            if ($result) {
                return $this->uploadMediaUrl . $outFile;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    /**
    @required GD
    return cropped image path if cropped success, FALSE otherwise
    **/
    private function cropImageUseWideImage($imagePath, $cropData) {
        //Get filename
        $temp = explode(DS, $imagePath);
        $filename = end($temp);
        $outFile = "crop-wide-" . time() . '_' . $filename;
        $newPath = $this->uploadDir . $outFile;
        try {
            $image = WideImage::load($imagePath);
            $newImage = $image->crop($cropData['x'], $cropData['y'], $cropData['w'], $cropData['h']);
            //Image quality, if jpg then set quality to 100
            $fileExt = explode(".", $outFile);
            if (end($fileExt) == "jpg" || end($fileExt) == "jpeg") {
                $newImage->saveToFile($newPath, 100);
            } else {
                $newImage->saveToFile($newPath);
            }
            if(file_exists($newPath)) {
                return $this->uploadMediaUrl . $outFile;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    //Return size config
    public function getConfig($outputType = "") {
        $config = array();
        //All size in byte unit
        $config['size_unit'] = Mage::getStoreConfig("pdp/custom_upload/size_unit");
        $uploadMaxSize = (float) Mage::getStoreConfig("pdp/custom_upload/upload_max_size");
        $uploadMinSize = (float) Mage::getStoreConfig("pdp/custom_upload/upload_min_size");
        $config['upload_max_size'] = $uploadMaxSize;
        $config['upload_min_size'] = $this->returnBytes($uploadMinSize . $config['size_unit']);
        $config['upload_max_filesize'] = $this->getUploadMaxFileSize('b');
        $config['max_size_alert'] = $this->__("This file is too big. The maximum upload size is: " . $uploadMaxSize . $config['size_unit']);
        $config['min_size_alert'] = $this->__("This file is too small. Please upload image file equal or bigger than : " . $uploadMinSize . $config['size_unit']);
        //X3 version message
        $config['upload_max_size_message'] = Mage::getStoreConfig("pdp/custom_upload/upload_max_size_message");
        $config['upload_max_files_message'] = Mage::getStoreConfig("pdp/custom_upload/upload_max_files_message");
        $config['default_message'] = Mage::getStoreConfig("pdp/custom_upload/default_message");
        $config['remove_label'] = Mage::getStoreConfig("pdp/custom_upload/remove_label");
        $config['cancel_label'] = Mage::getStoreConfig("pdp/custom_upload/cancel_label");
        $config['upload_max_files'] = Mage::getStoreConfig("pdp/custom_upload/upload_max_files");
        $config['upload_min_pixel_width'] = Mage::getStoreConfig("pdp/custom_upload/upload_min_pixel_width");
        $config['upload_min_pixel_height'] = Mage::getStoreConfig("pdp/custom_upload/upload_min_pixel_height");
        $config['upload_min_pixel_error'] = Mage::getStoreConfig("pdp/custom_upload/upload_min_pixel_error");
        if ($outputType === "JSON") {
            return json_encode($config);
        }
        return $config;
    }
    public function getUploadNote() {
        return Mage::getStoreConfig("pdp/custom_upload/upload_note");
    }
    public function addWatermark($source) {
        $watermarkConfig = Mage::helper("pdp")->getWatermarkConfig();
        if($watermarkConfig['active'] != "1") return false;
        try {
            $image = WideImage::load($source);
            $watermarkImgPath = $watermarkConfig['watermark_path'];
            $watermark = WideImage::load($watermarkImgPath);
            $position = explode("_", $watermarkConfig['position']);
            if(empty($position) || count($position) != 2) {
                $position[0] = "bottom";
                $position[1] = "right";
            }
            $new = $image->merge($watermark, $position[0], $position[1], 100);
            //Overwrite the source file
            $result = $new->saveToFile($source);
            return true;
        } catch (Exception $e) {
            
        }
        return false;
    }
    public function getSupportedImages() {
        //Please upload a file in one of the following formats: .svg, .jpg, .png, .jpeg, .bmp, .gif
        $formats = array('svg', 'jpg', 'jpeg', 'png', 'bmp', 'gif');
        if($this->isImagickLoaded()) {
            $formats[] = "pdf";
            $formats[] = "ai";
            $formats[] = "eps";
            $formats[] = "psd";
        }
        return $formats;
    }
    public function getUnsuportedMessage() {
        $message = $this->__("Please upload a file in one of the following formats:");
        $formats = $this->getSupportedImages();
        return $message . ' ' .  join($formats, ', ');
    }
    public function getFileAccept() {
        $fileTypes = array("image/*");
        if($this->isImagickLoaded()) {
            $fileTypes[] = "application/pdf";
            $fileTypes[] = "application/postscript";
            $fileTypes[] = ".psd"; //For dropzone upload
        }
        if(count($fileTypes) > 1) {
            return join($fileTypes, ',');
        } else {
            return $fileTypes[0];
        }
        
    }
    //Used in Upload controller
    public function getApplicationFileTypes() {
        $types = array("application/pdf", "application/postscript");
        //PSD
        $types[] = "application/octet-stream";
        return $types;
    }
    public function convertFileToImage($filePath) {
        $response = array(
            'status' => 'error',
            'message' => 'Sorry! Unable to convert this file!'
        );
        if(!file_exists($filePath)) {
            return $response;
        }
        try {
            $pathParts = pathinfo($filePath);
            $filename = $pathParts['filename'];
            $ext = $pathParts['extension'];
            $baseDir = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . "upload" . DS;
            $newFilename = 'converted-' . $filename . '.png';
            //exec("convert -geometry 1600x1600 -density 300x300 -quality 100 demo.pdf test_image.png"); // 300x300 DPI
            switch($ext) {
                case "psd" :
                    //For PSD, need -flatten
                    //Trick test3.psd[0]
                    //exec("convert identify 300 300 test6.psd[0] -flatten demo.jpg");
                    exec("convert " . $filePath ."[0] -flatten " . $baseDir . $newFilename);
                    break;
                case "eps" :
                    //convert white color to transparent
                    exec("convert -density 300 -colorspace srgb ". $filePath ." -transparent white " . $baseDir . $newFilename);
                    //Keep white color, please use default case
                    break;
                default:
                    exec("convert " . $filePath . " " . $baseDir . $newFilename);
                    break;
            }
            if(file_exists($baseDir . $newFilename)) {
                $response = array(
                    'status' => 'success',
                    'message' => 'Convert file to image successfully!',
                    'filename' => $newFilename
                );
            }
        } catch(Exception $e) {
            
        }
        return $response;
    }
    //Using imagick extension for php
	public function convertFileToImageUsingPhpExt($filePath) {
        $response = array(
            'status' => 'error',
            'message' => 'Sorry! PHP Imagick extension unable to convert this file!'
        );
        try {
            $pathParts = pathinfo($filePath);
            $filename = $pathParts['filename'];
            $ext = $pathParts['extension'];
            $baseDir = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . "upload" . DS;
            $newFilename = 'converted-' . $filename . '.png';
			$image = new Imagick();
			$image->readimage($filePath);
			if($ext == "psd") {
				$image->setIteratorIndex(0);
			}
            //Resize image to 400px
			//$image->thumbnailImage(400, 0);
			$image->writeImage($baseDir . $newFilename);
            if(file_exists($baseDir . $newFilename)) {
                $response = array(
                    'status' => 'success',
                    'message' => 'Convert file to image successfully!',
                    'filename' => $newFilename,
					'vector_filename' => $filename . "." . $ext
                );
            }
        } catch(Exception $e) {
            //Zend_Debug::dump($e);
        }
        return $response;
    }
	/**
	* upload image
	* @params array $profileImg, string $name_field
	* @return filename
	**/
	function uploadImage($profileImg,$name_field,$path = '',$beforName = '',$option = array())
	{
		$fname = '';
		$imagedata = array();
        if (!empty($profileImg['name'])) {
            try {
                $ext = substr($profileImg['name'], strrpos($profileImg['name'], '.') + 1);
                $fname = time() . '.' . $ext;
                $uploader = new Varien_File_Uploader($name_field);
				if(!isset($option['allow_type_image']))
				{
					$option['allow_type_image'] = array('jpg', 'jpeg', 'gif', 'png','svg');
				}
                $uploader->setAllowedExtensions($option['allow_type_image']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
				if($path == '')
				{
					$path = Mage::getBaseDir('media').DS."pdp".DS."images";
				}
				$fname = $beforName.$fname;
                $uploader->save($path, $fname);
				//resized image
				$basePath = $path.DS.$fname;
				if(isset($option['ok_resized']) && $option['ok_resized'] == 1 && $ext != 'svg')
				{
					$width = isset($option['width']) ? $option['width'] : 150;
					$hight = isset($option['height']) ? $option['width'] : 150;
					$newPath = $path.DS."resized".DS.$fname;
					if (file_exists($basePath) && is_file($basePath)) {
						$imageObj = new Varien_Image($basePath);
						$imageObj->constrainOnly(TRUE);
						$imageObj->keepAspectRatio(TRUE);
						$imageObj->keepFrame(true);
						$imageObj->keepTransparency(true);
						$imageObj->backgroundColor(array(255,255,255));
						$imageObj->resize($width, $hight);
						$imageObj->save($newPath);
					}
				}
				if(count($option ) && isset($option['value']) && $option['value'] != '')
				{
					$pathDelete = $path.DS;
					$this->removeImage($option['value'],$pathDelete );
					//delete thumbnail
					$pathDeleteThumbnail = $pathDelete."resized".DS;
					$this->removeImage($option['value'],$pathDeleteThumbnail);
				}

            } catch (Exception $e) {
                //$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
		return $fname;
	}
	function removeImage($filename,$path = '')
	{
		if($path == '')
		{
			$path = Mage::getBaseDir('media').DS;
		}
		if(is_file($path.$filename))
		@unlink($path.$filename);
	}
    /**
    $options[width]
    $options[height]
    $options[media-url]
    **/
    public function resizeImage($basePath, $newPath = '', $options = array()) {
        if(!file_exists($basePath)) {
            return false;
        }
        $extTemp = explode(".", $basePath);
        if(end($extTemp) == "svg") {
            return false;
        }
        //Image name 
        $width = 150;
        $height = 150;
        if(isset($options['width'])) {
            $width = $options['width'];
        }
        if(isset($options['height'])) {
            $height = $options['height'];
        }
        $nameTemp = explode(DS, $basePath);
        $newFilename = "resize_" . end($nameTemp);
        if($newPath == "") {
            $newPath = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . "upload" . DS . "resize" . DS;
        }
        //Create new folder if not exists
        if(!file_exists($newPath)) {
            mkdir($newPath, 0777, true);
            if(!file_exists($newPath)) {
                return false;
            }
        }
        $imageObj = new Varien_Image($basePath);
        $imageObj->constrainOnly(TRUE);
        $imageObj->keepAspectRatio(TRUE);
        $imageObj->keepFrame(false);
        $imageObj->keepTransparency(true);
        $imageObj->backgroundColor(array(255,255,255));
        $imageObj->resize($width, $height);
        $imageObj->save($newPath . $newFilename);
        if(file_exists($newPath . $newFilename)) {
            $mediaUrl = $this->uploadMediaUrl . "resize/";
            if(isset($options['media-url'])) {
                $mediaUrl = $options['media-url'];
            }
            return $mediaUrl . $newFilename;
        }
        return false;
    }
    /**
    Will create thumbnail for all artwork if the thumbnail field is empty
    **/
    public function autoCreateArtworkThumbnail() {
        echo "auto create thumbnaill";
        $artworkModel = Mage::getModel("pdp/images"); 
        $allArtworks = $artworkModel->getCollection();
        $newPath = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . "artworks" . DS . "resize" . DS;
        $basePath = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . 'artworks' . DS;
        foreach($allArtworks as $artwork) {
            if($artwork->getThumbnail() == "") {
                //Try to create thumbnail for this image when export, if thumbnail field is empty
                try {
                    //Auto Create clipart thumbnail
                    $_thumbOptions = array(
                        'width' => 150,
                        'height' => 150,
                        'media-url' => 'resize/'
                    );
                    $artworkThumbnail = Mage::helper("pdp/upload")->resizeImage($basePath . $artwork->getFilename(), $newPath, $_thumbOptions);
                    if($artworkThumbnail) {
                        $artworkModel->setThumbnail($artworkThumbnail)->setId($artwork->getId())->save();
                    }
                    //End auto create thumbnail
                } catch(Exception $error) {
                    Zend_Debug::dump($error);
                }   
                //End create thumbnail    
            }
        }
        
    }
    /**
    Will create thumbnail for all product color images if the filename_thumbnail field is empty
    **/
    public function autoCreateProductColorImageThumbnail() {
        echo "auto create thumbnaill";
        $artworkModel = Mage::getModel("pdp/pdpcolorimage"); 
        $allArtworks = $artworkModel->getCollection();
        $basePath = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS;
        $newPath = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . "resize" . DS;
        
        foreach($allArtworks as $artwork) {
            if($artwork->getFilenameThumbnail() == "") {
                //Try to create thumbnail for this image when export, if thumbnail field is empty
                try {
                    Zend_Debug::dump($artwork->getData());
                    //Auto Create clipart thumbnail
                    $_thumbOptions = array(
                        'width' => 150,
                        'height' => 150,
                        'media-url' => 'resize/'
                    );
                    $artworkThumbnail = Mage::helper("pdp/upload")->resizeImage($basePath . $artwork->getFilename(), $newPath, $_thumbOptions);
                    if($artworkThumbnail) {
                        $artworkModel->setFilenameThumbnail($artworkThumbnail)->setId($artwork->getId())->save();
                    }
                    //End auto create thumbnail
                } catch(Exception $error) {
                    Zend_Debug::dump($error);
                }   
                //End create thumbnail    
            }
        }
        
    }
}
