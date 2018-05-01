<?php

# by JiangCat
# 基于iMagick扩展的图片物件模型

class ImageOB
{
	var $HANDLER = FALSE;
	private $DEFAULT_FORMAT = 'jpg';
	
	// 用一个文件实例化该物件
	function __construct($infile='') {
		global $SYS;

		if ( $infile && !file_exists($infile)  )
			return FALSE;

		if ( $infile ) {
			$SYS->debuginfo['fileread']++;
			$this->HANDLER = new Imagick($infile);
		}
		else {
			$this->HANDLER = new Imagick();
		}
		return TRUE;
	}

	// 析构时释放各种指针
	function __destruct() {
		if ( !$this->HANDLER )
			return;
		$this->HANDLER->clear();
		$this->HANDLER->destroy();
	}

	// 直接用二进制BLOB加载物件
	function load_from_blob($blob) {
		$this->HANDLER = new Imagick();
		$this->HANDLER->readImageBlob($blob);
	}

	// 将图片转换为指定色彩深度
	function limit_color($lv) {
		return $this->HANDLER->posterizeImage($lv, FALSE);
	}

	// 剪切图片
	function crop($w, $h, $sx=0, $sy=0) {
		if ( !$this->HANDLER )
			return FALSE;
		return $this->HANDLER->cropImage($w, $h, $sx, $sy);
	}

	// 插入文字
	function insert_text($txt, $opts=array()) {
		if ( !$this->HANDLER )
			return FALSE;

		$PC = new Imagick();
		$PC->newImage($this->HANDLER->getImageWidth(), $this->HANDLER->getImageHeight(), "transparent", "png");
		
		$defaultopts = array(
			'size'		=> 12,
			'color'		=> '#000000',
			'weight'	=> 100,
			'font'		=> './includes/fonts/tahoma.ttf',
			'x'			=> 0,
			'y'			=> 0,
			'aa'		=> FALSE,
			'rotate'	=> 0,
		);
		if ( !file_exists($opts['font']) )
			unset($opts['font']);
		$opts = array_merge($defaultopts, $opts);

		$P = new ImagickDraw();
		$P->setFont($opts['font']);
		$P->setFontSize($opts['size']);
		$P->setFillColor($opts['color']);
		$P->setTextAntialias($opts['aa']);
		$P->setFontWeight($opts['weight']);
		if ( $opts['rotate'] ) {
			$P->rotate($opts['rotate']);
		}
		$P->annotation($opts['x'], $opts['y'], $txt);

		$PC->drawImage($P);
		$this->HANDLER->compositeImage($PC, imagick::COMPOSITE_OVER, 0, 0);

		return TRUE;
	}

	// 在现有图层物件上覆盖一个来自文件的图层
	function image_overlay($f, $x=0, $y=0, $op=1.0) {
		global $SYS;

		if ( !$this->HANDLER || !file_exists($f) )
			return FALSE;

		$SYS->debuginfo['fileread']++;

		$topimg = new Imagick($f);
		if ( $op != 1.0 )
			$topimg->setImageOpacity($op);

		$this->HANDLER->compositeImage($topimg, imagick::COMPOSITE_OVER, $x, $y);

		$topimg->clear();
		$topimg->destroy();

		return TRUE;
	}

	function add_watermark($f, $posy='center', $posx='center', $opacity=1.0) {
		if ( !$this->HANDLER || !$f || !file_exists($f) )
			return;

		$topimg = new Imagick($f);

		if ( $opacity != 1.0 )
			$topimg->setImageOpacity($opacity);

		if ( $topimg->getImageWidth() > $this->HANDLER->getImageWidth()/2 )
			$topimg->thumbnailImage(floor($this->HANDLER->getImageWidth()/2), 0, false);

		switch ( $posy ) {
			case 'top'		:	$posy = 0;
								break;
			case 'bottom'	:	$posy = $this->HANDLER->getImageHeight() - $topimg->getImageHeight();
								break;
			default			:	$posy = ceil(($this->HANDLER->getImageHeight() - $topimg->getImageHeight()) / 2);
								break;
		}
		switch ( $posx ) {
			case 'left'		:	$posx = 0;
								break;
			case 'right'	:	$posx = $this->HANDLER->getImageWidth() - $topimg->getImageWidth();
								break;
			default			:	$posx = ceil(($this->HANDLER->getImageWidth() - $topimg->getImageWidth()) / 2);
								break;
		}

		$this->HANDLER->compositeImage($topimg, imagick::COMPOSITE_OVER, $posx, $posy);

		$topimg->clear();
		$topimg->destroy();
	}

	// 保存图片到文件
	function save($fname) {
		global $SYS;

		if ( !$this->HANDLER )
			return FALSE;

@		$fp = fopen($fname, 'wb');
		if ( !$fp )
			return FALSE;

		if ( @!fwrite($fp, $this->get_blob()) )
			return FALSE;
		
		fclose($fp);

		$SYS->debuginfo['filewrite']++;

		return TRUE;
	}

	// 直接输出文件
	function show_pic() {
		if ( !$this->HANDLER )
			return FALSE;

		switch ( $this->DEFAULT_FORMAT ) {
			case 'jpg'		:
			case 'jpeg'		:	$mime = 'jpeg';
								break;
			case 'png'		:
			case 'png8'		:
			case 'png24'	:
			case 'png32'	:	$mime = 'png';
								break;
			default			:	$mime = $this->DEFAULT_FORMAT;
								break;
		}

		header("Content-Type: image/".$mime);
		echo $this->get_blob();
	}

	// 按比例或非比例压缩图片尺寸
	function createThumbnail($w=0, $h=0, $pro=TRUE) {
		return $this->HANDLER->thumbnailImage($w, $h, $pro);
	}

	// 用array(w,h)返回图片当前尺寸
	function getDimension() {
		return array(
			$this->HANDLER->getImageWidth(),
			$this->HANDLER->getImageHeight(),
		);
	}

	// 获取图片的二进制码
	function get_blob() {
		if ( !$this->HANDLER )
			return FALSE;
		return $this->HANDLER->getImageBlob();
	}

	// 设置图片的格式
	function set_format($fmt, $qua=90) {
		if ( !$this->HANDLER )
			return FALSE;

		if ( $fmt == 'jpg' )
			$fmt = 'jpeg';

		$this->DEFAULT_FORMAT = $fmt;
		$this->HANDLER->setImageFormat($fmt);

		if ( $fmt == 'jpeg' && $qua ) {
			if ( $qua < 1 )
				$qua = 1;
			if ( $qua > 100 )
				$qua = 100;

			$this->HANDLER->setCompression(imagick::COMPRESSION_JPEG);
			$this->HANDLER->setCompressionQuality($qua);
		}

		return TRUE;
	}
}

?>