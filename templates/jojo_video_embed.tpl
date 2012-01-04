<!-- Begin VideoJS -->
<div class="video-js-box">
  <!-- Using the Video for Everybody Embed Code http://camendesign.com/code/video_for_everybody -->
  <video class="video-js" width="{$video_width}" height="{$video_height}" controls preload poster="{$SITEURL}/images/{$video_width}x{$video_height}/videos/{$video.screenshot|default:'default.jpg'}">
    {if $video.mp4}<source src="{$SITEURL}/video/{$video.mp4}" type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"' />{/if}
    {if $video.ogv}<source src="{$SITEURL}/video/{$video.ogv}" type='video/ogg; codecs="theora, vorbis"' />{/if}
    {if $video.webm}<source src="{$SITEURL}/video/{$video.webm}" type='video/webm; codecs="vp8, vorbis"' />{/if}
    <!-- Flash Fallback. Use any flash video player here. Make sure to keep the vjs-flash-fallback class. -->
    <object class="vjs-flash-fallback" width="{$video_width}" height="{$video_height}" type="application/x-shockwave-flash" data="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf">
      <param name="movie" value="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf" />
      <param name="allowfullscreen" value="true" />
      <param name="flashvars" value='config={ldelim}"playlist":["{$SITEURL}/images/{$video_width}x{$video_height}/videos/{$video.screenshot|default:'default.jpg'}", {ldelim}"url": "{$SITEURL}/video/{$video.mp4}","autoPlay":false,"autoBuffering":true{rdelim}]{rdelim}' />
      <!-- Image Fallback. Typically the same as the poster image. -->
      <img src="{$SITEURL}/images/{$video_width}x{$video_height}/videos/{$video.screenshot|default:'default.jpg'}" width="{$video_width}" height="{$video_height}" alt="Poster Image" title="No video playback capabilities." />
    </object>
  </video>
  <!-- Download links provided for devices that can't play video in the browser. -->
  <p class="vjs-no-video"><strong>Download Video:</strong>
    {if $video.mp4}<a href="{$SITEURL}/video/{$video.mp4}">MP4</a>{/if}
    {if $video.ogv}<a href="{$SITEURL}/video/{$video.mp4}">Ogg</a>{/if}
    {if $video.webm}<a href="{$SITEURL}/video/{$video.mp4}">WebM</a>{/if}
    {*
    <!-- Support VideoJS by keeping this link. -->
    <a href="http://videojs.com">HTML5 Video Player</a> by VideoJS
    *}
  </p>
</div>
<!-- End VideoJS -->
