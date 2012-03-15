<?php

Jojo::addHook('admin_action_after_save_video', 'admin_action_after_save_video', 'jojo_video');
//Jojo::addHook('admin_action_after_save', 'admin_action_after_save_video', 'jojo_video');

 Jojo::addFilter('content', 'content', 'jojo_video');
 
$_provides['fieldTypes'] = array(
        'videoembedcode'         => 'Video embed code'
        );