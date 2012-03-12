
<a class="video_popup_trigger" href="[#]video_popup_{$video_id}"><img src="{$SITEURL}/images/{$video_width}x{$video_height}/videos/{$video.screenshot|default:'default.jpg'}" width="{$video_width}" height="{$video_height}" alt="" title="" /></a>

<div style="display:block;width:0;height:0;overflow:hidden;">
    <div id="video_popup_{$video_id}" class="video_popup">
    {$embed}
    </div>
</div>