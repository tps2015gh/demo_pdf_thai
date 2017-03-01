<?php 
//====================================================
// CodeIgniter get base_uri , both HTTP and HTTPS  
// from stackoverflow.com forum 
//====================================================
function base_url_ci(){
   $path1 = "" ; 
   $pth = explode("/",$_SERVER['REQUEST_URI']);
   $path1 = $pth[1];
   $path1 .= "/index.php";

  return sprintf(
    "%s://%s:%s/%s",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
    $_SERVER['SERVER_NAME']
	,$_SERVER['SERVER_PORT']
    , $path1 
  );
}

// replace any non-ascii character with its hex code.
// from  http://stackoverflow.com/questions/1162491/alternative-to-mysql-real-escape-string-without-connecting-to-db
function escape($value) {
    $return = '';
    for($i = 0; $i < strlen($value); ++$i) {
        $char = $value[$i];
        $ord = ord($char);
        if($char !== "'" && $char !== "\"" && $char !== '\\' && $ord >= 32 && $ord <= 126)
            $return .= $char;
        else
            $return .= '\\x' . dechex($ord);
    }
    return $return;
}


// set include path 
$APPPATH = 
define ('BASEPATH', 'C:\\xampp\\htdocs\\' . $APPPATH . '\\');
$fpdf_path = "C:\\xampp\\htdocs\\" . $APPPATH . "\\vendor\\setasign\\" ;
$fpdf_path = "C:\\xampp\\htdocs\\" . $APPPATH . "\\vendor\\setasign\\" ;
$template_pdf = 'C:\\xampp\\htdocs\\' . $APPPATH . '\\pdf_template\\tp1.pdf';
$app_path = "C:\\xampp\\htdocs\\" . $APPPATH . "\\" ;

require_once( $fpdf_path .  'fpdf\\fpdf.php');
require_once($fpdf_path .'fpdi\\fpdi.php'); 


$pdf=new FPDI();    /* ONLY FPDI */
$pageCount = $pdf->setSourceFile($template_pdf );
$tplIdx = $pdf->importPage(1, '/MediaBox');
$pdf->AddPage();

$pdf->useTemplate($tplIdx );               // <= use template in full page 
//$pdf->useTemplate($tplIdx, 10, 10, 200); // <= use template with specific coordinate 

$txutf8 =  'TEST DATA ทดสอบ ';
$tx874 =  iconv('UTF-8', 'windows-874', $txutf8); 
//$tx874 =  $txutf8 ; //iconv('UTF-8', 'windows-874', $txutf8); 

function utf2thai($txutf8){
  $tx874 =  iconv('UTF-8', 'windows-874', $txutf8); 
}
function addfont_setfont($pdf,$size=14 ){
  $pdf->AddFont('angsa','','angsa.php'); 
  $pdf->SetFont('angsa','',$size);
}
display_pdf($pdf, $a_valxy,false );




display_pdf($pdf, $a_valxy,false );

function _tx( $colnum  ){
  global $a_valxy ,$is_value  , $config;
  $is_value = IS_VALUE  ;  

    $txstr = _txval($colnum);
    $txval = "" ;

    if(in_array($colnum , [22,23,24,26] )) {
        $txval  = (int)$txstr;  

    // Date  DDMMYYYY
    }else if( in_array($colnum , [32,34] )) {
        $dd = substr($txstr,0,2);
        $mm = substr($txstr,2,2);
        $yyyy = substr($txstr,4,4);
        //console.log("str = " + txstr );
        $dmy = $dd . "/" .  $mm   . "/" . $yyyy ;  
        //console.log("dmy = " + dmy );
        $txval = $dmy    ;
    // percent in format 00000  , abcde = abc.de percent  
    //   decimal  (5,2)  , total size is 5 
    }else if( in_array($colnum , [33,35] )) {
        //txstr = "12345";
        $lf = substr( $txstr , 0,3) ; 
        $ri = substr( $txstr , 3,2) ;
        $perc_str = $lf . "." . $ri ; 
        $perc_float = (float)$perc_str;
        $txval = $perc_float  ; // + "@" + txstr ;     // decimal 8,2 (total = 10)
    }else if( in_array($colnum , [28,29,30,38] )) {
      $txval = pv28($txstr);
    }else if( in_array($colnum , [27,25] )) {
        //txstr = "12345";
        $lf = substr($txstr,0,6);
        $ri = substr($txstr,6,2);
        $perc_str = $lf . "." . $ri ;
        $float1  = (float)$perc_str;
        $txval = number_format($float1,2);
         
        //var perc_float = parseFloat(perc_str);
        //txval =  perc_str ;  //perc_float ;         
    }else{
        $txval = $txstr ; 
    }
    
    $str_out = "";
  //echo "<br>config=[" . print_r($config,true) . "]" ; 
  //print_r($config);
    if($config->IS_INDEX){
        $tx  = $colnum . ":" . $txval  ; 
        return $tx ; 
    }else{
        $tx = $txval ; 
        return $tx ; 
    } 
}

