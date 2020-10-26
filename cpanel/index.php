<?php
	$currentTime = time();
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Панель управления сервером</title>
		<meta charset="utf-8">
		
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<link rel="stylesheet" href="css/main.css?<?php echo $currentTime; ?>">
		
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		
		<script type="text/javascript">
			const TIME = <?php echo $currentTime; ?>;
		</script>
		
		<script src="script/main.js?<?php echo $currentTime; ?>"></script>
		<script src="script/query.js?<?php echo $currentTime; ?>"></script>
	</head>
	<body>
	
	<div class="container-fluid">
		<div id="alerts_cont"></div>
		
		<div class="row" id="nav">
			<div class="col-sm-4 hidden-xs hidden-sm" id="nav_label">
				<strong>cakehost.ru</strong> - Панель управления гибким MCPE сервером
			</div>
			<div class="col-sm-8" id="nav_bar">
				<div id="nav_exit">Выход</div>
				<div id="nav_user" data-toggle="tooltip" data-placement="bottom" title="Ваш ID в системе">u19132</div>
				<div id="nav_coin" data-toggle="tooltip" data-placement="bottom" title="Текущий баланс">-1</div>
			</div>
		</div>
		
		<div class="row" id="top">
		  <div class="col-sm-2" id="top_logo"><a id="top_logo_img" href="http://cakehost.ru/"></a></div>
		  <div class="col-sm-10 justify-content-center" id="top_menu">
			<a class="top_menu_item" style="background-image: url(img/menu/admin.png); display: none;" href="#admin">Мой Админ</a>
			<a class="top_menu_item" style="background-image: url(img/menu/support.png)" href="#help">Поддержка</a>
			<a class="top_menu_item" style="background-image: url(img/menu/community.png)" href="#community">Сообщество</a>
			<a class="top_menu_item" style="background-image: url(img/menu/stats.png)" href="#stats">Система</a>
			<a class="top_menu_item" style="background-image: url(img/menu/plugins.png)" href="#plugins">Плагины</a>
			<a class="top_menu_item" style="background-image: url(img/menu/control.png)" href="#control">Параметры</a>
			<a class="top_menu_item" style="background-image: url(img/menu/profile.png)" href="http://cakehost.ru/user">Аккаунт</a>
		  </div>
		</div>
		
		<div class="row" id="main">
		  <div class="col-sm-2" id="home_left">
			<span class="left_label">Нагрузка на общее CPU</span>
			<div id="left_cpu" class="left_block"
				data-toggle="tooltip" data-placement="right" title="Единицы вычислительной мощности">
				<div>?? ед.</div>
				<div>??c / час</div>
			</div>
		  
			<span class="left_label">Потребление ОЗУ</span>
			<div id="left_memory" class="left_block"
				data-toggle="tooltip" data-placement="right" title="Количество потребляемой оперативной памяти">
				<div>?? MB</div>
				<div>??c / час</div>
			</div>
			
			<span class="left_label">Объем на диске</span>
			<div id="left_cache" class="left_block"
				data-toggle="tooltip" data-placement="right" title="Использование сервером пространства SSD диска">
				<div>?? MB</div>
				<div>??c / час</div>
			</div>
			<span id="left_sum">Итого: ???c / час</span>
		  </div>
		  <div class="col-sm-8" id="content"><!-- MODULE DATA --></div>
		  <div class="col-sm-2" id="home_right">
			<div id="guides"><?php echo file_get_contents("guides.xml"); ?></div>
			<span class="right_label">Полезная информация</span>
			<div id="home_right_guide"><!-- AUTO DATA --></div>
		  </div>
		</div>
	
	</div>
	
	<div id="powerpanel">
		<div class="powerbtn_cont" data-toggle="modal" data-target="#power_modal_kill">
			<div class="powerbtn" id="power_btn_kill"
				data-toggle="tooltip" data-placement="top" title="Жесткое отключение"></div>
		</div>
		<div class="powerbtn_cont" data-toggle="modal" data-target="#power_modal_stop">
			<div class="powerbtn" id="power_btn_stop"
				data-toggle="tooltip" data-placement="top" title="Отключить как /stop"></div>
		</div>
		<div class="powerbtn_cont" data-toggle="modal" data-target="#power_modal_rest">
			<div class="powerbtn" id="power_btn_rest"
				data-toggle="tooltip" data-placement="top" title="Перезагрузка или запуск"></div>
		</div>
	</div>
	
	
	<!-- Modal Dialogs-->
	<div class="modal fade" id="power_modal_kill" role="dialog">
		<div class="modal-dialog modal-lg">
		  <div class="modal-content">
			<div class="modal-header">
			  <button type="button" class="close" data-dismiss="modal">×</button>
			  <h4 class="modal-title">Жесткая остановка</h4>
			</div>
			<div class="modal-body">
				<p>Данная функция необходима для того, чтобы в случаях зависания сервера 
				или срочной необходиомости его отключения - остановить сервер.</p>
				<p>Процесс сервера будет уничтожен в принудительном порядке.</p>
				<p>Некоторые данные могут не сохраниться!</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
				<button type="button" class="btn btn-danger" data-dismiss="modal" onclick="query_kill()">Убить процесс</button>
			</div>
		  </div>
		</div>
	</div>
	
	<div class="modal fade" id="power_modal_stop" role="dialog">
		<div class="modal-dialog modal-lg">
		  <div class="modal-content">
			<div class="modal-header">
			  <button type="button" class="close" data-dismiss="modal">×</button>
			  <h4 class="modal-title">Отключение сервера</h4>
			</div>
			<div class="modal-body">
				<p>Используйте эту функцию, если сервер необходимо плавно остановить.</p>
				<p>Серверу будет отправлена команда /stop, затем процесс будет уничтожен.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
				<button type="button" class="btn btn-danger" data-dismiss="modal"  onclick="query_stop()">Остановить сервер</button>
			</div>
		  </div>
		</div>
	</div>
	
	<div class="modal fade" id="power_modal_rest" role="dialog">
		<div class="modal-dialog modal-lg">
		  <div class="modal-content">
			<div class="modal-header">
			  <button type="button" class="close" data-dismiss="modal">×</button>
			  <h4 class="modal-title">Перезагрузка или восстановление</h4>
			</div>
			<div class="modal-body">
				<p>В случаи, если сервер уже был запущен, он будет плавно остановлен.</p>
				<p>Затем процесс сервера будет уничтожен.</p>
				<p>Далее будет отправлена команда на возобновление работы сервера.</p>
				<p>Полный цикл перезагрузки может иметь период в более чем 10 секунд!</p> 
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
				<button type="button" class="btn btn-success" data-dismiss="modal" onclick="query_restart()">Перезагрузить сервер</button>
			</div>
		  </div>
		</div>
	</div>
	
	<script>
	$(document).ready(
		function()
		{ 
			loadAll();
			
			query_onload();
			
			$('[data-toggle="tooltip"]').tooltip(); 
			
			if($(location).attr('hash') != "") load_module($(location).attr('hash'));
		});

		$(".top_menu_item").click(function(){ load_module($(this).attr('href')); });
	</script>
	</body>
</html>
		
