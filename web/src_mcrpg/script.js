$(function(){$('*[data-clipboard-text]').tooltip('disable');var clipboard=new ClipboardJS('*[data-clipboard-text]');clipboard.on('success',function(e){$(e.trigger).removeClass('copied');setTimeout(function(){$(e.trigger).addClass('copied').tooltip('enable').tooltip('show');setTimeout(function(){$(e.trigger).removeClass('copied');console.log(e.trigger,$(e.trigger).is(':hover'));$(e.trigger).tooltip('disable');},500);$(e.trigger).one('mouseleave blur',function(){$(this).tooltip('disable').tooltip('hide');});},1);e.clearSelection();});clipboard.on('error',function(e){prompt('Скопируйте текст:',e.text);});});$(window).on('load',function(){go_to_el(location.hash);$('*[data-scroll-to]').click(function(e){go_to_el($(this).data("scroll-to"));});$('header .menu>span>a:not(.link), footer .menu>span>a').click(function(e){go_to_el($(this).attr("href"));return false;});function go_to_el(q){if(!q)return false;var el=$(q.replace('#',''));if(el.length)$("html, body").stop().animate({scrollTop:el.offset().top+"px"});}});var player;function onYouTubeIframeAPIReady(){player=new YT.Player('playerYT',{videoId:'INQ9f20v334'});}
$(function(){$('.modalYT .close').click(function(){$('.modalYT').removeClass('show');player.stopVideo();});$('#playYT').click(function(){$('.modalYT').addClass('show');player.playVideo();});});
$(function(){
    setTimeout(function(){
        $('#shopModal').fadeIn("slow");
    }, 2000);
	$("#close-shop-modal").on("click", function() {
		$('#shopModal').fadeOut("slow");
	});
});