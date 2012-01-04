README

This plugin works best with FFMPEG + ffmpeg2theora installed on the server.

Installation...

1. Recommended: Install ffmpeg and ffmpeg2theora (http://v2v.cc/~j/ffmpeg2theora/). Sorry, I can't help you with this.

2. Add the following code to your .htaccess file

AddType video/ogg  .ogv
AddType video/mp4  .mp4
AddType video/webm .webm

3. Create a "video" folder in your public_html folder and set write permissions for the web server user. eg.
chmod -R 755 /path/to/public_html/video/

4. Set the max_file_upload setting in php.ini to as high as you need

5. Make sure your hosting account has plenty of disk space available. FFMPEG will make 3 extra copies of each video, so a 10Mb video may need 40mb plus.