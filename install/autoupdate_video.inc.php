<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2008 Jojo CMS
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Michael Cochrane <mikec@jojocms.org>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

$default_td['video'] = array(
        'td_name' => "video",
        'td_primarykey' => "videoid",
        'td_displayfield' => "name",
        'td_orderbyfields' => "displayorder, name",
        'td_deleteoption' => "yes",
        'td_menutype' => "list",
        'td_plugin' => 'Jojo_video',
    );

$o = -1;

// ID Field
$default_fd['video']['videoid'] = array(
        'fd_name' => "ID",
        'fd_type' => "hidden",
        'fd_readonly' => "0",
        'fd_help' => "A unique ID, automatically assigned by the system",
        'fd_order' => $o++,
    );


// name Field
$default_fd['video']['name'] = array(
        'fd_name' => "Name",
        'fd_type' => "text",
        'fd_required' => "yes",
        'fd_readonly' => "0",
        'fd_size' => "50",
        'fd_help' => "",
        'fd_order' => $o++,
    );
    
// caption Field
$default_fd['video']['caption'] = array(
        'fd_name' => "Caption",
        'fd_type' => "text",
        'fd_required' => "no",
        'fd_readonly' => "0",
        'fd_size' => "50",
        'fd_help' => "",
        'fd_order' => $o++,
    );
    
// source Field
$default_fd['video']['source'] = array(
        'fd_name' => "Source",
        'fd_type' => "fileupload",
        'fd_required' => "no",
        'fd_readonly' => "0",
        'fd_size' => "50",
        'fd_help' => "",
        'fd_order' => $o++,
    );
    
// mp4 Field
$default_fd['video']['mp4'] = array(
        'fd_name' => "MP4 Video",
        'fd_type' => "readonly",
        'fd_required' => "no",
        'fd_readonly' => "0",
        'fd_size' => "50",
        'fd_help' => "",
        'fd_order' => $o++,
    );

// ogv Field
$default_fd['video']['ogv'] = array(
        'fd_name' => "OGV Video",
        'fd_type' => "readonly",
        'fd_required' => "no",
        'fd_readonly' => "0",
        'fd_size' => "50",
        'fd_help' => "",
        'fd_order' => $o++,
    );
    
// webm Field
$default_fd['video']['webm'] = array(
        'fd_name' => "WEBM Video",
        'fd_type' => "readonly",
        'fd_required' => "no",
        'fd_readonly' => "0",
        'fd_size' => "50",
        'fd_help' => "",
        'fd_order' => $o++,
    );
    
// screenshot Field
$default_fd['video']['screenshot'] = array(
        'fd_name' => "Screenshot",
        'fd_type' => "fileupload",
        'fd_required' => "no",
        'fd_readonly' => "0",
        'fd_size' => "50",
        'fd_help' => "",
        'fd_order' => $o++,
    );
    
// displayorder Field
$default_fd['video']['displayorder'] = array(
        'fd_name' => "Display Order",
        'fd_type' => "order",
        'fd_required' => "no",
        'fd_readonly' => "0",
        'fd_size' => "50",
        'fd_help' => "",
        'fd_order' => $o++,
    );

