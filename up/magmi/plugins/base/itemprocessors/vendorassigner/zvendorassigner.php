<?php
//require_once("../integration/inc/magmi_datapump.php");
class ZVendorAssigner extends Magmi_ItemProcessor
{		
	protected $sourceSkus;
	
	public function getPluginInfo()
	{
		return array(
            "name" => "Vendor Assigner",
            "author" => "Harshil Patwa",
            "version" => "0.0.1",
            "url"=>""
            );
	}
	
	public function initialize($params){
		$_vendor_id = $this->getVendorIDFromCLI();
		if($_vendor_id!=''){	
			$update_sql="UPDATE ".$this->tablename("udropship_vendor_product")." SET stock_qty = 0, status = 0 where vendor_id = '".$_vendor_id."'";
			$this->update($update_sql);	
		}		
	}	

	public function getPluginParamNames()
	{
		return array();
	}

	static public function getCategory()
    {
        return "Input Data Preprocessing";
    }
	
	public function beforeImport() {
		$this->log("Starting vendor product assigner ","startup");	
	}

    public function processItemAfterId(&$item, $params = null)
    {		
		$vendor_available_field = array("vendor_id", "product_id", "priority", "carrier_code","vendor_sku","vendor_cost","stock_qty","backorders","shipping_price","status","reserved_qty","avail_state","avail_date","vendor_title","vendor_price");		
	
		$vendor_data = array();
				
		foreach($item as $key=>$val)
		{				
			if (in_array($key, $vendor_available_field)) {
				$vendor_data[$key] = $val;
			}			
		}
		
		$vendor_data['vendor_id'] = '';
		if(PHP_SAPI === 'cli'){
			if($this->getVendorIDFromCLI()!=''){
				$vendor_data['vendor_id'] = $this->getVendorIDFromCLI();
			}
		}
		
		if(isset($item['udropship_vendor']) && $item['udropship_vendor']!=''){
			$vendor_data['vendor_id'] = $item['udropship_vendor'];
		}
		
		if($vendor_data['vendor_id']==''){
			$this->log("Vendor ID not found", "error");
			return;
		}
		
		$vendor_data['product_id'] = $params['product_id'];
		
		$col = '';
		$val = '';
		foreach($vendor_data as $column=>$value){
			if($value!='' && $column!=''){
				$col = $col.",".$column;			
				$val = $val.",'".$value."'";
			}			
		}		
		$col = ltrim($col, ',');
		$val = ltrim($val, ',');
		
		$sql = 'INSERT INTO ' . $this->tablename("udropship_vendor_product").' ('.$col.') VALUES ('.$val.')'; 
		try{
			$this->insert($sql);			 
		} catch (Exception $e) {
			
			$sql = "SELECT * FROM " . $this->tablename("udropship_vendor_product") . " where vendor_id=".$vendor_data['vendor_id']." AND product_id=".$vendor_data['product_id'];
			$stmt = $this->select($sql);
			$n = 0;
			while ($result = $stmt->fetch()) {				
				$update_data= '';
				unset($vendor_data['vendor_id']);
				unset($vendor_data['product_id']);
				unset($vendor_data['vendor_product_id']);
				foreach($vendor_data as $column_name => $value){
				   $update_data .= $column_name."='". $value."', ";
				}
				$update_data = rtrim($update_data, ", ");				
				$update_sql="update ".$this->tablename("udropship_vendor_product").' SET '.$update_data." where vendor_product_id=".$result["vendor_product_id"];				
				
				$this->update($update_sql);			
				$n++;
				break;
			}				
			
			if ($n == 0) {
				$this->log($e->getMessage(), "error");
				return false;
			}						
		}
	}

	public function getVendorIDFromCLI(){	
		$argv = $_SERVER['argv'];
		foreach($argv as $data){        
			$argument = explode("=",$data);
			if($argument[0]=="-udropship_vendor")
			{					
				if(is_numeric($argument[1])){
					return $argument[1];	
				}else{
					return '';
				}
			}
		}   		
		return '';
	}	
}


