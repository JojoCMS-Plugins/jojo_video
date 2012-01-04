<?php
include('config.php');

$f = fopen(_SITEURL.'/external/jojo_video_cron.php', 'r');
fclose($f);