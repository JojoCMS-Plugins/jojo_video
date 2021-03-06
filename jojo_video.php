<?php

class Jojo_Plugin_Jojo_video extends Jojo_Plugin
{
    public static function admin_action_after_save_video($id)
    {
        if (!$id) return false;
        $video = Jojo::selectRow("SELECT * FROM {video} WHERE videoid=?", $id);
        if (empty($video['videoid'])) return false;

        /* handle conversions from single source */
        if (!empty($video['source'])) {
            //$cached = array('mp4' => false, 'ogv' => false, 'webm' => false);
            $cached = array('mp4' => false, 'ogv' => false);
            $source_md5 = md5_file(_DOWNLOADDIR.'/videos/'.$video['source']);
            //echo $source_md5;
            $base_name = self::removeExtension($video['source']);

            if (Jojo::fileExists(self::cacheDir().'/'.$base_name.'.md5')) {
                $cache_md5 = file_get_contents(self::cacheDir().'/'.$base_name.'.md5');
            } else {
                $cache_md5 = false;
            }

            /* copy source to cache folder if it's a FLV, and copy MP4 files across directly */
            if ((Jojo::getFileExtension($video['source']) == 'flv') || (Jojo::getFileExtension($video['source']) == 'mp4')) {
                copy(_DOWNLOADDIR.'/videos/'.$video['source'], self::cacheDir().'/'.$video['source']);
                if (Jojo::getFileExtension($video['source']) == 'mp4') Jojo::updateQuery("UPDATE {video} SET mp4=? WHERE videoid=?", array($video['source'], $video['videoid']));
            }

            foreach ($cached as $ext => &$filename) {
                if (Jojo::fileExists(self::cacheDir().'/'.$base_name.'.'.$ext) && ($cache_md5 == $source_md5)) continue;
                $temp = Jojo::selectRow("SELECT * FROM {videoqueue} WHERE source=? AND format=?");
                if (!empty($temp['videoqueueid'])) continue; //don't add a video to the conversion queue twice
                if (Jojo::getOption('video_conversion', 'manual') == 'ffmpeg') {
                    Jojo::insertQuery("INSERT INTO {videoqueue} SET source=?, format=?, started=0", array(_DOWNLOADDIR.'/videos/'.$video['source'], $ext));
                }
            }
            file_put_contents(self::cacheDir().'/'.$base_name.'.md5', $source_md5);

            if (empty($video['screenshot'])) {
                $screenshot = self::screenshot(_DOWNLOADDIR.'/videos/'.$video['source']);
                echo "\n".'ss='. $screenshot,"\n\n";
                if (!empty($screenshot)) {
                    copy($screenshot, _DOWNLOADDIR.'/videos/'.$base_name.'.jpg');
                    unlink($screenshot);
                    if (Jojo::fileExists(_DOWNLOADDIR.'/videos/'.$base_name.'.jpg')) {
                        $video['screenshot'] = $base_name.'.jpg';
                        Jojo::updateQuery("UPDATE {video} SET screenshot=? WHERE videoid=?", array($base_name.'.jpg', $video['videoid']));
                    }
                }
            }
        }

        /* handle manually uploaded videos */
        $formats = array('mp4', 'ogv', 'webm');
        foreach ($formats as $format) {
            if (!empty($video[$format.'_upload']) && Jojo::fileExists(_DOWNLOADDIR.'/videos/'.$video[$format.'_upload'])) {
                if (strtolower(Jojo::getFileExtension($video[$format.'_upload'])) != $format) {
                    //TODO: raise error about being the wrong file extension
                    unlink(_DOWNLOADDIR.'/videos/'.$video[$format.'_upload']); //delete the uploaded file
                    Jojo::updateQuery("UPDATE {video} SET ".$format."_upload='' WHERE videoid=?", array($video['videoid'])); //clear the database field
                    continue;
                }

                //move the vid to cache
                rename(_DOWNLOADDIR.'/videos/'.$video[$format.'_upload'], self::cacheDir().'/'.$video[$format.'_upload']);

                //clear the file upload field
                Jojo::updateQuery("UPDATE {video} SET ".$format."_upload='', ".$format."=? WHERE videoid=?", array($video[$format.'_upload'], $video['videoid']));

                //remove the video from the conversion queue
                if (!empty($video['source'])) Jojo::deleteQuery("DELETE FROM {videoqueue} WHERE source=? AND format=?", array($video['source'], $format));
            }
        }

        //copy(_DOWNLOADDIR.'/videos/'.$video['screenshot'], self::cacheDir().'/'.$video['screenshot']);

        return true;
    }

