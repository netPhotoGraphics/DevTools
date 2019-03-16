Installation instructions

Existing netPhotoGraphics sites:

Visit your admin/overview page. There will be a button to install the upgrade in the admin/Utility functions button section. Click the button and the upgrade will commence. If the button is not present follow the instructions below for a new installation.

New installations of netPhotoGraphics:

Unzip this archive.

Upload the extract.php.bin file into the root folder of your Gallery. I.e. If you want your Gallery to be (or if you update a Gallery) at the following address: http://mydomain.com/mygallery, then upload the file in the "mygallery" folder (Note: the upload must be done in "binary" mode or the file may be corrupted. The ".bin" suffix should cause your FTP client to use this mode.)

On your website rename extract.php.bin to extract.php

Using your browser, visit http://mydomain.com/mygallery/extract.php (if you install netPhotoGraphics at root level, then visit http://mydomain.com/extract.php).

The netPhotoGraphics files will self-extract and the setup process will start automatically.

Note: If you get a PHP Allowed memory size fatal error you will need to increase your PHP memory limit in the server PHP INI file. In the Resource Limits section change memory_limit to 128M or greater.
