hMailServer External accounts Plugin for Roundcube
==================================================
Plugin that adds a possibility to change user external accounts using two
methods (drivers) via Settings/External accounts tab.

Requirements
------------
- hMailServer with webserver and PHP >= 5.3.0
- Requires PHP COM (Windows only)

How to install
--------------
- Copy folder to *roundcube/plugin/hms_externalaccounts*
- Config *config.inc.php.dist* and save as *config.inc.php*
- Enable plugin in *roundcube/config/config.inc.php*

Drivers
-------
hMailServer External accounts plugin supports two change mechanisms which are handled
by included drivers. Just pass driver name in 'hms_externalaccounts_driver' option.

**hMailServer (hmail)**

Requires PHP COM (Windows only). For access to hMail server on remote host you'll
need to define 'hms_externalaccounts_remote_dcom' and 'hms_externalaccounts_remote_server'.
See config.inc.php.dist file for more info.

**hMailServer Remote (hmail_remote)**

Requires PHP COM (Windows only) on remote host.
See config.inc.php.dist file for more info.


Changelog
---------
Version 1.1 (2017-03-05)
- Rearrange code
- Fixed some typos

Version 1.0 (2017-03-03)
- First release

License
-------
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see http://www.gnu.org/licenses/.