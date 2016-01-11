<head>
	<title><?php echo $title; ?></title>
	<meta charset="utf-8" />
	<meta name="viewport" 
		content="width=device-width initial-scale=1.0 user-scalable=no" />
	<meta name="keyword" content="<?php echo $keyword; ?>" />
<?php if(gettype($__js__) != "array") { $__js__ = array(); } ?>
<?php foreach($__js__ as $js) { ?>
		<script src="<?php echo $js; ?>"></script>
<?php } ?>
<?php if(gettype($__css__) != "array") { $__css__ = array(); } ?>
<?php foreach($__css__ as $css) { ?>
		<link rel="stylesheet" href="<?php echo $css; ?>" />
<?php } ?>
</head>
