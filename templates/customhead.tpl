

<script type="text/javascript" src="external/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link rel="stylesheet" href="external/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />

<script src="external/videojs/video.js" type="text/javascript" charset="utf-8"></script>
<link rel="stylesheet" href="external/videojs/video-js.css" type="text/css" media="screen" title="Video JS" charset="utf-8">
{literal}
<script type="text/javascript">
VideoJS.setupAllWhenReady();
$(document).ready(function(){
  $("a.video_popup_trigger").fancybox({
        'overlayOpacity': 0.95,
        'overlayColor': '#000',
        'autoDimensions': false,
        'width': 912,
        'height': 456,
        'onComplete': function(){VideoJS.setupAllWhenReady();}
  });
});


</script>
{/literal}