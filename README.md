# thumber
timthumb-ish in nature... light, portable

This project attempts to create folders and a thumb based on url received.

## Requirements
- PHP
- Apache server if using other server type, you should set your rewrite rules. 
Here's a search for .htaccess converters for NGINX: https://www.google.com.ng/search?q=htaccess+to+nginx

## usage
- Copy  `_thumber.php` to serving directory.
- If using Apache, copy `.htaccess` to serving directory.
- To override defaults, `_thumber.config-sample.php` to serving directory, rename it to 
`_thumber.config.php` and make changes.
- Request files and see them auto generated in the proper dimensions. Modes are discussed below.

### zoomcropped mode `{width}/{height}`
e.g. receiving `83/93/test.jpg`
should create a folder named `83` and a subfolder in it named `93`
then a 83by93 zoom-cropped version of an image `test.jpg` which is in the configured
home folder
`0/93` will mean scaled to have height 0 no white padding
and `98/0` will mean scaled to have width 98 no white padding

### padded mode `{width}/{height}/pad`
e.g. receiving `83/93/pad/test.jpg`
should create a folder named `83` and a subfolder in it named `93`
then a 83by93 padded version of an image `test.jpg` which is in the configured
home folder
`0/93` will mean scaled to have height 0 no white padding
and `98/0` will mean scaled to have width 98 no white padding
