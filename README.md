CommunityID
===========

PHP OpenID Server


2010-04-20 Reiner Jung <reiner@kb-m.com>

- To provide a simpler installation, all files will go under the web 
dir, and there's no longer need to create a symlink. Have 
this in mind when upgrading, replacing the symlink you currently have 
with the files from this release.

- Some of the new features need new configuration directives. To 
upgrade, use the older config.php file, and only after a successful 
upgrade you can take a look at config.default.php and fill out the new 
directives into the config.php file

NEW REQUIREMENTS:

- Minimal supported PHP version is 5.2.4
- For YubiKey support php-curl package is required
