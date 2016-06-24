<?php
class MST_Pdp_Adminhtml_Pdpadmin_ImportcategoriesController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed() {
        return true;
    }
    public function editAction() { 
        $this->loadLayout() ->_setActiveMenu('pdp/pdp');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('pdp/adminhtml_importcategories_edit'))
				->_addLeft($this->getLayout()->createBlock('pdp/adminhtml_importcategories_edit_tabs'));
        $this->renderLayout();
    }
    function saveAction()
    {
        $resource = Mage::getSingleton('core/resource');
    	$writeConnection = $resource->getConnection('core_write');
        $readConnection = $resource->getConnection('core_write');
    	$table = $resource->getTableName('mst_pdp_artwork_category');
        //get old data
        $catogories = $readConnection->fetchAll('select * from '.$table);
        $oldData = array();
		if($catogories)
		{
			foreach($catogories as $catogory)
			{
				$oldData[$catogory['title']] = $catogory['title'];
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
                    $title = $data[0];
					$imageTypes = $data[1];
					$position = $data[2];
					$status = $data[3];
    			    if(trim($title) == '')
                    continue;
					if(array_key_exists($title,$oldData))
					{
						$query .= "UPDATE {$table} SET status = '{$status}', position = '{$position}', thumbnail = '', parent_id = '0', image_types = '{$imageTypes}' WHERE title = '{$title}';";
						continue;
					}
					$query .= "INSERT INTO {$table} VALUES (NULL,'{$title}','{$status}','{$position}','','0','');";
					
				}
    			fclose($handle);
			}
			if($query !='')
			{
				$writeConnection->query($query);
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pdp')->__('Import data success'));
                $this->_redirect('*/adminhtml_artworkcate/index');
                return;
			}
			else
			{
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pdp')->__('No data to import!'));
                $this->_redirect('*/adminhtml_importcategories/edit');
                return;
			}
			
        }
    }
}