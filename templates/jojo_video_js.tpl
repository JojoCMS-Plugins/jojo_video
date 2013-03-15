<script type="text/javascript" src="{$SITEURL}/external/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script src="{$SITEURL}/external/videojs/video.js" type="text/javascript" charset="utf-8"></script>
<link rel="stylesheet" href="{$SITEURL}/external/videojs/video-js.css" type="text/css" media="screen" title="Video JS" charset="utf-8">
<script type="text/javascript">
/*<![CDATA[*/
VideoJS.setupAllWhenReady();
$(document).ready(function(){ldelim}
  $("a.video_popup_trigger").fancybox({ldelim}
        'overlayOpacity': 0.95,
        'overlayColor': '#000',
        'autoDimensions': false,
        'width': {$video_width},
        'height': {$video_height},
        'onComplete': function(){ldelim}VideoJS.setupAllWhenReady();{rdelim}
  {rdelim});
{rdelim});
/*]]>*/
</script>