function _txval( $colnum ){
  global  $a_valxy;
    $valxy =  $a_valxy[$colnum];

  //Convert Array To Object 
  if(is_array($valxy)){
    $valxy = (object)$valxy; 
  }

  // Check Show or NOT 
  if($valxy->show == true ){
    return $valxy->text ; 
  }else{
    return "" ; 
  }
}

function display_pdf($pdf,$a_valxy  ){
  global $config ;
  $is_show_colnum = false  ;            /* FOR DISPLAY Column number before TEXT */

  $fontsize1 = 14;
  addfont_setfont($pdf,$fontsize1);
  
  if($config->IS_SHOW_RULER){
    // display grid 
    set_text_red($pdf);
    for($x=0 ; $x <= 200;$x+=10){
      writexy_utf2thai($pdf,$x,1, "". $x );
    }
    for($y=0 ; $y <= 270;$y+=10){
      writexy_utf2thai($pdf,0,$y, "". $y );
    }   
  }


  if ($config->IS_SHOW_DATA){
    set_text_blue($pdf);
    foreach([1,2,5,6,7,8,9,10,11,12,13,14,15,16,17,18] as $colnum ){
      $valxy = $a_valxy[$colnum];
      $tx = _tx($colnum);
      if($is_show_colnum){
        $tx = "$colnum: " . $tx ; 
      }
      writexy_utf2thai($pdf,$valxy->x,$valxy->y, $tx);
    }

    $tx = _tx(3) . _tx(4);
    writexy_utf2thai($pdf,33,34, $tx );

    $tx = _tx(19);
    $txval = _txval(19);
    if($config->IS_SHOW_CHECKBOX_VAL){
      writexy_utf2thai( $pdf , 36-2+1.2 ,84+3  , "$tx"  );
    }
    $a_conf_19 =  [
              ["val"=>1 , "pos"=>["x" => 44   , "y"=>84.2+4   ]]
                        , [  "val"=>2 , "pos"=>["x" => 62.4 , "y"=>84.2+4   ]] 
                        , [  "val"=>3 , "pos"=>["x" => 83   , "y"=>84.2+4   ]]
                        ];  
    do_checkbox($pdf , $txval, $a_conf_19) ; 

    $tx = _tx(20);
        $txval = _txval(20);
    if($config->IS_SHOW_CHECKBOX_VAL){  
          writexy_utf2thai( $pdf , 115 ,84.2+4, $tx );
    }        
        $a_conf_20 =  [
              [  'val'=> 0 , 'pos'=>['x' => 167   , 'y'=>84.2  +4  ]]
                        , [  'val'=> 1 , 'pos'=>['x' => 167-20+4 , 'y'=>84.2 +4  ]] 
                        ];
    do_checkbox($pdf , $txval, $a_conf_20) ; 
    
    if($config->IS_SHOW_NOTUSE){
          $tx = _tx(21);
          writexy_utf2thai($pdf,  138+30 +2-1,125+6.5  +107 -3  , $tx   );
    }

        $tx = _tx(22);
        writexy_utf2thai($pdf, 49-1,127.5+6.5 , $tx   );

        $tx = _tx(23);
        writexy_utf2thai($pdf, 138-1,127.5+6.5 , $tx   );
    
    if($config->IS_SHOW_NOTUSE){
          $tx = _tx(24);
      writexy_utf2thai($pdf, 138+30 +2-1,125+6.5 +107  , $tx   );

      $tx = _tx(25);
      writexy_utf2thai($pdf, 138+30 +2-1,125+3+6.5 +107, $tx   );

      $tx = _tx(26);
      writexy_utf2thai($pdf, 138+30 +2-1,125+3*2+6.5 +107, $tx   );

      $tx = _tx(27);
      writexy_utf2thai($pdf, 138+30  +2-1, 125+3*3 + 6.5 +107, $tx   );
    }

        $tx = _tx(28);
        writexy_utf2thai($pdf,138+30 -1, 175 + 6.5+3 , $tx   );

        $tx = _tx(29);
        writexy_utf2thai($pdf,138+30+4 -1, 175+5.5 + 6.5+3 , $tx   );

        $tx = _tx(30);
        writexy_utf2thai($pdf,138+30 -1, 175+5.5*2 + 6.5+3 , $tx   );
    
        $tx = _tx(31);
        $txval = _txval(31);        
    if($config->IS_SHOW_CHECKBOX_VAL){
      writexy_utf2thai( $pdf , 50-2 , 197-0.4 +10-2, $tx );
    }        
        $a_conf_31 =  [
              [  'val'=> 0 , 'pos'=>['x' => 57+18   , 'y'=>197-0.4+10  ]]
                        , [  'val'=> 1 , 'pos'=>['x' => 57 , 'y'=>197-0.4+10  ]] 
                        ];
    do_checkbox($pdf , $txval, $a_conf_31) ; 
    
        $tx = _tx(32);
        $txval = _txval(32);
        writexy_utf2thai($pdf,114 -1, 196 + 6.5+3+2-0.5 , $tx   );    

        $tx = _tx(33);
        $txval = _txval(33);
    //if($config->IS_SHOW_CHECKBOX_VAL){    
        writexy_utf2thai($pdf,114+4 -1 +4, 196+5.5 + 6.5+3+2-0.5 , $tx   );
    //}   
    if($config->IS_SHOW_NOTUSE){
      $tx = _tx(34);
      $txval = _txval(34);
      writexy_utf2thai($pdf,170 -1, 210+6-0.5 +25 + 6.5+3+2 , $tx   );    

      $tx = _tx(35);
      $txval = _txval(35);
      writexy_utf2thai($pdf,170  -1, 210+6+3 +25 + 6.5+3+2 , $tx   );   
    }

    //===============================
        $tx = _tx(36);
        $txval = _txval(36);
    if($config->IS_SHOW_CHECKBOX_VAL){    
      writexy_utf2thai( $pdf , 18 , 200-0.3+10+1, $tx );
    }        
        $a_conf_36 =  [
        [  'val'=> 0 , 'pos'=>['x'=>15+18-3,'y'=>197-0.4+5+11 ]]
            , [  'val'=> 1 , 'pos'=>['x'=>15+58-6-10-8,'y'=>197-0.4+5+11 ]] 
          ];
    do_checkbox($pdf , $txval, $a_conf_36) ; 


    if($config->IS_SHOW_NOTUSE){
      $tx = _tx(37);
      $txval = _txval(37);
      //ctx.fillText( tx , cm2x(15+15),cm2y( 210+2 ));
      writexy_utf2thai($pdf, 170 -1 , 212+11 + 6.5+3+2 +25 , $tx   );
    }

        $tx = _tx(38);
        $txval = _txval(38);
        //ctx.fillText( tx , cm2x(75),cm2y( 207 ) );
    writexy_utf2thai($pdf,75 -1+2 , 207 + 6.5+3+2 , $tx   );


  }// is show data
}// funcx