    public static function getPopupHtml($videoid, $width=false, $height=false) {
        global $smarty;
        $embed = self::getEmbedHtml($videoid, Jojo::getOption('videopopup_width', 720), Jojo::getOption('videopopup_height', 400));
        $smarty->assign('embed', $embed);
        $smarty->assign('video_id', mt_rand(1000,9999));
        $smarty->assign('video_width', $width);
        $smarty->assign('video_height', $height);
        $html = $smarty->fetch('jojo_video_popup.tpl');
        return $html;
    }

    public static function getPopupButtonHtml($videoid, $video_popup_button='images/video_popup_button.gif') {
        global $smarty;
        $embed = self::getEmbedHtml($videoid, Jojo::getOption('videopopup_width', 720), Jojo::getOption('videopopup_height', 400));
        $smarty->assign('embed', $embed);
        $smarty->assign('video_id', mt_rand(1000,9999));
        $smarty->assign('video_width', $width);
        $smarty->assign('video_height', $height);
        $smarty->assign('video_popup_button', $video_popup_button);
        $html = $smarty->fetch('jojo_video_popup_button.tpl');
        return $html;
    }

    public static function getEmbedHtml($videoid, $width=false, $height=false) {
        global $smarty;
        $video = self::getEmbedData($videoid);
        if (empty($video['videoid'])) return '';//can't find anything by that ID

        $smarty->assign('video_width', $width);
        $smarty->assign('video_height', $height);
        $smarty->assign('video_controls', (boolean)(Jojo::getOption('video_controls', 'yes')=='yes'));
        $smarty->assign('video_autoplay', (boolean)(Jojo::getOption('video_autoplay', 'no')!='no'));
        $smarty->assign('video_preload', (boolean)(Jojo::getOption('video_preload', 'yes')=='yes'));
        $smarty->assign('video', $video);
        $html = $smarty->fetch('jojo_video_embed.tpl');
        return $html;
    }

    public static function getEmbedData($videoid) {
        $video = Jojo::selectRow("SELECT * FROM {video} WHERE videoid=? or name=?", array(trim($videoid),trim($videoid)));
        if (empty($video['videoid'])) return false;

        /* display the FLV for flash fallback, if source is a FLV */
        if (!empty($video['source']) && Jojo::getFileExtension($video['source']) == 'flv') {
            $video['flv'] = $video['source'];
        } else {
            $video['flv'] = '';
        }

        return $video;
    }

    public static function screenshot($input, $seconds=10) {
        //ffmpeg -y -i /path/to/video.avi -f mjpeg -1 -ss 10 -vframes 1 -s 120x90 -an /path/to/picture.jpg

        $temp = _CACHEDIR.'/'.md5(mt_rand(1, 100000)).'.jpg';

        $command = self::ffmpegPath().' -y -i '.$input.' -f mjpeg -ss '.$seconds.' -vframes 1 -an '.$temp;
        self::runExternal($command, $code);
        //echo $command;
        return $temp; //returns the name of the temporary file created
    }

