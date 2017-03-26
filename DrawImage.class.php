<?php
/**
 * @author yanghuichao
 * @date 2014/7/15
 * @brief  画图
 *  
 **/
class DrawImage{
	//y值处理函数 
	public static function line_point_y($num,$width,$high,$max_num_add,$min_num_add,$y_pxdensity){ 
		if($max_num_add==$min_num_add)return $high;
		$return=$high-floor(($num-$min_num_add+$y_pxdensity)/(($max_num_add-$min_num_add)/$high)); 
		return $return; 
	} 
	public static function line_stats_pic($value_y,$width,$high,$strong=1,$fix=0,$filename){ 
		//参数处理 
		$allnum=sizeof($value_y); 
		$max_num=max($value_y); //最大值 
		$min_num=min($value_y); //最小值 
		$limit_m=$max_num-$min_num; //极差 
		$max_num_add=$max_num+$limit_m*0.1; //轴最大值 
		$min_num_add=$min_num-$limit_m*0.1; //轴最小值 
		$limit=$max_num_add-$min_num_add; //极差-坐标轴y 
		$y_pxdensity=($max_num_add-$min_num_add)/$high; //y轴密度 
		$x_pxdensity=floor($width/$allnum); //x轴密度 
		reset($value_y); //将数组指针归零 
		$i=0; 
		foreach($value_y as $val){ 
			$point_y[$i]=self::line_point_y($val,$width,$high,$max_num_add,$min_num_add,$y_pxdensity); 
			$i++; 
		} 
		$zero_y=self::line_point_y(0,$width,$high,$max_num_add,$min_num_add,$y_pxdensity); //零点的y值 
		$empty_size_x=(strlen($max_num) > strlen($min_num) ? strlen($max_num) : strlen($min_num))*5+3; //左边空白 
		
		//图片流开始 
		//header("Content-type:image/png"); 
		$pic=imagecreate($width+$empty_size_x+10,$high+13); 
		imagecolorallocate($pic,255,255,255); //背景色 
		$color_1=imagecolorallocate($pic,30,144,255); //线条色 
		$color_2=imagecolorallocate($pic,0,0,0); //黑色 
		$color_3=imagecolorallocate($pic,194,194,194);//灰色 
		//绘制网格 
		imagesetthickness($pic,1); //网格线宽 
		$y_line_width=24;//floor($width/100); //纵网格线数目 
		$y_line_density=$y_line_width==0 ? 0 :floor($width/$y_line_width); //纵网格线密度 
		$point_zero_y=$zero_y > $high ? $high : $zero_y; 
		imagestring($pic,1,$empty_size_x-1,$high+4,"0",$color_2); //零点数轴标记 
		imagestring($pic,2,$empty_size_x+5,$point_y[0]-15,$value_y[0],$color_2); 
		for($i=1;$i < $y_line_width;$i++){ //绘制纵网格线 
			imagesetthickness($pic,1); //网格线宽 
			imageline($pic,$y_line_density*$i+$empty_size_x,0,$y_line_density*$i+$empty_size_x,$high,$color_3); 
			imagesetthickness($pic,2); //轴点线宽 
			//imageline($pic,$y_line_density*$i+$empty_size_x,$point_zero_y-4,$y_line_density*$i+$empty_size_x,$point_zero_y,$color_2); 
			imagestring($pic,1,$y_line_density*$i+$empty_size_x,$high+4,$i,$color_2); //x轴标记
			imagestring($pic,2,$y_line_density*$i+$empty_size_x+5,$point_y[$i]-15,$value_y[$i],$color_2); //y轴标记
			//imagestring($pic,1,100*$i+$empty_size_x-5,$high+4,$allnum/$y_line_width*$i,$color_2); //数轴标记
		} 
		
		//绘制轴线 
		imagesetthickness($pic,2); //轴线宽 
		imageline($pic,1+$empty_size_x,0,1+$empty_size_x,$high,$color_2); 
		if($zero_y > $high){ //x轴位置 
			imageline($pic,0+$empty_size_x,$high,$width+$empty_size_x,$high,$color_2); 
		}else{ 
			imageline($pic,0+$empty_size_x,$zero_y,$width+$empty_size_x,$zero_y,$color_2); 
		} 
		//产生折线 
		$point_x=0; 
		$j=0; 
		imagesetthickness($pic,$strong); //线条粗细 
		while($j+1 < $allnum){ 
			imageline($pic,$point_x+2+$empty_size_x,$point_y[$j],$point_x+$x_pxdensity+2+$empty_size_x,$point_y[$j+1],$color_1); 
			$point_x+=$x_pxdensity; 
			$j++; 
		} 
		
		//echo '<br>';
		imagepng($pic,$filename); 
		imagedestroy($pic); 
	} 

}

?>