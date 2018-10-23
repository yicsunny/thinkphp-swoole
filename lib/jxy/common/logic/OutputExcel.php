<?php
namespace jxy\common\logic;

/** PHPExcel_IOFactory */

//import('ORG.Util.PHPExcel.PHPExcel_IOFactory');

class OutputExcel {

    public $type = 'Excel5'; // 'Excel2007'
    public $title = 'sheet1';
    public $filename = "拓美互娱科技有限公司";
    public $header;
    public $content;
    public $template = false; //is loading xls
    public $isBorder = false; //if output border
    public $maxCol = 0;
    public $maxRow = 0;
    public $row = 1;
    public $setBorder = ["start"=>"A1", "fnt"=>"all"];

    public function output(){

        $this->init();

        if($this->template){
            $objReader = \PHPExcel_IOFactory::createReader($this->type);
            $objPHPExcel = $objReader->load($this->template);
        }else{
            $objPHPExcel = new \PHPExcel();
        }

        \PHPExcel_CachedObjectStorageFactory::cache_to_discISAM;

        // Create new PHPExcel object
        $objSheet = $objPHPExcel->setActiveSheetIndex(0);
        $col = 0;
        $row = $this->row;
        if ( isset($this->header)){
            foreach( $this->header as $v ){
                $cell = \PHPExcel_Cell::stringFromColumnIndex( $col ) . $row;
                $objSheet->setCellValue( $cell, $v);
                $col++;
            }
            $row++;
            $this->maxCol = $col;
            $col = 0;
        }

        foreach ($this->content as $rowValue) {
            foreach ( $rowValue as $_v ) {
                $cell = \PHPExcel_Cell::stringFromColumnIndex( $col ) . $row;
                $objSheet->setCellValue( $cell, $_v);
                $col++;
            }
            $row++;
            if($this->maxCol < $col){
                $this->maxCol = $col;
            }
            $col = 0;
        }
        $this->maxRow = $row;
        if($this->isBorder){
           $this->setBorderAll($objSheet);
        }
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle( $this->title );
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $this->browserExport($this->type, $this->filename);
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, $this->type);
        $objWriter->save('php://output');
    }

    public function browserExport($type, $filename){

        $ua = $_SERVER["HTTP_USER_AGENT"];

        $encoded_filename = urlencode($filename);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);

        header('Content-Type: application/octet-stream');
        
        if (preg_match("/MSIE/", $ua)) {  
            
             $filename = $encoded_filename;

        } else if (preg_match("/Firefox/", $ua)){  

            $filename = '"utf8\'\'' . $filename . '"';

        }

        if( $type == "Excel5" ){
            // Redirect output to a client’s web browser (Excel5)
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        }else{
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        }
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');
            // If you're serving to IE over SSL, then the following may be needed
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0
    }

    public function getColTag($col_num){
        return \PHPExcel_Cell::stringFromColumnIndex( $col_num );
    }

    protected function init(){

        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
        date_default_timezone_set('Asia/Shanghai');

        //$objReader = PHPExcel_IOFactory::createReader('Excel5');
        //$objPHPExcel = $objReader->load("/var/www/teplates/financial/recmodel.xls");
        
    }

    protected function setBorderAll(&$objSheet){

        $styleArray = array(  
                'borders' => array(  
                'allborders' => array(  
                //'style' => PHPExcel_Style_Border::BORDER_THICK,//边框是粗的  
                'style' => \PHPExcel_Style_Border::BORDER_THIN,//细边框  
                //'color' => array('argb' => 'FFFF0000'),  
                ),  
            ),  
        );
        $col = $this->maxCol - 1;
        $row = $this->maxRow - 1;
        $objSheet->getStyle( $this->setBorder["start"] . ':' . $this->getColTag($col) . $row)->applyFromArray($styleArray);
    }

    protected function setBorderTop(&$objSheet){

        $styleArray = array(  
                'borders' => array(  
                'allborders' => array(  
                //'style' => PHPExcel_Style_Border::BORDER_THICK,//边框是粗的  
                'style' => \PHPExcel_Style_Border::BORDER_THIN,//细边框  
                //'color' => array('argb' => 'FFFF0000'),  
                ),  
            ),  
        );
        $col = $this->maxCol - 1;
        $row = $this->maxRow - 1;
        $objSheet->getStyle( $this->setBorder["start"] . ':' . $this->getColTag($col) . $row)->applyFromArray($styleArray);

    }

}