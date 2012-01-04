<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2011 Harvey Kane <code@ragepank.com>

 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Harvey Kane <code@ragepank.com>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */



/* edit FAQ page */
$data = Jojo::selectQuery("SELECT * FROM {page} WHERE pg_url='admin/edit/video'");
if (!count($data)) {
    echo "Adding <b>Edit video</b> Page to menu<br />";
    Jojo::insertQuery("INSERT INTO {page} SET pg_title='Edit videos', pg_link='Jojo_Plugin_Admin_Edit', pg_url='admin/edit/video', pg_parent=?, pg_order=5, pg_mainnav='yes', pg_breadcrumbnav='yes', pg_sitemapnav='no', pg_xmlsitemapnav='no', pg_footernav='no', pg_index='no'", $_ADMIN_CONTENT_ID);
}
