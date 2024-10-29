<?php
/*
  Plugin Name: Aspexi Easy Login URL
  Plugin URI: http://dryja.info
  Description: Aspexi Easy Login URL changes your url/wp-login.php URL into your custom string i.e. url/login and more.
  Version: 1.1.1
  Author: Krzysztof Dryja
  Author URI: http://dryja.info
  Copyright 2012 Krzysztof Dryja (email: krzysztof.dryja@onet.pl)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if (!class_exists("AELURL")) {

    class AELURL {

        // Constructor
        function AELURL() {
            
        }

        // Init stuff
        function init() {
            if ('true' == @$_REQUEST['settings-updated']) {
                global $wp_rewrite;
                $wp_rewrite->flush_rules();
            }
        }

        // Prepare settings variables
        function init_settings() {
            register_setting('aelurl_settings', 'aelurl_login_input', array('AELURL', 'parse_url'));
            register_setting('aelurl_settings', 'aelurl_login');
            register_setting('aelurl_settings', 'aelurl_register_input', array('AELURL', 'parse_url'));
            register_setting('aelurl_settings', 'aelurl_register');
            register_setting('aelurl_settings', 'aelurl_forgot_input', array('AELURL', 'parse_url'));
            register_setting('aelurl_settings', 'aelurl_forgot');
            register_setting('aelurl_settings', 'aelurl_htaccess_original');
        }

        // Execute
        function run() {
            if ('true' == @$_REQUEST['settings-updated']) {
                self::htaccess_reload();
            }

            self::fix_urls();
        }

        // Register admin menu
        function wp_menu() {
            if (current_user_can("administrator")) {
                add_submenu_page('options-general.php', 'Aspexi Easy Login URL', 'Easy Login URL', 'administrator', /* __FILE__ */ 'aelurl', array('AELURL', 'backend'));
            }
        }

        // Update rewrite rules
        function htaccess($rules) {
            // Backup original .htaccess
            if (!strlen(get_option('aelurl_htaccess_original'))) {
                update_option('aelurl_htaccess_original', $rules);
            }
            // TODO: change to query vars
            else if ('true' == @$_GET['reset'] && 'true' != @$_REQUEST['settings-updated']) {
                $rules = get_option('aelurl_htaccess_original');
                return $rules;
            }

            $new_rules = '';
            // Update rules
            $login = get_option('aelurl_login_input');
            if (get_option('aelurl_login') == 'on' && strlen($login)) {
                $new_rules .= "\n# ASPEXI Easy Login URL - LOGIN\n";
                $new_rules .= "RewriteEngine on\n";
                $new_rules .= "RewriteRule ^$login$ wp-login.php [NC,L]\n";
            }

            $register = get_option('aelurl_register_input');
            if (get_option('aelurl_register') == 'on' && strlen($register)) {
                $new_rules .= "\n# ASPEXI Easy Login URL - REGISTER\n";
                $new_rules .= "RewriteEngine on\n";
                $new_rules .= "RewriteRule ^$register$ wp-login.php?action=register [NC,L]\n";
            }

            $forgot = get_option('aelurl_forgot_input');
            if (get_option('aelurl_forgot') == 'on' && strlen($forgot)) {
                $new_rules .= "\n# ASPEXI Easy Login URL - FORGOT PASSWORD\n";
                $new_rules .= "RewriteEngine on\n";
                $new_rules .= "RewriteRule ^$forgot$ wp-login.php?action=lostpassword [NC,L]\n";
            }

            return $new_rules . $rules;
        }

        // Flush rules
        function htaccess_reload() {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }

        /*function rewrite_reload($wp_rewrite) {
            global $wp_rewrite;
            $wp_rewrite->mod_rewrite_rules();
            return $wp_rewrite;
        }*/

        /*function rewrite_reload_array($in) {
            return $in;
        }*/

        // Parse strings from inputs
        function parse_url($in) {
            $in = trim(self::urify($in));

            return $in;
        }

        // Parse strings from inputs - helper
        function urify($str) {
            return str_replace(" ", "-", trim(ereg_replace("[^A-Za-z0-9[:space:]-]", "", $str)));
        }

        // Fix urls
        function fix_urls() {

            // Login url
            $login = get_option('aelurl_login_input');
            if (get_option('aelurl_login') == 'on' && strlen($login)) {
                add_filter('login_url', 'fix_login');

                global $aelurl_login;
                $aelurl_login = $login;

                if (!function_exists('fix_login')) {

                    function fix_login($url) {
                        global $aelurl_login;
                        return str_replace(site_url('wp-login.php', 'login'), site_url($aelurl_login, 'login'), $url);
                    }

                }
            }

            // Register url
            $register = get_option('aelurl_register_input');
            if (get_option('aelurl_register') == 'on' && strlen($register)) {
                add_filter('register', 'fix_register');

                global $aelurl_register;
                $aelurl_register = $register;

                if (!function_exists('fix_register')) {

                    function fix_register($url) {
                        global $aelurl_register;
                        return str_replace(site_url('wp-login.php?action=register', 'login'), site_url($aelurl_register, 'login'), $url);
                    }

                }

                add_filter('site_url', 'fix_register2', 10, 3);

                if (!function_exists('fix_register2')) {

                    function fix_register2($url, $path, $orig_scheme) {
                        global $aelurl_register;
                        if ($orig_scheme !== 'login')
                            return $url;
                        if ($path == 'wp-login.php?action=register')
                            return site_url($aelurl_register, 'login');

                        return $url;
                    }

                }
            }

            // Forgot url
            $forgot = get_option('aelurl_forgot_input');
            if (get_option('aelurl_forgot') == 'on' && strlen($forgot)) {
                add_filter('lostpassword_url', 'fix_forgot');

                global $aelurl_forgot;
                $aelurl_forgot = $forgot;

                if (!function_exists('fix_forgot')) {

                    function fix_forgot($url) {
                        global $aelurl_forgot;
                        return str_replace('?action=lostpassword', '', str_replace(network_site_url('wp-login.php', 'login'), site_url($aelurl_forgot, 'login'), $url));
                    }

                }
            }
        }

        // Include common script
        function scripts() {
            wp_enqueue_script('common');
        }

        // Get current .htaccess file preview
        function htaccess_preview() {
            $hf = get_home_path() . '.htaccess';
            if (file_exists($hf)) {
                $handle = fopen($hf, "r");
                if ($handle) {
                    $contents = nl2br(fread($handle, filesize($hf)));
                    return $contents;
                } else
                    return 'Fatal error: cannot access .htaccess file!';
                fclose($handle);
            } else
                return 'Fatal error: cannot access .htaccess file!';
        }

        // Prepare reset url
        function reset_url() {
            $q = explode('&', $_SERVER['QUERY_STRING']);
            $purl = 'http' . ((!empty($_SERVER['HTTPS'])) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $q[0] . '&reset=true';
            return $purl;
        }

        // Check if user reset
        function reset_maybe() {
            // TODO: change to query vars
            if ('true' == @$_GET['reset'] && 'true' != @$_REQUEST['settings-updated']) {
                delete_option('aelurl_login_input');
                delete_option('aelurl_login');
                delete_option('aelurl_register_input');
                delete_option('aelurl_register');
                delete_option('aelurl_forgot_input');
                delete_option('aelurl_forgot');

                self::htaccess_reload();
                self::fix_urls();

                echo '<div class="updated">
                    <p><b>Aspexi Easy Login URL has been reset.</b> Original .htaccess file has been restored.</p>
                </div>';

                unset($_GET['reset']);
            }
        }

        // Output configuration
        function backend() {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            if (function_exists('is_multisite'))
                if (is_multisite()) {
                    echo '<div class="error">
                    <p><b>Aspexi Easy Login URL has not been tested on multisite.</b> Plugin might not work. Use at your own risk!</p>
                </div>';
                }

            if (!get_option('permalink_structure')) {
                echo '<div class="error">
                    <p><b>Aspexi Easy Login URL is not active.</b> You must <a href="' . admin_url('options-permalink.php') . '">enable Permalink</a> before it can work.</p>
                </div>';
            }


            self::reset_maybe(); {
                ?>
                <div class="wrap">
                    <div id="icon-link" class="icon32"></div><h2>Aspexi Easy Login URL Options</h2>

                    <div id="poststuff" class="metabox-holder has-right-sidebar">
                        <div id="post-body">
                            <div id="post-body-content">
                                <form method="post" action="options.php"> 
                                    <?php settings_fields('aelurl_settings'); ?>
                                    <input type="hidden" name="action" value="update" />
                                    <div class="stuffbox">
                                        <h3><span>Settings</span></h3>
                                        <div class="inside">
                                            <p>Login URL<br /><span style="font-size: 10px">I.e. use <?php echo site_url(); ?>/<b>login</b> instead of <?php echo site_url(); ?>/<b>wp-login.php</b></span></p>
                                            <input type="checkbox" <?php if (get_option('aelurl_login') == "on") { ?>checked <?php } ?>name="aelurl_login" /> Enable Login redirection
                                            <input type="text" name="aelurl_login_input" value="<?php echo get_option('aelurl_login_input', 'login'); ?>" />
                                            <p>Register URL<br /><span style="font-size: 10px">I.e. use <?php echo site_url(); ?>/<b>register</b> instead of <?php echo site_url(); ?>/<b>wp-login.php?action=register</b></span></p>
                                            <input type="checkbox" <?php if (get_option('aelurl_register') == "on") { ?>checked <?php } ?>name="aelurl_register" /> Enable Register redirection
                                            <input type="text" name="aelurl_register_input" value="<?php echo get_option('aelurl_register_input', 'register'); ?>" />
                                            <p>Frogot Password URL<br /><span style="font-size: 10px">I.e. use <?php echo site_url(); ?>/<b>forgot</b> instead of <?php echo site_url(); ?>/<b>wp-login.php?action=lostpassword</b></span></p>
                                            <input type="checkbox" <?php if (get_option('aelurl_forgot') == "on") { ?>checked <?php } ?>name="aelurl_forgot" /> Enable Forgot Password redirection
                                            <input type="text" name="aelurl_forgot_input" value="<?php echo get_option('aelurl_forgot_input', 'forgot'); ?>" />


                                        </div>
                                    </div>

                                    <p><input class="button-primary" type="submit" name="send" value="Save settings" id="submitbuttonn" /></p>

                                    <div class="stuffbox">
                                        <h3><span>Advanced</span></h3>
                                        <div class="inside">
                                            <p>Current .htaccess preview:</p>
                                            <span style="font-size: 10px;"><pre style=" background-color: #eee;"><?php echo self::htaccess_preview(); ?></pre></span>
                                            <p>Backed-up .htaccess preview:</p>
                                            <span style="font-size: 10px"><pre style=" background-color: #eee;"><?php echo nl2br(get_option('aelurl_htaccess_original')); ?></pre></span>
                                            <p><a href="<?php echo self::reset_url(); ?>">Restore original .htaccess file and reset configuration - one click!</a></p>
                                        </div>
                                    </div>

                                    <input class="button-primary" type="submit" name="send" value="Save settings" id="submitbutton" />
                                </form>

                            </div>
                        </div>
                        <?php
                    }
                    ?>

                    <div id="side-info-column" class="inner-sidebar">

                        <div class="postbox">
                            <h3><span>Made by</span></h3>	
                            <div class="inside">
                                <div style="width: 170px; margin: 0 auto;">
                                    <a href="http://dryja.info" target="_blank"><img src="<?php echo plugins_url() . '/easyloginurl/images/aspexi300.png'; ?>" alt="" border="0" width="150" /></a>
                                    <p>Krzysztof Dryja</p>
                                    <p><em><a href="http://dryja.info/" target="_blank">http://dryja.info</a></em></p>
                                    <p><em><a href="mailto:krzysztof.dryja@onet.pl" target="_blank">krzysztof.dryja@onet.pl</a></em></p>
                                </div>
                            </div>
                        </div>

                        <div class="postbox">
                            <h3><span>Donate</span></h3>	
                            <div class="inside">    
                                <p>If this plugin is useful for you, consider donate it or buy me a beer :) Thank you!</p>
                                <div style="width:100%; display: block; text-align:center;"><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                                        <input type="hidden" name="cmd" value="_s-xclick">
                                        <input type="hidden" name="hosted_button_id" value="6FPXWH9786E3U">
                                        <input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal — The safer, easier way to pay online.">
                                        <img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">
                                    </form>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>
                <div class="clear"></div>
            </div>
            <?php
        }

    }

}

// Missing?
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/misc.php');

// Create wp_rewrite object if not exists
global $wp_rewrite;
if (empty($wp_rewrite) || !($wp_rewrite instanceof WP_Rewrite)) {
    $wp_rewrite = new WP_Rewrite();
}

// Create main plugin class
if (class_exists("AELURL")) {
    $AELURL = new AELURL();
}

// Dance baby
if (isset($AELURL)) {
    add_action('init', array($AELURL, 'init'));
    add_action('admin_init', array($AELURL, 'init_settings'));
    add_filter('mod_rewrite_rules', array($AELURL, 'htaccess'));
    add_action('admin_menu', array($AELURL, 'wp_menu'));
    add_action('flush_event', array($AELURL, 'htaccess_reload'));
    //add_filter('generate_rewrite_rules', array($AELURL, 'rewrite_reload'));
    //add_filter('rewrite_rules_array', array($AELURL, 'rewrite_reload_array'));
    add_action('wp_enqueue_scripts', array($AELURL, 'scripts'));

    $AELURL->run();
}

// Why so serious?
?>