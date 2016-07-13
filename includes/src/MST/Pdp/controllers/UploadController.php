<?php
class MST_Pdp_UploadController extends Mage_Core_Controller_Front_Action
{
    protected $_helper;
    public function _construct() {
        $this->_helper = Mage::helper("pdp/upload");
    }
    public function testAction() {
        echo "Minimum pixel or DPI </br>";
        /**
        $images = array("filename1446856840.jpg", "filename1448247444.jpg", "filename1449503335.png", "1415126317_Green-DTM.jpg");
        $imagePath = Mage::getBaseDir("media") . "/pdp/images/dpi/";
        $mediaUrl = Mage::getBaseUrl("media") . "/pdp/images/dpi/";
        foreach($images as $image) {
            echo '<img src="'. $mediaUrl . $image . '" width="100"/>';
            $dpi = $this->checkUserImageDPI($imagePath . $image);
            Zend_Debug::dump($dpi);
            echo "<hr/>";
        }  **/
    }
    private function getImageDPI($imagePath) {
        if($this->_helper->isImagickLoaded()) {
            $_DPI = 72;
            $img = new Imagick($imagePath);
            $identify = $img->identifyimage();
            if(isset($identify['resolution']['x'])) {
                if(isset($identify['units']) && $identify['units'] == "PixelsPerCentimeter") {
                    $_DPI = ceil($identify['resolution']['x'] * 2.54);
                } elseif(isset($identify['units']) && $identify['units'] == "PixelsPerInch") {
                    $_DPI = $identify['resolution']['x'];
                } else {
                    // units = Undefined
                    $_DPI = 72;
                }
            }
            return $_DPI;
        }
    }
    private function checkUserImageDPI($imagePath) {
        if(!file_exists($imagePath)) return false;
        $dpiRequired = Mage::getStoreConfig("pdp/custom_upload/upload_min_dpi");
        $response = array(
            'min_dpi_enable' => false,
            'valid_image' => false //Is image high or low resolution
        );
        if($dpiRequired != "" && $dpiRequired != NULL && (int) $dpiRequired > 0 && $this->_helper->isImagickLoaded()) {
            $response['min_dpi_enable'] = true;
            $response['image_dpi'] = $this->getImageDPI($imagePath);
            $response['require_dpi'] = $dpiRequired;
            if($response['image_dpi'] >= $response['require_dpi']) {
                $response['valid_image'] = true;
            }
        }
        //Check image dimensions
        $imgPixelRequireW = Mage::getStoreConfig("pdp/custom_upload/upload_min_pixel_width");
        $imgPixelRequireH = Mage::getStoreConfig("pdp/custom_upload/upload_min_pixel_height");
        if($imgPixelRequireW > 0 || $imgPixelRequireH > 0) {
            $imageSize = $this->_helper->getImageSize($imagePath);
            $response['image_dimension']['real_width'] = 0;
            $response['image_dimension']['real_height'] = 0;
            if(is_array($imageSize) && isset($imageSize[0]) && isset($imageSize[1])) {
                $response['image_dimension']['real_width'] = $imageSize[0];
                $response['image_dimension']['real_height'] = $imageSize[1];
            }
            $response['image_dimension']['require_width'] = (int) $imgPixelRequireW;
            $response['image_dimension']['require_height'] = (int) $imgPixelRequireH;
            $response['image_dimension']['valid_image'] = true;
            if($response['image_dimension']['require_width'] > 0 
               && $response['image_dimension']['require_width'] > $response['image_dimension']['real_width']) {
                $response['image_dimension']['valid_image'] = false;
            }
            if($response['image_dimension']['require_height'] > 0 
               && $response['image_dimension']['require_height'] > $response['image_dimension']['real_height']) {
                $response['image_dimension']['valid_image'] = false;
            }
            
        }
        return $response;
    }
	public function uploadCustomImageAction() {
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES["uploads"])) {
			$uploads = $_FILES["uploads"];
            //SVG type : image/svg+xml
			if (count($uploads['name'])>0) {
				$baseDir = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . "upload" . DS;
				if (!file_exists($baseDir)) {
					mkdir($baseDir, 0777);
				}
				if (file_exists($baseDir)) {
					$mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "pdp/images/upload/";
					$uploadedImages = array();
					foreach ($uploads['name'] as $key => $name) {
						if ($uploads['error'][$key] === UPLOAD_ERR_OK) {
							$filenameTemp = explode(".", $uploads["name"][$key]);
							$name = time() . '-customupload.' . end($filenameTemp);
							$size = $uploads["size"][$key];
							$type = $uploads["type"][$key]; // could be bogus!!! Users and browsers lie!!!
							$tmp  = $uploads["tmp_name"][$key];
							$result = move_uploaded_file( $tmp, $baseDir . $name);
							if ($result) {
                                //Check upload file types
                                $applicationFileTypes = Mage::helper("pdp/upload")->getApplicationFileTypes();
                                if(in_array($type, $applicationFileTypes)) {
                                    //Using imagick to convert application file to png file
                                    $convertResult = Mage::helper("pdp/upload")->convertFileToImage($baseDir . $name);
                                    if(isset($convertResult['status']) && $convertResult['status'] == "success") {
                                        $uploadedImages[] = $mediaUrl . $convertResult['filename'];
                                    } else {
                                        $this->getResponse()->setBody(json_encode($convertResult))->sendResponse();
                                        exit();
                                    }
                                } else {
                                    //Check if image is real, if not, remove file for security reason.
                                    if($uploads["type"][$key] == "image/svg+xml") {
                                        $uploadedImages[] = $mediaUrl . $name;
                                    } else {
                                        $isRealImage = $this->_helper->isRealImage($baseDir . $name);
                                        if($isRealImage) {
                                            $uploadedImages[] = $mediaUrl . $name;
                                        } else {
                                            $response['status'] = 'error';
                                            $response['message'] = 'Please upload a valid file!';
                                            //unlink($baseDir . $name);
                                            $this->getResponse()->setBody(json_encode($response))->sendResponse();
                                            exit();
                                        }
                                    }
                                }
							} else {
                                $response['status'] = 'error';
								$response['message'] = 'Can not upload image to server. Please check your server settings!';
								$this->getResponse()->setBody(json_encode($response))->sendResponse();
								exit();
                            }
						} else if ($uploads['error'][$key] === UPLOAD_ERR_INI_SIZE) {
							$response['status'] = 'error';
							$response['message'] = 'The uploaded file exceeds the upload_max_filesize. Please check your server PHP settings!';
							$this->getResponse()->setBody(json_encode($response))->sendResponse();
							exit();
						}
					}
					$key++;
					if (isset($uploadedImages[0])) {
						$this->setCustomImageSession($uploadedImages[0]);
					}
					$this->getResponse()->setBody(json_encode($uploadedImages));
				}
			}
		}
	}
	public function setCustomImageSession($image) {
		$customImages = Mage::getSingleton("core/session")->getCustomUploadImages();
		$customImages[] = $image;
        $reOrderImages = array_reverse($customImages);
		Mage::getSingleton("core/session")->setCustomUploadImages($reOrderImages);
	}
    public function cropImageAction() {
        $request = $this->getRequest()->getPost();
        $response = array();
        if (isset($request['filename']) && $request['filename'] != "") {
            $imagePath = $this->_helper->uploadDir . $request['filename'];
            $croppedImage = $this->_helper->cropImage($imagePath, $request);
            if($croppedImage) {
                $response = $croppedImage;
                $this->setCustomImageSession($croppedImage['crop_image']);
            }
        } else {
            $response['status'] = "error";
            $response['message'] = "Image not found!";
        }
        $this->getResponse()->setBody(json_encode($response));
    }
    public function deleteImageAction() {
        $response = array(
            'status' => 'error',
            'message' => 'Can not remove image! Something went wrong!'
        );
        $request = $this->getRequest()->getPost();
        if(isset($request['image']) && $request['image']) {
            $customImages = Mage::getSingleton("core/session")->getCustomUploadImages();
            $newCustomImages = array_diff($customImages, array($request['image']));
            Mage::getSingleton("core/session")->setCustomUploadImages($newCustomImages);
            $response = array(
                'status' => 'success',
                'message' => 'Image had been successfully removed!'
            );
        }
        $this->getResponse()->setBody(json_encode($response));
    }
    public function uploadImageAction() {
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES["filename"])) {
			$uploads = $_FILES["filename"];
            //SVG type : image/svg+xml
			if (count($uploads['name'])>0) {
				$baseDir = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . "upload" . DS;
				if (!file_exists($baseDir)) {
					mkdir($baseDir, 0777);
				}
				if (file_exists($baseDir)) {
					$mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "pdp/images/upload/";
					$uploadedImage = "";
                    if ($uploads['error'] === UPLOAD_ERR_OK) {
                        $tmp  = $uploads["tmp_name"];
                        $filenameTemp = explode(".", $uploads["name"]);
                        $name = time() . '-' . uniqid() . '-custom.' . end($filenameTemp);
                        $size = $uploads["size"];
                        $type = $uploads["type"]; // could be bogus!!! Users and browsers lie!!!
                        $result = move_uploaded_file( $tmp, $baseDir . $name);
                        if ($result) {
                            //Check upload file types
                            $applicationFileTypes = Mage::helper("pdp/upload")->getApplicationFileTypes();
                            if(in_array($type, $applicationFileTypes)) {
                                //Using imagick to convert application file to png file
                                $convertResult = Mage::helper("pdp/upload")->convertFileToImage($baseDir . $name);
                                if(isset($convertResult['status']) && $convertResult['status'] == "success") {
                                    $uploadedImage = $mediaUrl . $convertResult['filename'];
                                } else {
                                    $this->getResponse()->setBody(json_encode($convertResult))->sendResponse();
                                    exit();
                                }
                            } else {
                                //Check if image is real, if not, remove file for security reason.
                                if($uploads["type"] == "image/svg+xml") {
                                    $uploadedImage = $mediaUrl . $name;
                                } else {
                                    $isRealImage = $this->_helper->isRealImage($baseDir . $name);
                                    if($isRealImage) {
                                        $uploadedImage = $mediaUrl . $name;
                                    } else {
                                        $response['status'] = 'error';
                                        $response['message'] = 'Please upload a valid file!';
                                        //unlink($baseDir . $name);
                                        $this->getResponse()->setBody(json_encode($response))->sendResponse();
                                        exit();
                                    }
                                }
                            }
                        }
                    } else if ($uploads['error'] === UPLOAD_ERR_INI_SIZE) {
                        $response['status'] = 'error';
                        $response['message'] = 'The uploaded file exceeds the upload_max_filesize. Please check your server PHP settings!';
                        $this->getResponse()->setBody(json_encode($response))->sendResponse();
                        exit();
                    }
					if (isset($uploadedImage)) {
                        $uploadThumbnail = "";
                        $filenameTemp = explode("/", $uploadedImage);
                        //Create thumbnail
                        $uploadHelper = Mage::helper("pdp/upload");
                        $basePath = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . "upload" . DS . end($filenameTemp);
                        $thumbnailResult = $uploadHelper->resizeImage($basePath);
                        if($thumbnailResult) {
                            $uploadThumbnail = $thumbnailResult;
                        }
                        //Upload fee if exists
                        $productId = $_POST['product_id'];
                        $price = 0;
                        $priceFormat = Mage::helper("pdp")->__("Free");
                        if($productId) {
                            $productConfig = Mage::helper("pdp")->getProductConfig($productId);
                            if(isset($productConfig['clipart_price']) && $productConfig['clipart_price'] > 0) {
                                $price = $productConfig['clipart_price'];
                                $priceFormat = Mage::helper('core')->currency($price, true, false);
                            }
                        }
                        //Check image dpi, image dimensions
                        $checkImage = $this->checkUserImageDPI($basePath);
						$response = array(
                            'status' => 'success',
                            'message' => 'Image had uploaded successfully!',
                            'filename' => $uploadedImage,
                            'original_name' => $uploads["name"],
                            'thumbnail' => $uploadThumbnail,
                            'price' => $price,
                            'price_format' => $priceFormat,
                            'check_status' => $checkImage
                        );
                        $this->setCustomImageSession($response);
                        //Save image to customer account
                        $customerUploadFilename = explode("/pdp/images/", $uploadedImage);
                        $customerUploadThumbnail = explode("/pdp/images/", $uploadThumbnail);
                        $customerImageData = array(
                            'filename' => end($customerUploadFilename),
                            'original_filename' => $uploads["name"],
                            'thumbnail' => end($customerUploadThumbnail),
                        );
                        Mage::getModel("pdp/customerupload")->saveCustomerUploadImage($customerImageData);
					}
					$this->getResponse()->setBody(json_encode($response));
				}
			}
		}
	}
	//http://create.stephan-brumme.com/qr-code/
    protected function createQRCode($url, $width = 150, $height = 150, $border = 1,
            $error = "L", $https = false, $loadBalance = false) {
        // build Google Charts URL:
        // secure connection ?
        $protocol = $https ? "https" : "http";
        // load balancing
        $host   = "chart.googleapis.com";
        if ($loadBalance)
          $host = abs(crc32($parameters) % 10).".chart.apis.google.com";
        // safe URL
        $url    = urlencode($url);
        // put everything together
        $qrUrl  = "$protocol://$host/chart?chs={$width}x{$height}&cht=qr&chld=$error|$border&chl=$url";
        // get QR code from Google's servers
        $qr     = file_get_contents($qrUrl);
        // optimize PNG and save locally
        $baseDir = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . "upload" . DS;
        $filename = "qrcode-" . time() . ".png";
        $path = $baseDir . $filename;
        $imgIn  = imagecreatefromstring($qr);
        $imgOut = imagecreate($width, $height);
        imagecopy($imgOut, $imgIn, 0,0, 0,0, $width,$height);
        imagepng($imgOut, $path, 9, PNG_ALL_FILTERS);
        imagedestroy($imgIn);
        imagedestroy($imgOut);
        if(file_exists($path)) {
            $mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "pdp/images/upload/";
            return $mediaUrl . $filename;
        }
    }
    //This way much better
    protected function createQRCodeUsingCurl($url, $width = 150, $height = 150, $border = 1,
            $error = "L", $https = false, $loadBalance = false) {
		
		// build Google Charts URL:
        // secure connection ?
        $protocol = $https ? "https" : "http";
        // load balancing
        $host   = "chart.googleapis.com";
        if ($loadBalance)
          $host = abs(crc32($parameters) % 10).".chart.apis.google.com";
        // safe URL
        $url    = urlencode($url);
        // put everything together
        $qrUrl  = "$protocol://$host/chart?chs={$width}x{$height}&cht=qr&chld=$error|$border&chl=$url";
		$baseDir = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . "upload" . DS;
        $filename = "qrcode-" . time() . ".png";
		$path = $baseDir . $filename;
		$ch = curl_init($qrUrl);
		$fp = fopen($path, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		if(file_exists($path)) {
            $mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "pdp/images/upload/";
            return $mediaUrl . $filename;
        }
	}
    public function createQRCodeOnServerAction() {
        $data = $this->getRequest()->getPost();
        $response = array(
            "status" => "error", 
            "message" => "Can not create qrcode. Something when wrong!"
        );
        if(isset($data['content']) && $data['content'] != "") {
            $qrcodeUrl = $this->createQRCodeUsingCurl($data['content']);
            if($qrcodeUrl) {
                $response = array(
                    "status" => "success", 
                    "message" => "QRcode had created successfully!",
                    "filename" => $qrcodeUrl
                );
            }
        }
        echo json_encode($response);
    }
	public function copyImageFromUrlAction() {
		$data = $this->getRequest()->getPost();
        //$data['url'] = "https://scontent.cdninstagram.com/hphotos-ash/t51.2885-15/e15/10593521_932289783466894_1249853250_n.jpg";
		$response = array(
            "status" => "error", 
            "message" => "Can not upload your image to server. Something when wrong!"
        );
        if(isset($data['url']) && $data['url'] != "") {
            $baseDir = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . "upload" . DS;
			$mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "pdp/images/upload/";
			$fileTemp = explode('.', $data['url']);
			$fileExt = end($fileTemp);
			//Fix facebook upload issue
			//http://scontent.xx.fbcdn.net/hphotos-xtp1/v/t1.0-9/12122821_946660232074380_1655781826548584573_n.jpg?oh=672e4355b75b5185b0ddd7f8bce48124&oe=571CD91D
			$tempExt = explode("?", $fileExt);
			$fileExt = $tempExt[0];
			$filename = "social-image-" . time() . "." . $fileExt;
			if (copy($data['url'], $baseDir . $filename)) {
				//Upload fee if exists
				$productId = $_POST['product_id'];
				$price = 0;
				$priceFormat = Mage::helper("pdp")->__("Free");
				if($productId) {
					$productConfig = Mage::helper("pdp")->getProductConfig($productId);
					if(isset($productConfig['clipart_price']) && $productConfig['clipart_price'] > 0) {
						$price = $productConfig['clipart_price'];
						$priceFormat = Mage::helper('core')->currency($price, true, false);
					}
				}
				$response = array(
                    "status" => "success", 
                    "message" => "Image had copied successfully!",
                    "filename" => $mediaUrl . $filename,
					"original_filename" => $data["url"],
					"price" => $price,
					"price_format" => $priceFormat
                );
			}
        }
        echo json_encode($response);
	}
    public function removeImageAction() {
        $response = array(
            "status" => "error", 
            "message" => "Can not remove this image. Something when wrong!"
        );
        $data = $this->getRequest()->getParams();
        if(isset($data['image-path']) && $data['image-path']) {
            //Remove image from session and from database
            $uploadedImages = Mage::getSingleton("core/session")->getCustomUploadImages();
            //Zend_Debug::dump($uploadedImages);
            for($i = 0; $i < count($uploadedImages); $i++) {
                if(isset($uploadedImages[$i]['filename']) && $uploadedImages[$i]['filename'] == $data['image-path']) {
                    unset($uploadedImages[$i]);
                    //Reindex after remove
                    $reindexImageArray = array_values($uploadedImages);
                    $uploadedImages = $reindexImageArray;
                    Mage::getSingleton("core/session")->setCustomUploadImages($uploadedImages);
                    $response = array(
                        "status" => "success", 
                        "message" => "This image has successfull removed!",
                        "filename" => $data['image-path']
                    );
                    break;
                }
            }
            //Remove from database
            if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                //Remove from database
                $tempName = explode("/pdp/images/", $data['image-path']);
                $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
                $customerImageCollection = Mage::getModel("pdp/customerupload")->getCollection();
                $customerImageCollection->addFieldToFilter("customer_id", $customerId);
                $customerImageCollection->addFieldToFIlter("filename", end($tempName));
                $customerImageCollection->getFirstItem()->delete();
                $response = array(
                    "status" => "success", 
                    "message" => "This image has successfull removed!",
                    "filename" => $data['image-path']
                );
            }   
            
        }
        echo json_encode($response);
    }
}