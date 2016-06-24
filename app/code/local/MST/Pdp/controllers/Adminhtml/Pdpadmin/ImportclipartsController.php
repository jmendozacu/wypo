<?php
class MST_Pdp_Adminhtml_Pdpadmin_ImportClipartsController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed() {
        return true;
    }
    public function editAction() { 
        $this->loadLayout() ->_setActiveMenu('pdp/pdp');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('pdp/adminhtml_importcliparts_edit'))
				->_addLeft($this->getLayout()->createBlock('pdp/adminhtml_importcliparts_edit_tabs'));
        $this->renderLayout();
    }
    function saveAction()
    {
        $resource = Mage::getSingleton('core/resource');
    	$writeConnection = $resource->getConnection('core_write');
        $readConnection = $resource->getConnection('core_write');
    	$table = $resource->getTableName('mst_pdp_images');
    	$field="image_id,image_type,filename,category,color,position,image_name,price,color_type,status,thumbnail,description,image_types,sort_description,image_tag";
        //get old data
        $cliparts = $readConnection->fetchAll('select * from '.$table);
        $oldData = array();
		if(count($cliparts))
		{
			foreach($cliparts as $clipart)
			{
				$oldData[$clipart['filename']] = $clipart['filename'];
			}
		}
		if($dataPost = $this->getRequest()->getPost())
    	{
			$csv_file = $_FILES['file_csv']['tmp_name'];
			if ( ! is_file( $csv_file ) )
       		{
       		    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pdp')->__('File not Found'));
                 $this->_redirect('*/*/edit');
                 return;
       		}
			// $ext = end(explode('.',$_FILES['file_csv']['name']));
			$ext = substr($_FILES['file_csv']['name'], strrpos($_FILES['file_csv']['name'], '.') + 1);
			if($ext != 'csv')
			{
               Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pdp')->__('Please upload csv file'));
                $this->_redirect('*/*/edit');
                return;
			}
			$query = '';
			if (($handle = fopen( $csv_file, "r")) !== FALSE)
			{
				$i = 0;
    			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
    			{
					$i++;
					if($i == 1)
					{
						continue;
					}
    			    //list valibeal
                    $fileName = $data[0];
                    $category = $data[1];
					$imageName = $data[2];
					$price = $data[3];
					$status = $data[4];
                    $position = $data[5];
					$thumbnail = $data[6];
                    //Try to create thumbnail for this image when export, if thumbnail field is empty
                    if($thumbnail == "") {
                        try {
                            //Auto Create clipart thumbnail
                            $basePath = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . 'artworks' . DS. $fileName;
                            $newPath = Mage::getBaseDir('media') . DS . "pdp" . DS . "images" . DS . "artworks" . DS . "resize" . DS;
                            $_thumbOptions = array(
                                'width' => 150,
                                'height' => 150,
                                'media-url' => 'resize/'
                            );
                            $artworkThumbnail = Mage::helper("pdp/upload")->resizeImage($basePath, $newPath, $_thumbOptions);
                            if($artworkThumbnail) {
                                $thumbnail = $artworkThumbnail;
                            }
                            //End auto create thumbnail
                        } catch(Exception $error) {

                        }   
                    }
                    //End create thumbnail
                    $description = $data[7];
                    $sortDescription = $data[8];
                    $imageTag = $data[9];
    			    if(trim($fileName) == '')
                    continue;
					if(array_key_exists($fileName,$oldData))
					{
						$query .= "UPDATE {$table} SET category = '{$category}', position = '{$position}', image_name = '{$imageName}', price = '{$price}', thumbnail = '{$thumbnail}', description = '{$description}', sort_description = '{$sortDescription}', status = '{$status}', image_tag = '{$imageTag}'   WHERE filename = '{$fileName}';";
						continue;
					}
					$query .= "INSERT INTO {$table} ({$field}) VALUES (NULL,'','{$fileName}','{$category}','','{$position}','{$imageName}','{$price}','','{$status}','{$thumbnail}','{$description}', '','{$sortDescription}', '{$imageTag}');";
					
				}
    			fclose($handle);
			}
			if($query !='')
			{
				$writeConnection->query($query);
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pdp')->__('Import data success'));
                $this->_redirect('*/adminhtml_pdp/image');
                return;
			}
			else
			{
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pdp')->__('No data to import!'));
                $this->_redirect('*/adminhtml_importcliparts/edit');
                return;
			}
			
        }
    }
}