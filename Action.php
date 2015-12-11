<?php
class TeThumbnail_Action extends Typecho_Widget implements Widget_Interface_Do{
	public function action(){
		$options = Typecho_Widget::widget('Widget_Options');
		if(!isset($options->plugins['activated']['TeThumbnail'])) exit;
		$siteUrl = $options->siteUrl;
		$options = $options->plugin('TeThumbnail');
		$url = $this->request->get('url');
		$size = $this->request->get('size');
		
		if($size){
			$size = explode('x',$size);
		}else{
			$size = array($options->width,$options->height,);
		}
		$path = __TYPECHO_ROOT_DIR__.'/'.$url;
		
		if(!is_file($path)) exit;
		
		require_once ('Image.php');
		$image = new Image();
		$image->open($path);
		$type = $image->type();
		
		$image->thumb($size[0], $size[1],3);
		
		header('Content-Type:image/'.$type.';');
		
		//输出图像
		if('jpeg' == $type || 'jpg' == $type){
			// 采用jpeg方式输出
			imagejpeg($image->showImg());
		}elseif('gif' == $type){
			imagegif($image->showImg());
		}else{
			$fun  =   'image'.$type;
			$fun($image->showImg());
		}
	}

}
