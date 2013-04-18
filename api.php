<?php

Jojo::addHook('admin_action_after_save_video', 'admin_action_after_save_video', 'jojo_video');
Jojo::addHook('foot', 'js', 'jojo_video');

Jojo::addFilter('output', 'content', 'jojo_video');

$_provides['fieldTypes'] = array(
        'videoembedcode'         => 'Video embed code'
        );

$_options[] = array(
    'id'          => 'videoembed_width',
    'category'    => 'Video',
    'label'       => 'video Width',
    'description' => 'Pixel width for the video',
    'type'        => 'text',
    'options'     => '',
    'default'     => Jojo::getOption('youtube_width', 425),
    'plugin'      => 'jojo_video'
);

$_options[] = array(
    'id'          => 'videoembed_height',
    'category'    => 'Video',
    'label'       => 'video Height',
    'description' => 'Pixel height for the video',
    'type'        => 'text',
    'options'     => '',
    'default'     => Jojo::getOption('youtube_height', 350),
    'plugin'      => 'jojo_video'
);

$_options[] = array(
    'id'          => 'videopopup_width',
    'category'    => 'Video',
    'label'       => 'video popup Width',
    'description' => 'Pixel width for the video',
    'type'        => 'text',
    'options'     => '',
    'default'     => 720,
    'plugin'      => 'jojo_video'
);

$_options[] = array(
    'id'          => 'videopopup_height',
    'category'    => 'Video',
    'label'       => 'video popup Height',
    'description' => 'Pixel height for the video',
    'type'        => 'text',
    'options'     => '',
    'default'     => 400,
    'plugin'      => 'jojo_video'
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

$_options[] = array(
    'id'          => 'video_autoplay',
    'category'    => 'Video',
    'label'       => 'Video Autoplay',
    'description' => 'Play videos automatically when the page load completes.',
    'type'        => 'radio',
    'default'     => 'no',
    'options'     => 'yes,no',
    'plugin'      => 'jojo_video'
);

$_options[] = array(
    'id'          => 'video_controls',
    'category'    => 'Video',
    'label'       => 'Video Controls',
    'description' => 'Display controls (play/pause etc).',
    'type'        => 'radio',
    'default'     => 'yes',
    'options'     => 'yes,no',
    'plugin'      => 'jojo_video'
);

$_options[] = array(
    'id'          => 'video_preload',
    'category'    => 'Video',
    'label'       => 'Video Preload',
    'description' => 'Preload the video for faster play start (will slow page load on pages with many videos)',
    'type'        => 'radio',
    'default'     => 'yes',
    'options'     => 'yes,no',
    'plugin'      => 'jojo_video'
);
