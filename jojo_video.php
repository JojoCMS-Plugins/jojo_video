<?php

class Jojo_Plugin_Jojo_video extends Jojo_Plugin
{   
    function admin_action_after_save_video($id)
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
            foreach ($cached as $ext => &$filename) {
                if (Jojo::fileExists(self::cacheDir().'/'.$base_name.'.'.$ext) && ($cache_md5 == $source_md5)) continue;
                $temp = Jojo::selectRow("SELECT * FROM {videoqueue} WHERE source=? AND format=?");
                if (!empty($temp['videoqueueid'])) continue; //don't add a video to the conversion queue twice 
                Jojo::insertQuery("INSERT INTO {videoqueue} SET source=?, format=?, started=0", array(_DOWNLOADDIR.'/videos/'.$video['source'], $ext));
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
    
    function getPopupHtml($videoid, $width=false, $height=false) {
        global $smarty;
        //$embed = self::getEmbedHtml($videoid, $width, $height);
        //$embed = self::getEmbedHtml($videoid, 608, 304);
        $embed = self::getEmbedHtml($videoid, 912, 456); 
        $smarty->assign('embed', $embed);
        $smarty->assign('video_id', mt_rand(1000,9999));
        $smarty->assign('video_width', $width);
        $smarty->assign('video_height', $height);
        $html = $smarty->fetch('jojo_video_popup.tpl');
        return $html;
    }
    
    function getEmbedHtml($videoid, $width=false, $height=false) {
        global $smarty;
        $video = Jojo::selectRow("SELECT * FROM {video} WHERE videoid=?", $videoid);
        if (empty($video['videoid'])) return '';//can't find anything by that ID
        $smarty->assign('video_width', $width);
        $smarty->assign('video_height', $height);
        $smarty->assign('video', $video);
        $html = $smarty->fetch('jojo_video_embed.tpl');
        return $html;
    }
    
    function getEmbedData($videoid) {
        $video = Jojo::selectRow("SELECT * FROM {video} WHERE videoid=?", $videoid);
        if (empty($video['videoid'])) return false;
        
        return $video;
    }
    
    function screenshot($input, $seconds=10) {
        //ffmpeg -y -i /path/to/video.avi -f mjpeg -1 -ss 10 -vframes 1 -s 120x90 -an /path/to/picture.jpg
        
        $temp = _CACHEDIR.'/'.md5(mt_rand(1, 100000)).'.jpg';
        
        $command = self::ffmpegPath().' -y -i '.$input.' -f mjpeg -ss '.$seconds.' -vframes 1 -an '.$temp;
        self::runExternal( $command, &$code );
        //echo $command;
        return $temp; //returns the name of the temporary file created
    }
    
    function cron()
    {
        Jojo::updateQuery("UPDATE {videoqueue} SET started=0 WHERE started < ?", strtotime('-60 minute')); //any videos that haven't completed in 60 mins get requeued
        $queue = Jojo::selectRow("SELECT * FROM {videoqueue} WHERE started=0 LIMIT 1");
        if (empty($queue['source'])) return false;
        
        //begin processing
        Jojo::updateQuery("UPDATE {videoqueue} SET started=? WHERE videoqueueid=?", array(time(), $queue['videoqueueid']));
        $res = self::convert($queue['source'], self::cacheDir().'/'.self::removeExtension(basename($queue['source'])).'.'.$queue['format']);
        if ($res) {
            Jojo::deleteQuery("DELETE FROM {videoqueue} WHERE source=? AND format=?", array($queue['source'], $queue['format']));
            Jojo::updateQuery("UPDATE {video} SET ".$queue['format']."=? WHERE source=?", array(self::removeExtension(basename($queue['source'])).'.'.$queue['format'], basename($queue['source'])));
            //echo "UPDATE {video} SET ".$queue['format']."='".self::removeExtension(basename($queue['source'])).'.'.$queue['format']."' WHERE source='".$queue['source']."'";
        }
        return true;
    }
    
    function test() {
        echo self::screenshot(_DOWNLOADDIR.'/videos/024_babymiracle.mp4');
    }
    
    //$video is a record from the database, not a filename
    function makeCache($video) {
        
    }
    
    //returns the filename without the extension
    function removeExtension($filename) {
        $temp = explode('.', $filename);
        if (count($temp) <= 1) return $filename;
        $ext = array_pop($temp);
        return implode('.', $temp); 
    }
    
    function convert($input, $output) {
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
            
        }
        self::runExternal( $command, &$code );
        if ($code) {
            //echo "Error - resizing ".$input.' to '.$s.' size.<br />'."\n";
            return false;
        } else {
            rename($temp, $output);
            return true;
            //echo "Success - Resized ".$input.' to '.$s.' size.<br />'."\n";
        }
        
    }
    
    function cacheDir() {
        return _WEBDIR.'/video';
    }
    
    //helper function for resizing
    function makeMultipleTwo ($value) {
        $sType = gettype($value/2);
        if($sType == "integer") {
            return $value;
        } else {
            return ($value-1);
        }
    }
    
    
    function getMeta($filename, $type) {
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
    
   
    function ffmpegPath()
    {
        return '/usr/bin/ffmpeg';
    }
    
    function ffmpeg2theoraPath()
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

    
    function runExternal( $cmd, &$code ) {

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
    

}