function do_checkbox( $ctx , $val  , $a_conf ){
  global  $config;  
      $is_debug =  $config->IS_SHOWPOS_CHECKBOX  ;
    //$is_debug =true; 
        for($i = 0 ;  $i < count($a_conf)  ; $i++  ){
            //console.log(" loop i= " + i );
                $pos =  $a_conf[$i]["pos"] ;
                ///console.log("pos="  + pos);
                if($a_conf[$i]["val"] == $val || $is_debug == true ){
                    if($is_debug){ 
            $x = $a_conf[$i]["val"]  .  ":X"; 
          }else{
                      $x = "X" ; 
            //$x = "X" . "/isDebug=[$is_debug]";
          }
                    //ctx.fillText( x , cm2x(pos.x),cm2y(pos.y) );
                    writexy_utf2thai( $ctx, $pos["x"] , $pos["y"] , $x );
                }
        }
}
 

function writexy_utf2thai_xyobj($pdf,$x,$y , $xyobj){
  if($xyobj->type == 'column'){
      $text_utf8 = $xyobj->cname ; 
      writexy_utf2thai($pdf,$x,$y, "[$text_utf8]" );
  }elseif($xyobj->type == '@compute'){
      $text_utf8 = $xyobj->cname ; 
      writexy_utf2thai($pdf,$x,$y, "[$text_utf8]" );
  }
}

function writexy_utf2thai($pdf,$x,$y ,$txutf8){
  //$txutf8 = strtr($txutf8,["\n"=>"" , "\r"=>"","\t"=>" "]);
  $pdf->SetXY($x,$y);
  $tx874 = iconv('UTF-8', 'windows-874', $txutf8);
  $pdf->Write(0, $tx874);
}

function set_text_blue($pdf){
  $pdf->SetTextColor(0, 0, 255);
}
function set_text_red($pdf){
  $pdf->SetTextColor(255, 0, 0);
}
function set_text_silver($pdf){
  $pdf->SetTextColor(100, 100, 100);
}
function cellxy($pdf, $x,$y, $a, $txutf8){
  $pdf->SetXY($x,$y);
  $tx874 = iconv('UTF-8', 'windows-874', $txutf8);
  $pdf->Cell($a,0,$tx874);
}

set_text_blue($pdf);
$pdf->Output();


