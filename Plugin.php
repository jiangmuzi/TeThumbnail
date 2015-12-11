<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 内容缩略图插件
 * 
 * @package TeThumbnail 
 * @author 绛木子
 * @version 1.0.0
 * @link http://lixianhua.com
 */
class TeThumbnail_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate(){
        //添加评分操作地址
        Helper::addAction('thumbnail', 'TeThumbnail_Action');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){
        Helper::removeAction('thumbnail');
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
		$type = new Typecho_Widget_Helper_Form_Element_Radio(
          'type', array('local' => '本地生成', 'qiniu' => '七牛'), 'local',
          '缩略图模式', '默认为本地生成缩略图');
        $form->addInput($type);
        /** 默认缩略图*/
        $default = new Typecho_Widget_Helper_Form_Element_Text('default', NULL, '', _t('默认缩略图'),_t('文章没有图片时显示的默认缩略图，为空时表示不显示'));
        $form->addInput($default);
        /** 默认大小 */
        $width = new Typecho_Widget_Helper_Form_Element_Text('width', NULL, '200', _t('缩略图默认宽度'));
        $form->addInput($width);
        /** 默认高度*/
        $height = new Typecho_Widget_Helper_Form_Element_Text('height', NULL, '140', _t('缩略图默认高度'));
        $form->addInput($height);
		/** 七牛CDN地址*/
        $qiniu = new Typecho_Widget_Helper_Form_Element_Text('qiniu', NULL, NULL, _t('七牛CDN地址'),_t('七牛CDN提供的缩略图功能，使用七牛模式必须填写'));
        $form->addInput($qiniu);
        
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 显示缩略图
     * 
     * @access public
	 * @param Widget_Archive $obj
	 * @param string $size 缩略图大小 200x140|full
	 * @param bool $link 是否返回链接
	 * @param string $pattern 输出的缩略图模版
     * @return void
     */
    public static function show($obj,$size=null,$link=false,$pattern='<div class="post-thumb"><a class="thumb" href="{permalink}" title="{title}" style="background-image:url({thumb})"></a></div>'){

		$options = Typecho_Widget::widget('Widget_Options');
		
		//插件是否启用
		if(!isset($options->plugins['activated']['TeThumbnail'])) return;
		
		$config = $options->plugin('TeThumbnail');
		$thumb = '';
		
		preg_match_all( "/<[img|IMG].*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/", $obj->content, $matches );

		if(isset($matches[1][0])){
			$thumb = $matches[1][0];
			
			if($config->type=='qiniu' && !empty($config->qiniu)){
				$thumb = str_ireplace($options->siteUrl.'usr/uploads/',$config->qiniu,$thumb);
				if($size!='full'){
					$thumb_width = $config->width;
					$thumb_height = $config->height;
			
					if($size!=null){
						$size = explode('x', $size);
						if(!empty($size[0]) && !empty($size[1])){
							list($thumb_width,$thumb_height) = $size;
						}
					}
					$thumb .= '?imageView2/1/w/'.$thumb_width.'/h/'.$thumb_height;
				}
			}else{
				$path = substr($thumb,strlen($options->siteUrl));
				
				if(is_file(__TYPECHO_ROOT_DIR__.'/'.$path))
					$thumb = Typecho_Common::url('action/thumbnail?url='.$path.'&size='.$size, $options->index);
			}
		}

		if(empty($thumb) && empty($config->default)){
			return '';
		}else{
			$thumb = empty($thumb) ? $config->default : $thumb;
		}
		if($link){
			return $thumb;
		}
		echo str_replace(
			array('{title}','{thumb}','{permalink}'),
			array($obj->title,$thumb,$obj->permalink),
			$pattern);
	}
}
