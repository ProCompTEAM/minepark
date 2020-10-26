var common_sound = function(soundName)
{
	var audio = new Audio();
	audio.src = "../sound/" + soundName + '.ogg';
	audio.load();
	audio.play();
}

function implode(glue, pieces) 
{
	return ( ( pieces instanceof Array ) ? pieces.join ( glue ) : pieces );
}

var requestN = new XMLHttpRequest();
requestN.responseType = "text";

var common_request = function(url, endFunc)
{
	requestN.open('GET', url + "?" + TIME, true);
	
	requestN.onload = function() 
	{
		endFunc(this.responseText, url);
	}
	
	requestN.send();
}

var load_module = function(name)
{
	$('#content').html('<center><img id="loading" src="img/l.gif"></center>');
	
	if(name[0] == '#') name = name.substr(1);
	
	common_request("modules/" + name + ".htm",
		function(data, url)
		{
			$('#content').html(data);
		}
	);
}

var show_alert = function(message, view = "info")
{
	html = '<div class="alert alert-success alert-' + view + '">';
	html += '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
	html += message + '</div>';
	
	$('.container-fluid > #alerts_cont').append(html);;
}

var clog = function(elId)
{
	noli = $("#" + elId + " > div:nth-child(1)");
	$("#" + elId).empty();
	$("#" + elId).html("<div>" + noli.html() + "</div>")
}

var html_log = function(data, seperator = "]: ")
{
	result = "";
	
	lines = data.split('\n');
	
	lines.forEach(function(line) 
	{	
		line.split('\r', '');
		
		blocks = line.split(seperator);
		
		val = blocks.pop();
		
		if(val.length < 2) return;
		
		result += "<div title=\"" + line.replace('"', "*") + "\">";
		
		begin = implode("]", blocks);
		
		result += "<span>" + begin + seperator + "</span>";
		result += "<span>" + val + "</span>";
		
		result += "</div>";
	});
	
	return result;
}




///auto///
const TIMEOUT_GUIDE_SECONDS = 30;

var loadAll = function()
{
	updateGuide();
	
	setInterval(updateGuide, TIMEOUT_GUIDE_SECONDS * 1000);
}

var num = 0;
var updateGuide = function()
{
	$("#home_right_guide").fadeOut(1000);
	
	if(num >= ($("#guides").children().length)) {  num = 0;  }
	
	$("#home_right_guide").html(
		$("#guides > article").eq(num).html()
	);
	
	num++;
	
	$("#home_right_guide").fadeIn(1000);
}