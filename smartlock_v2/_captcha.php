<?php

/*
 * Captcha generating and displaying file.
 * Default captcha is 300x50 pixels dimension.
 */

define('NOHEADER', TRUE);
define('ALLOW_GUEST', TRUE);
require_once('global.php');

require_once('includes/ob.image.php');

$cdata = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."captcha WHERE sid = '".$ME->data['sid']."'");
if ( !$cdata ) {
	$ctext = trim($_INPUT['ctext']);
	if ( !$ctext ) {
//		$ctext = generate_hash(6,true);
		$ctext = '      ';
	}
} else {
	$ctext = $cdata['captchastring'];
}

// 500x100 background pic.
$imgob = new ImageOB('lib/captchabk.png');
// Crop a random 300x50 dimension area to fit in the webpage
$imgob->crop(300, 50, rand(0,199), rand(0,49));

// Determin how big the font size should be.
$charw = floor(300/strlen($ctext));
$charsize = $charw > 50 ? 50 : $charw;
$charsize = floor($charsize*0.7);

// Insert the captcha text with different font, size, color, position for each character.
for ($i=0; $i<strlen($ctext); $i++) {
	$lsize = floor($charsize * rand(60,100) / 100);
	$letter = substr($ctext, $i, 1);
	$imgob->insert_text($letter, array(
		'size'		=> $lsize,
		'color'		=> 'rgba('.rand(50,255).','.rand(50,255).','.rand(50,255).',1)',
		'weight'	=> 100,
		'font'		=> 'lib/font.'.rand(0,2).'.ttf',
		'x'			=> ($charw*$i)+rand(0,$charw-$lsize),
		'y'			=> $lsize+rand(0,50-$lsize),
		'aa'		=> TRUE
	));
}

// Set the image format to jpeg and 50% compression to save bandwidth.
$imgob->set_format('jpeg', 50);

// Display image.
$imgob->show_pic();

?>