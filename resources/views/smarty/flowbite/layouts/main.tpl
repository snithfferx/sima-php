<!DOCTYPE html>
<html lang="en">
{{if $data.layout.head}}
{{include file=_VIEW_|cat:$theme|cat:$data.layout.head.template data=$data.layout.head.data app=$data.layout.app}}
{{else}}

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Document</title>
		<link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.css" rel="stylesheet" />
		<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
	</head>
{{/if}}

<body class="h-full scrollbar scrollbar-w-1 scrollbar-thumb-rounded-[0.25rem] 
		scrollbar-track-pink-200 
		scrollbar-thumb-purple-400 
		dark:scrollbar-track-purple-900 
		dark:scrollbar-thumb-purple-700 
		text-primary dark:text-white">
	{{if $data.layout.navbar}}
		{{include file=_VIEW_|cat:$theme|cat:$data.layout.navbar.template data=$data.layout.navbar.data}}
	{{else}}
		<header class="bg-white text-gray-900 dark:text-white dark:bg-gray-800 shadow">
			<div class="container mx-auto px-4 py-6">
				<h1 class="text-3xl font-bold">Mi Aplicación con Flowbite</h1>
			</div>
		</header>
	{{/if}}
	<main class="container mx-auto px-4 py-6">
		{{block name="content"}}{{/block}}
	</main>
	{{if $data.layout.footer}}
		{{include file=_VIEW_|cat:$theme|cat:$data.layout.footer.template data=$data.layout.footer.data}}
	{{else}}
<footer class="bg-white text-gray-900 dark:text-white dark:bg-gray-800 shadow mt-auto">
			<div class="container mx-auto px-4 py-6 text-center">
				&copy; 2024 Mi Aplicación. Todos los derechos reservados.
			</div>
		</footer>
	{{/if}}

</html>
<scripts></scripts>
