<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="<?=ROOT?>/assets/css/bootstrap.min.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?=ucfirst(page())?> | <?=APP_NAME?></title>
</head>
<body>

	<?php do_action(plugin_id().'_main_menu',['links'=>$links])?>


