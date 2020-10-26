var query_request = function(url, endFunc)
{
	var crequest = new XMLHttpRequest();
	crequest.responseType = "text";
	crequest.open('GET', "/query.php?act=" + url, true);
	
	crequest.onload = function() 
	{
		endFunc(this.responseText, url);
	}
	
	crequest.send();
}

function query_onload()
{
	query_update_stats();
	
	setInterval(query_update_stats, 30000);
}

var qStats = [];

function query_update_stats()
{
	qStats["index"] = 0;
	
	query_request("memory",
		function(data, url)
		{
			qStats["memory"] = data;
			qStats["index"]++;
			
			print_stats();
		}
	);
	
	query_request("cpu",
		function(data, url)
		{
			qStats["cpu"] = data;
			qStats["index"]++;
			
			print_stats();
		}
	);
	
	query_request("cache",
		function(data, url)
		{
			qStats["cache"] = data;
			qStats["index"]++;
			
			print_stats();
		}
	);
}

function print_stats()
{
	if(qStats["index"] == 3)
	{
		let cache = parseFloat(qStats["cache"]);
		let mem =  parseFloat(qStats["memory"]);
		let cpu = parseFloat(qStats["cpu"]);
		
		$('#left_cache > div:nth-child(1)').html(cache + " MB");
		$('#left_memory > div:nth-child(1)').html(mem + " MB");
		$('#left_cpu > div:nth-child(1)').html(cpu + " ед.");
	}
}

function query_kill()
{
	query_request("kill",
		function(data, url)
		{
			show_alert("Запрос на уничтожение процесса сервера был отправлен!");
		}
	);
}

function query_stop()
{
	query_request("stop",
		function(data, url)
		{
			show_alert("Была запрошена запланированная остановка сервера!");
		}
	);
	
	setTimeout(query_kill, 8000);
}


function query_restart()
{
	var func = function()
	{
		query_request("start", 
			function(data, url)
			{
				show_alert("Выполняется загрузка пакета процессов сервера!", "success");
			}
		);
	};
	
	if($('#left_memory > div:nth-child(1)').html() != "0 MB") 
	{
		query_stop();
		setTimeout(func, 10000);
	}
	else func();
}

function query_srvlog(cnt = 5)
{
	clog("box_srvlog");
	
	query_request("srvlog;" + cnt,
		function(data, url)
		{
			$("#box_srvlog").append(html_log(data));
		}
	);
}

function query_com(command)
{
	query_request('"' + command + '"',
		function(data, url) { }
	);
}