    public static function cron()
    {
        if (Jojo::getOption('video_conversion', 'manual') == 'ffmpeg') {
            Jojo::updateQuery("UPDATE {videoqueue} SET started=0 WHERE started < ?", strtotime('-60 minute')); //any videos that haven't completed in 60 mins get requeued
            $queue = Jojo::selectRow("SELECT * FROM {videoqueue} WHERE started=0 LIMIT 1");
            if (empty($queue['source'])) return false;

            //begin processing
            Jojo::updateQuery("UPDATE {videoqueue} SET started=? WHERE videoqueueid=?", array(time(), $queue['videoqueueid']));
            $res = self::convert(_DOWNLOADDIR.'/videos/'.$queue['source'], self::cacheDir().'/'.self::removeExtension(basename($queue['source'])).'.'.$queue['format']);
            if ($res) {
                Jojo::deleteQuery("DELETE FROM {videoqueue} WHERE source=? AND format=?", array($queue['source'], $queue['format']));
                Jojo::updateQuery("UPDATE {video} SET ".$queue['format']."=? WHERE source=?", array(self::removeExtension(basename($queue['source'])).'.'.$queue['format'], basename($queue['source'])));
                //echo "UPDATE {video} SET ".$queue['format']."='".self::removeExtension(basename($queue['source'])).'.'.$queue['format']."' WHERE source='".$queue['source']."'";
            }
        }
        return true;
    }

    private static function test() {
        echo self::screenshot(_DOWNLOADDIR.'/videos/024_babymiracle.mp4');
    }

    //$video is a record from the database, not a filename
    public static function makeCache($video) {

    }

    //returns the filename without the extension
    public static function removeExtension($filename) {
        $temp = explode('.', $filename);
        if (count($temp) <= 1) return $filename;
        $ext = array_pop($temp);
        return implode('.', $temp);
    }

    public static function convert($input, $output) {
        $ext = Jojo::getFileExtension($output);

        //output via a temp file so that if the process is interrupted, there aren't incomplete files sitting in the live folder
        if (!file_exists(self::cacheDir().'/temp/')) mkdir(self::cacheDir().'/temp/'); //ensure the temp folder exists
        $temp   = self::cacheDir().'/temp/'.md5(mt_rand(1, 100000)).'.'.$ext;

        /* get size / quality info from input video */
        $w = self::getMeta($input, 'width');
        $h = self::getMeta($input, 'height');
        $ab = self::getMeta($input, 'ab');
        $ar = self::getMeta($input, 'ar');

        //echo 'w='.$w;
        //exit;


        $s = $w.'x'.$h;//'320x180';
            $b = '280k';
            $r = 15;
            $a = '16:9';

        //runExternal( $ffmpeg_path .' -i '.$input.' -s '.$s.' -b '.$b.' -r '.$r.' -aspect '.$a.' '.$temp, &$code );
        //$command = $ffmpeg_path . " -i " . $input . " -ar " . $ar . " -ab " . $ab . " -f flv -s " . $w . "x" . $h . " " . $temp;
        if ($ext == 'ogv') {
            $command = self::ffmpeg2theoraPath() . " " . $input ." -o " . $temp;
        } else {
            //$command = self::ffmpegPath() . " -i " . $input . " -ar 44100 -f ".$ext." -s " . $w . "x" . $h . " " . $temp;
            $command = self::ffmpegPath() . " -i " . $input . " -acodec copy -f ".$ext." -s " . $w . "x" . $h . " " . $temp;
            //$command = self::ffmpegPath() . " -i " . $input . " -acodec copy -f ".$ext." " . $temp;

        }
        self::runExternal($command, $code);
        if ($code) {
            //echo "Error - resizing ".$input.' to '.$s.' size.<br />'."\n";
            echo $command;
            return false;
        } else {
            rename($temp, $output);
            return true;
            //echo "Success - Resized ".$input.' to '.$s.' size.<br />'."\n";
        }

    }

    public static function cacheDir() {
        return _WEBDIR.'/video';
    }

    //helper function for resizing
    public static function makeMultipleTwo ($value) {
        $sType = gettype($value/2);
        if($sType == "integer") {
            return $value;
        } else {
            return ($value-1);
        }
    }


