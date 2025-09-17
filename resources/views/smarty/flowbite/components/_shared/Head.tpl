<head>
	<title>
		{{$data.page_title|default:"Applicación"}} | SIMA
	</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="shortcut icon" href="assets/img/brand_icons/brand_logo.ico" type="image/x-icon">
	<link rel="icon" type="image/png" sizes="192x192" href="assets/img/brand_icons/brand_logo_192.png">
	<link rel="apple-touch-icon" sizes="180x180" href="assets/img/brand_icons/brand_logo_180.ico">
	<link rel="icon" type="image/png" sizes="32x32" href="assets/img/brand_icons/brand_logo_32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="assets/img/brand_icons/brand_logo_16.png">
	<link rel="manifest" href="manifest.json">
	{{foreach from=$data.meta item=meta key=key}}
		<meta name="{{$meta.meta_name}}" content="{{$meta.meta_content}}">
	{{/foreach}}
	{{* 
	{{foreach from=$data.css item=css}}
	<link rel="stylesheet" href="{{$css}}">
	{{/foreach}}
	{{foreach from=$data.js item=js}}
	<script src="{{$js}}" defer></script>
	{{/foreach}} *}}
	<script type="module" src="http://localhost:5173/@vite/client"></script>
	<script type="module" src="http://localhost:5173/resources/js/global.js"></script>
	<script src="/{{$app.data.app_js}}" defer></script>
	<link href="/{{$app.data.app_css}}" />
</head>
