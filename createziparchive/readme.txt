Installation instructions

Unzip this archive.

Upload the extract.php.bin file into the root folder of your Gallery. I.e. If you want your Gallery to be (or if you update a Gallery) at the following address: http://mydomain.com/mygallery, then upload the file in the "mygallery" folder (Note: the upload must be done in "binary" mode or the file may be corrupted. The ".bin" suffix should cause your FTP client to use this mode.)

On your website rename extract.php.bin to extract.php

If this is an upgrade to an existing installation you should be sure that you are logged in to your site before taking the next step. 

Using your browser, visit http://mydomain.com/mygallery/extract.php (if you install ZenPhoto20 at root level, then visit http://mydomain.com/extract.php).

The ZenPhoto20 files will self-extract and the setup process will start automatically.

Note: If you get a PHP Allowed memory size fatal error you will need to increase your PHP memory limit in the server PHP INI file. In the Resource Limits section change memory_limit to 128M.
