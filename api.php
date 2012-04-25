<?php

Jojo::addHook('admin_action_after_save_video', 'admin_action_after_save_video', 'jojo_video');
//Jojo::addHook('admin_action_after_save', 'admin_action_after_save_video', 'jojo_video');

 Jojo::addFilter('content', 'content', 'jojo_video');
 
$_provides['fieldTypes'] = array(
        'videoembedcode'         => 'Video embed code'
        );
        
$_options[] = array(
    'id'          => 'video_conversion',
    'category'    => 'Video',
    'label'       => 'Video conversion',
    'description' => 'The system to use for converting video formats (only select ffmpeg if this is available on your server)',
    'type'        => 'radio',
    'default'     => 'manual',
    'options'     => 'manual,ffmpeg',
    'plugin'      => 'jojo_video'
);