    public static function getMeta($filename, $type) {
        static $vstats;
        if (!isset($vstats[$filename])) {
            $command = self::ffmpegPath() . ' -i ' . $filename . ' -vstats 2>&1';
            $vstats[$filename] = shell_exec ( $command );
        }
        //echo $vstats[$filename];
        if (empty($vstats[$filename])) return false;

        /* width */
        if ($type == 'width') {
            if (preg_match('/Stream.*Video.*([\\d]{3,4})x[\\d]{3,4}/', $vstats[$filename], $matches)) {
            	return $matches[1];
            } else {
            	return false;
            }
        }

        /* height */
        if ($type == 'height') {
            if (preg_match('/Stream.*Video.*[\\d]{3,4}x([\\d]{3,4})/', $vstats[$filename], $matches)) {
            	return $matches[1];
            } else {
            	return false;
            }
        }

        /* Audio sample rate */
        if ($type == 'ar') {
            if (preg_match('/Stream.*Audio.* ([\\d]*) Hz/', $vstats[$filename], $matches)) {
            	return $matches[1];
            } else {
            	return false;
            }
        }

        /* Audio bit rate */
        if ($type == 'ab') {
            if (preg_match('%Stream.*Audio.* (\\d*) kb/s%', $vstats[$filename], $matches)) {
            	return $matches[1].'000';
            } else {
            	return false;
            }
        }

        return false;
    }


    public static function ffmpegPath()
    {
        return '/usr/bin/ffmpeg';
    }

    public static function ffmpeg2theoraPath()
    {
        return '/usr/local/bin/ffmpeg2theora';
    }

    /*function run_in_background($Command, $Priority = 0)
   {
       if($Priority)
           $PID = shell_exec("nohup nice -n $Priority $Command 2> /dev/null & echo $!");
       else
           $PID = shell_exec("nohup $Command 2> /dev/null & echo $!");
       return($PID);
   }
   */


    public static function runExternal( $cmd, $code) {

        $descriptorspec = array(
           0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
           1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
           2 => array("pipe", "w") // stderr is a file to write to
        );

        $pipes= array();
        $process = proc_open($cmd, $descriptorspec, $pipes);

        $output= "";

        if (!is_resource($process)) return false;

        #close child's input imidiately
        fclose($pipes[0]);

        stream_set_blocking($pipes[1],false);
        stream_set_blocking($pipes[2],false);

        $todo= array($pipes[1],$pipes[2]);

        while( true ) {
           $read= array();
           if( !feof($pipes[1]) ) $read[]= $pipes[1];
           if( !feof($pipes[2]) ) $read[]= $pipes[2];

           if (!$read) break;

           $ready= stream_select($read, $write=NULL, $ex= NULL, 2);

           if ($ready === false) {
               break; #should never happen - something died
           }

           foreach ($read as $r) {
               $s= fread($r,1024);
               $output.= $s;
           }
        }

        fclose($pipes[1]);
        fclose($pipes[2]);

        $code= proc_close($process);

        return $output;
    }

    public static function content($content)
    {
        global $smarty;

        /* Find all [[video: ID]] tags */
        preg_match_all('/\[\[video: ?([^\]]*) ?\]\]/', $content, $matches);
        foreach($matches[1] as $id => $videoid) {
            $embed = self::getEmbedHtml($videoid, Jojo::getOption('videoembed_width', 290), Jojo::getOption('videoembed_height', 160));
            if (!$embed) continue;
            $embed = '<div class="video_embed_wrap">'.$embed.'</div>';
            $content = str_replace($matches[0][$id], $embed, $content);
        }

        /* Find all [[video popup: ID]] tags */
        preg_match_all('/\[\[video ?popup: ?([^\]]*) ?\]\]/', $content, $matches);
        foreach($matches[1] as $id => $videoid) {
            $embed = self::getPopupHtml($videoid, Jojo::getOption('videoembed_width', 290), Jojo::getOption('videoembed_height', 160));
            if (!$embed) continue;
            $embed = '<div class="video_embed_popup_wrap">'.$embed.'</div>';
            $content = str_replace($matches[0][$id], $embed, $content);
        }
        return $content;
    }

    public static function js() {
        global $smarty;
        $smarty->assign('video_width', Jojo::getOption('videopopup_width', 720));
        $smarty->assign('video_height', Jojo::getOption('videopopup_height', 400));
        $js = $smarty->fetch('jojo_video_js.tpl');
        return $js;
    }
}
