<?php

require_once 'excel_reader2.php';
class ExcelReader{
	
	public static $data = NULL;
	public function reader($filename){
	
		error_reporting(E_ALL ^ E_NOTICE);
		self::$data = new Spreadsheet_Excel_Reader();
		self::$data->read($filename);
			
		return self::$data;
	}

}
?>