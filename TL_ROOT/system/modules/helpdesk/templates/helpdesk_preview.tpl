<?php
/**
 * TYPOlight Helpdesk :: Template for message preview window
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- 

	TYPOlight Forum/Helpdesk :: Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
	Visit http://www.typolight.org/extension-list/view/helpdesk.html for details.
	
-->
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $this->language; ?>">
<head>
<base href="<?php echo $this->base; ?>" />
<title><?php echo $this->title; ?> :: TYPOlight webCMS <?php echo VERSION; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->charset; ?>" />
<link rel="stylesheet" type="text/css" href="system/themes/<?php echo $this->theme; ?>/basic.css" media="screen" />
<link rel="stylesheet" type="text/css" href="system/modules/helpdesk/themes/<?php echo $this->theme; ?>/frontend.css" />
<!--[if lte IE 6]><style type="text/css">.pngfix{behavior:url("system/modules/helpdesk/themes/default/pngfix.htc")}</style><![endif]-->
</head>
<body>
<div id="container">
<div id="main" >
<div id="helpdesk-preview" class="helpdesk-messagelist">
<div class="helpdesk-message" style="padding:5px;">
<?php echo $this->content; ?>
</div>
</div>
</div>
</div>
</body>
</html>