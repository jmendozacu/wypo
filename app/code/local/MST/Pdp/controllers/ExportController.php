<?php
class MST_Pdp_ExportController extends Mage_Core_Controller_Front_Action {
	public function downloadAllAction(){
		$request = $this->getRequest()->getPost();
        $response = array(
            'status' => 'error',
            'message' => 'Unable to create zip and download exported files!'
        );
        if(isset($request['images']) && $request['images'] != "") {
            $images = explode(",", $request['images']);
            $finalFiles = $images;
            //Create pdf using both png and svg format
            $baseDir = Mage::getBaseDir("media") . DS . "pdp" . DS . "download" . DS;
            foreach($images as $filename) {
                $temp = explode(".", $filename);
                if(end($temp) == "svg") {
                    $pdfFilename = $temp[0] . "_svg.pdf";
                    $result = $this->createPDFFromSVG($baseDir . $filename, $pdfFilename);
                } else {
                    $pdfFilename = $temp[0] . "_png.pdf";
                    $result = $this->createPDFFromPng($baseDir . $filename, $pdfFilename);
                }
                if($result) {
                    $finalFiles[] = $result;
                }
            }
            $orderInfo = "";
            if(isset($request['order_info'])) {
                $orderInfo = $request['order_info'];
            }
            $zipResult = $this->createZipToDownload($finalFiles, $orderInfo);
            if($zipResult['status'] == "success") {
                $response = $zipResult;
            }
        }
        $this->getResponse()->setBody(json_encode($response));
	}
    protected function createZipToDownload($files, $orderInfo) {
        //Create zip folder
        $downloadAllFolder =  Mage::getBaseDir("media") . DS . "pdp" . DS . "download" . DS;
        if (!file_exists($downloadAllFolder)) {
            mkdir($downloadAllFolder, 0777);
        }
        if (file_exists($downloadAllFolder)) {
            $zipFilename = $this->getDownloadFilename($orderInfo);
            $fileBaseDir = Mage::getBaseDir("media") . DS . "pdp" . DS . "download" . DS;
            $zipUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . '/pdp/download/' . $zipFilename;
            
            $zip = new ZipArchive();
            $_zipPath = $downloadAllFolder . $zipFilename;
            $zip->open( $_zipPath, ZipArchive::CREATE );
            //Read order folder
            $directory = $fileBaseDir;
            if( is_dir( $directory ) && $handle = opendir( $directory ) )
            {
                while( ( $file = readdir( $handle ) ) !== false )
                {
                    if(in_array($file, $files)) {
                        $zip->addFile($directory . "/" . $file, $file);
                    }
                }
            }
            $zip->close();
            if(file_exists($_zipPath)) {
                $response = array(
                    "status" => "success",
                    "message" => "Zip file created successfully!",
                    "zip_url" => $zipUrl
                );
            } else {
                $response = array(
                    "status" => "error",
                    "message" => "Zip file not found!"
                );
            }
            return $response;
        }
    }
    public function saveExportImageAction() {
        $data = $this->getRequest()->getPost();
        $exportFormat = "png";
        if(isset($data['format'])) {
            $exportFormat = $data['format'];
        }
        $sideName = "_";
        if(isset($data['side_name'])) {
            $sideName = "_" . $data['side_name'] . '_';
        }
        $response = array(
            'status' => 'error',
            'message' => 'Unable to save base 64 image!'
        );
        if(isset($data['base_code_image'])) {
            $baseCode = $data['base_code_image'];
            $thumbnailDir = Mage::getBaseDir("media") . DS . "pdp" . DS . "download" . DS;
            $thumbnailUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "pdp/images/thumbnail/";
            if (!file_exists($thumbnailDir)) {
                mkdir($thumbnailDir, 0777);
            }
            if (!file_exists($thumbnailDir)) {
                $this->getResponse()->setBody(json_encode($response));
                return;
            }
            if($exportFormat == "svg") {
                $filename = "Design" . $sideName . uniqid() . '.svg';
                $file = $thumbnailDir . $filename;
                file_put_contents($file, $data['base_code_image']);
                if(file_exists($file)) {
                    $response = array(
                        'status' => 'success',
                        'message' => 'Image have been successfully saved!',
                        'filename' => $filename,
                        'thumbnail_path' => $thumbnailUrl . $filename
                    );
                    $this->getResponse()->setBody(json_encode($response));
                }
            } else {
                if($data['format'] === "jpeg") {
                    $data['format'] = "jpg";
                }
                $filename = "Design" . $sideName . uniqid() . '.' . $data['format'];
                $file = $thumbnailDir . $filename;
                if(substr($baseCode,0,4)=='data'){
                    $uri =  substr($baseCode,strpos($baseCode,",")+1);
                    // save to file
                    file_put_contents($file, base64_decode($uri));
                    if(file_exists($file)) {
                        //$thumbnailUrl
                        $response = array(
                            'status' => 'success',
                            'message' => 'Image have been successfully saved!',
                            'filename' => $filename,
                            'thumbnail_path' => $thumbnailUrl . $filename
                        );
                        $this->getResponse()->setBody(json_encode($response));
                    }
                }
            }
        }
    }
    protected function getDownloadFilename($orderInfo) {
        if(!empty($orderInfo)) {
            $filename = 'Order-' . $orderInfo['increment_id'] . "-Item-" . $orderInfo['item_id'] . '-' . time() . '.zip';
        } else {
            $filename = "Al-Exported-Files-". time() .".zip";
        }
        return $filename;
    }
    protected function createPDFFromSVG($svgFile, $filename) {
		if(!file_exists($svgFile)) {
			return false;
		}
		$xml = simplexml_load_file($svgFile);
		$svgFonts = array();
		foreach($xml as $node) {
			try {
				if ($node->text) {
					$textAttr = $node->text->attributes();
					$fontFamily = (string)$textAttr['font-family'];
					if(!in_array($fontFamily, $svgFonts)) {
						$svgFonts[] = $fontFamily;
					}
				}
			} catch(Exception $e) {
	
			}
		}
		if(!empty($svgFonts)) {
			$svgFonts = $this->filterSvgFont($svgFonts);
		}
		$attrs = $xml->attributes();
		$pdfSize = array(floatval($attrs->width), floatval($attrs->height));
        if($pdfSize[0] > 0 && $pdfSize[1] > 0) {
            $svgSizeInMM = $this->getSVGSizeInMM($pdfSize[0], $pdfSize[1]);
            $pdfSize = $svgSizeInMM;
        }
		$pdf = new TCPDF_TCPDF("", "mm", $pdfSize, true, 'UTF-8', false, false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetLeftMargin(0);
		$pdf->SetRightMargin(0);
		$pdf->SetHeaderMargin(0);
		$pdf->SetFooterMargin(0);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		$pdf->SetAutoPageBreak(TRUE, -$pdf->getBreakMargin());
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		//Set Font
		foreach($svgFonts as $font) {
			$pdf->SetFont($font);
		}
		$pdf->AddPage();
		$pdf->ImageSVG($svgFile, $x=0, $y=0, $w=$pdf->getPageWidth(), $h=0, $link='', $align='', $palign='', $border=0, $fitonpage=true);
		$pdf->close();
		//Close and output PDF document
		//header('Content-type: application/pdf');
		//echo $pdf->Output($filename, 'S');
        
        $pdfDir = Mage::getBaseDir("media") . DS . "pdp" . DS . "download" . DS;
        if (!file_exists($pdfDir)) {
            mkdir($pdfDir, 0777);
        } 
        $pdfPath = $pdfDir . $filename;
		$pdf->Output($pdfPath, 'F');
        if(file_exists($pdfPath)) {
			return $filename;
		}
        return false;
	}
    
    //Get DPI to get exactly size of pdf
    private function getPngDPI($pngFile) {
        //Some code to get dpi here
        return 96;
    }
    private function pixelToMM ($pngFile, $pixel) {
        //mm = (pixels * 25.4) / dpi
        //pixels = (mm * dpi) / 25.4
        //there are 25.4 millimeters in an inch
        //Reference Link: http://www.dallinjones.com/2008/07/how-to-convert-from-pixels-to-millimeters/
        $dpi = $this->getPngDPI($pngFile);
        $mm = ($pixel * 25.4) / $dpi;
        return $mm;
    }
    private function getSVGSizeInMM($svgWidthInPixel, $svgHeightInPixel) {
        $dpi = $this->getPngDPI(null);
        return array(
            ($svgWidthInPixel * 25.4) / $dpi,
            ($svgHeightInPixel * 25.4) / $dpi
        );
    }
    private function getImageSize($pngFile) {
        //Return mm
        $pngSize = Mage::helper("pdp/upload")->getImageSize($pngFile);
        if(is_array($pngSize)) {
            $pngInMM = array();
            $pngInMM[0] = $this->pixelToMM($pngFile, $pngSize[0]);
            $pngInMM[1] = $this->pixelToMM($pngFile, $pngSize[1]);
            return $pngInMM;
        }
        return false;
    }
    protected function createPDFFromPng($pngFile, $filename) {
		if(!file_exists($pngFile)) {
			return false;
		}
		$pdfSize = array();//array(floatval($attrs->width), floatval($attrs->height));
        //png size 
        $pngSize = $this->getImageSize($pngFile);
        if(is_array($pngSize)) {
            $pdfSize[0] = $pngSize[0];
            $pdfSize[1] = $pngSize[1];
        }
		$pdf = new TCPDF_TCPDF("", "mm", $pdfSize, true, 'UTF-8', false, false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetLeftMargin(0);
		$pdf->SetRightMargin(0);
		$pdf->SetHeaderMargin(0);
		$pdf->SetFooterMargin(0);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		$pdf->SetAutoPageBreak(TRUE, -$pdf->getBreakMargin());
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->AddPage();
		$pdf->Image($pngFile, $x=0, $y=0, $w=$pdf->getPageWidth(), $h=0, $link='', $align='', $palign='', $border=0, $fitonpage=true);
		$pdf->close();
		//Close and output PDF document
		//header('Content-type: application/pdf');
		//echo $pdf->Output($filename, 'S');
        
        $pdfDir = Mage::getBaseDir("media") . DS . "pdp" . DS . "download" . DS;
       
        if (!file_exists($pdfDir)) {
            mkdir($pdfDir, 0777);
        } 
        
        $pdfPath = $pdfDir . $filename;
		$pdf->Output($pdfPath, 'F');
        if(file_exists($pdfPath)) {
			return $filename;
		}
        return false;
	}
	protected function filterSvgFont($fonts) {
		$validFont = array();
		$tcpdfFontPath = Mage::getBaseDir("lib") . DS . "TCPDF" . DS . "fonts" . DS;
		$tcpdfFonts = array();
		$directory = $tcpdfFontPath;
		if( is_dir( $directory ) && $handle = opendir( $directory ) )
		{
			while( ( $file = readdir( $handle ) ) !== false )
			{
				$temp = explode(".", $file);
				if(end($temp) == "php") {
					$tcpdfFonts[] = str_replace(".php", "", $file);
				}
			}
		}
		//Compare font
		foreach($fonts as $font) {
			$fontName = trim(strtolower($font)); 
			if(in_array($fontName, $tcpdfFonts)) {
				$validFont[] = $fontName;
			}
		}
		return $validFont;
	}
}