=== Aspexi Easy Login URL ===
Author: Krzysztof Dryja (Aspexi)
Author URI: http://dryja.info
Contributors: cih997
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6FPXWH9786E3U
Tags: login, logout, htaccess, custom, url, wp-admin, admin, htaccess, permalinks, rewrite, register, forgot
Requires at least: 3.1
Tested up to: 3.4.2
Stable tag: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Aspexi Easy Login URL changes your url/wp-login.php URL into your custom string i.e. url/login and more (incl. Register and Forgot password links).

== Description ==

Aspexi Easy Login URL:

* changes your url/wp-login.php URL into your custom string i.e. url/login
* changes your url/wp-login.php?action=register URL into your custom string i.e. url/register
* changes your url/wp-login.php?action=lostpassword URL into your custom string i.e. url/forgot
* related URLs fix (i.e. on login page) >=3.3.2
* backups original .htaccess file, restore option included

* Not tested on multisite. Use at your own risk.
* Not tested on IIS7. Use at your own risk.
* Be careful using this plugin with other which modify .htaccess file 

== Installation ==

1. Upload `easyloginurl` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure plugin

== Frequently Asked Questions ==

== Screenshots ==

1. Plugin configuration

== Changelog ==

= 1.1.1 =
* Rewrite flushing fix IMPORTANT!

= 1.1 =
* SVN stable tag update

= 1.0.1 =
* Category / Tags listing bug fix

= 1.0 =
* Initial version