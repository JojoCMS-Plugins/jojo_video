<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2012 Jojo CMS <info@jojocms.org>
  *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Harvey Kane <code@ragepank.com>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 * @package jojo_core
 */

class Jojo_Field_videoembedcode extends Jojo_Field
{
    function displayedit()
    {
         $videoid = $this->table->getRecordID();
         if (!empty($videoid)) {
             global $smarty;
             $smarty->assign('videoid', $videoid);
             
             return $smarty->fetch('admin/fields/videoembedcode.tpl');
             //return '[[video: '.$this->table->getRecordID().']]';
         } else {
             return '';
         }
        
        return '';

    }
}