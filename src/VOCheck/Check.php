<?php

namespace VOCheck;

class Check
{
    private $checks = [
        'get_pagespeed_insight_desktop',
        'get_pagespeed_insight_mobile',
        'is_database_prefix_wp',
        'has_admin_user',
        'uploads_is_writeable',
        'comments_are_off',
        //'has_generator_tag', //NOT WORKING YET
        'permalinks_are_on',
        'has_inactive_plugins',
        'has_inactive_themes',
        'has_standard_posts',
        'has_what_the_file_installed',
        'has_yoast_seo_installed',
        'check_if_wp_config_sample_exists',
        'check_if_unique_authentication_keys_salts_exists',
        'site_turned_public',
        'check_html_styling',
        'check_for_404_template',
        'check_for_print_css',
        'check_for_google_analytics',
        'check_for_minified_css',
        'check_admins_passwords',
        'xmlrpc_turned_off',
        'disallowed_file_edits',
        'wp_json_turned_off',
    ];

    /**
     * Check constructor.
     * Creates ajax actions for all the checks
     */
    public function __construct()
    {
        add_action('wp_ajax_' . 'return_checks', [$this, 'return_checks']);

        $checks = $this->get_checks();
        foreach ($checks as $check) {
            add_action('wp_ajax_' . $check, [$this, $check]);
        }

        add_action('wp_ajax_' . 'get_screenshots_of_website', [$this, 'get_screenshots_of_website']);

        //Easy to test new checks. var_dump the result and die()
//        (!is_admin()) ? add_action('init', [$this, 'check_for_google_analytics']) : "";
    }

    /**
     * How To Add New Checks
     * 1. Always expect the check failed. Let your checks proof the user did okay. If no $status is send to
     * return_json_object() the system will assign the value 'failed'.
     * 2. Fill in all the messages
     * 3. Add the function name to $this->checks. This will be used to create the necessary action and
     * ajax call in Javascript
     * 4. Great, you're done!
     *
     * Example function:
     */
    public static function is_this_true()
    {
        if (1 == 1) // == true
            $status = 's'; // = success

        $successMessage = "This is true";
        $failedMessage = "This isn't true";
        $fixMessage = "You cannot fix this.";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Retrieves Google Speed Insight using the current site url for desktop
     */
    public static function get_pagespeed_insight_desktop()
    {
        Check::process_pagespeed_result('desktop');
    }

    /**
     * Retrieves Google Speed Insight using the current site url for mobile
     */
    public static function get_pagespeed_insight_mobile()
    {
        Check::process_pagespeed_result('mobile');
    }

    /**
     * Processes the Google Page Speed request
     * @param $strategy
     */
    private static function process_pagespeed_result($strategy)
    {
        $sitePage = get_option('browserstack_site_page');
        $sitePage = ($sitePage !== '') ? $sitePage : get_site_url();

        $response = file_get_contents('https://www.googleapis.com/pagespeedonline/v2/runPagespeed?url=' . urlencode($sitePage) . "&strategy=" . $strategy);
        $response = json_decode($response);
        if ($response->responseCode == 200) {
            if ($response->ruleGroups->SPEED->score < 70) {
                $status = 'f';
            } else if ($response->ruleGroups->SPEED->score < 80) {
                $status = 'n';
            } else {
                $status = 's';
            }
            $successMessage = "<h3>" . ucfirst($strategy) . "</h3><h4>PageSpeed Insights</h4><span class='score'>" . $response->ruleGroups->SPEED->score . "</span>";
            $failedMessage = "<h3>" . ucfirst($strategy) . "</h3><h4>PageSpeed Insights</h4><span class='score'>" . $response->ruleGroups->SPEED->score . "</span>";
        } else if ($response->responseCode == 403) {
            $status = 'n';
            $failedMessage = "<h3>" . ucfirst($strategy) . "</h3><h4>cannot be fetched from the PageSpeed Insights because this server is not allowing outside IP addresses (" . $sitePage . ")</h4>";
        } else {
            $status = 'n';
            $failedMessage = "<h3>" . ucfirst($strategy) . "</h3><h4>cannot be fetched from the PageSpeed Insights (" . $sitePage . ")</h4>";
        }
        $fixMessage = "";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status, 'sidebar');
    }

    /**
     * Get screenshots of the website in various browsers
     */
    public static function get_screenshots_of_website()
    {
        $username = get_option('browserstack_username');
        $accessKey = get_option('browserstack_access_key');
        $sitePage = get_option('browserstack_site_page');

        $sitePage = ($sitePage !== '') ? $sitePage : get_site_url();

        if (empty($username) && empty($accessKey)) {
            $failedMessage = "Please fill in your Browserstack credentials";
        } else {
            $api = new BrowserStack($username, $accessKey);
            $output = $api->generateScreenshots($sitePage);
            $response = $output['response'];
            if ($output['status'] == 'success') {
                $html = '<div class="vocheck-list-item_screenshot-container">';
                foreach ($response as $screenshot) {
                    $html .= '<div class="vocheck-list-item_screenshot"><a target="_blank" href="' . $screenshot['img'] . '"><img alt="' . $screenshot['browser'] . ' ' . $screenshot['os'] . '" src="' . $screenshot['img'] . '"><span>' . $screenshot['device'] . ' ' . $screenshot['browser'] . ' ' . $screenshot['os'] . '</span></a></div>';
                }
                $html .= '</div>';
                $status = 's';
                $successMessage = "Please make sure the following screenshots are looking good. The website should work fine on all of these browsers" . $html;
            } else {
                $status = 'f';
                $failedMessage = "Screenshots haven't been generated";
            }
            $fixMessage = "Message from Browserstack: " . print_r($response, true);
        }
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status, $location = 'screenshots');
    }

    /**
     * Checks if database prefix isn't wp_
     */
    public static function is_database_prefix_wp()
    {
        global $wpdb;
        if ($wpdb->prefix != 'wp_')
            $status = 's';

        $successMessage = "wp_ is not the current database prefix";
        $failedMessage = "wp_ is your current database prefix";
        $fixMessage = "The easiest way to fix this issue is to install and run the following plugin: https://nl.wordpress.org/plugins/db-prefix-change/. Don't forget to delete the plugin afterwards";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Checks if the user 'admin' isn't arround anymore
     */
    public static function has_admin_user()
    {
        $user = get_user_by('login', 'admin');
        if (!$user)
            $status = 's';

        $successMessage = "You don't have a user called 'admin'";
        $failedMessage = "You still have a user called 'admin'";
        $fixMessage = "Create a new administrator user and delete 'admin'";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Checks if /uploads are writeable
     */
    public static function uploads_is_writeable()
    {
        if (is_writable(wp_upload_dir()['basedir']))
            $status = 's';

        $successMessage = "The upload folder is writeable";
        $failedMessage = "The upload folder isn't writeable";
        $fixMessage = "Please view the following documentation: <a href=\"https://codex.wordpress.org/Changing_File_Permissions\">https://codex.wordpress.org/Changing_File_Permissions</a>";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Comments and pings are turned off
     */
    public static function comments_are_off()
    {
        if (get_option('default_ping_status') == 'closed' && get_option('default_comment_status') == 'closed') {
            $status = 's';
        } else {
            $status = 'n';
        }

        $successMessage = "The ability to post comments or receive pings are turned off";
        $failedMessage = "The ability to post comments or receive pings are not turned off";
        $fixMessage = "Go to Settings -> Discussion and turn off the first three booleans";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * NOT WORKING YET
     * Checks if you have removed the generator tag
     */
    public static function has_generator_tag()
    {
        $generatorVersions = [
            the_generator('html'),
            the_generator('xhtml'),
            the_generator('atom'),
            the_generator('rss2'),
            the_generator('rdf'),
            the_generator('comment')
        ];

        $status = 's';
        foreach ($generatorVersions as $genVersion) {
            if ($genVersion != NULL) {
                $status = 'f';
            }
        }

        $successMessage = "The generator tag is removed from the html output as well from the rss feed";
        $failedMessage = "The generator tag isn't removed from the html output or from the rss feed";
        $fixMessage = "Please view the following code snippet: <a href=\"http://www.wpbeginner.com/wp-tutorials/the-right-way-to-remove-wordpress-version-number/\">http://www.wpbeginner.com/wp-tutorials/the-right-way-to-remove-wordpress-version-number/</a>";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Comments and pings are turned off
     */
    public static function permalinks_are_on()
    {
        if (get_option('permalink_structure') != '')
            $status = 's';

        $successMessage = "Permalinks are configured";
        $failedMessage = "Permalinks aren't configured";
        $fixMessage = "Go to Settings -> Permalinks and select the appropriate option";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Checks if there are plugins which are inactive
     */
    public static function has_inactive_plugins()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins();

        $status = 's';

        foreach ($plugins as $key => $plugin) {
            if (is_plugin_inactive($key))
                $status = 'f';
        }

        $successMessage = "No inactive plugins found";
        $failedMessage = 'Inactive plugins found';
        $fixMessage = 'Delete the plugins you don\'t use';
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Checks if there are themes which are inactive
     */
    public static function has_inactive_themes()
    {
        if (!function_exists('wp_get_themes')) {
            require_once ABSPATH . 'wp-admin/includes/theme.php';
        }

        $themes = wp_get_themes();
        $currentTheme = wp_get_theme();

        if (count($themes) == 1 || (count($themes) == 2 && $currentTheme->parent() != false))
            $status = 's';

        $successMessage = 'No inactive themes found';
        $failedMessage = 'Inactive themes found';
        $fixMessage = 'Delete the themes you don\'t use';
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Checks if the standard post and page are still on the site
     */
    public static function has_standard_posts()
    {
        $status = 's';
        $pageEn = get_page_by_path('sample-page');
        $pageNl = get_page_by_path('voorbeeld-pagina');
        if ($pageEn || $pageNl)
            $status = 'f';
        $post = get_post(1);
        if ($post) {
            if ($post->guid == 'hello-world' || $post->guid == 'hallo-wereld')
                $status = 'f';
        }

        $successMessage = "The standard page and post are removed or changed";
        $failedMessage = "The standard page and/or post still exist in their original form";
        $fixMessage = "Delete the page/post or edit them. Don't forget to change the slug";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * What the file is installed
     */
    public static function has_what_the_file_installed()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $whatTheFile = 'what-the-file/what-the-file.php';

        $plugins = get_plugins();

        $status = 'n';
        foreach ($plugins as $key => $plugin) {
            if ($key == $whatTheFile)
                if (is_plugin_active($key))
                    $status = 's';
        }

        $successMessage = "The plugin 'What the File' is activated";
        $failedMessage = "The plugin 'What the File' is't installed or activated";
        $fixMessage = "It's highly recommanded to activate and use 'What the File'. Please install it from the plugins repository";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     *Yoast SEO is installed
     */
    public static function has_yoast_seo_installed()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $yoastSEO = 'wordpress-seo/wp-seo.php';

        $plugins = get_plugins();

        foreach ($plugins as $key => $plugin) {
            if ($key == $yoastSEO)
                if (is_plugin_active($key))
                    $status = 's';
        }

        $successMessage = "The plugin 'Yoast SEO' is activated";
        $failedMessage = "The plugin 'Yoast SEO' is't installed or activated";
        $fixMessage = "Please install Yoast SEO from the plugin repository.";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Checks if the file wp-config-sample.php exists
     */
    public static function check_if_wp_config_sample_exists()
    {
        if (!file_exists(get_home_path() . 'wp-config-sample.php'))
            $status = 's';

        $successMessage = "The wp-config-sample.php file is removed";
        $failedMessage = "The wp-config-sample.php file isn't removed";
        $fixMessage = "Delete the file using FTP";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Checks if you have setup unique authentication keys and salts
     */
    public static function check_if_unique_authentication_keys_salts_exists()
    {
        if (defined('AUTH_KEY') && AUTH_KEY !== 'put your unique phrase here')
            $status = 's';

        $successMessage = "You have added unique authentication keys and salts";
        $failedMessage = "You haven't added unique authentication keys and salts";
        $fixMessage = "Generate your keys and paste these inside wp-config.php. <a href=\"https://api.wordpress.org/secret-key/1.1/salt/\">https://api.wordpress.org/secret-key/1.1/salt/</a>";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Checks if you search engines are allowed to index this site
     */
    public static function site_turned_public()
    {
        if (get_option('blog_public') == 1) {
            $status = 's';
        } else {
            $status = 'n';
        }

        $successMessage = "This site is public and searchable by search engines";
        $failedMessage = "This site is not searchable by search engines";
        $fixMessage = "Go to Settings -> Reading and deselect 'Discourage search engines from indexing this site'";
        $fixName = 'search_engine_visibility';
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Checks if you search engines are allowed to index this site
     */
    public static function check_html_styling()
    {
        if (get_option('vo_test_html_post_id') !== false) {
            $id = get_option('vo_test_html_post_id');
        } else {
            $id = 0;
        }

        $htmlContent = "<h1>Header 1</h1> <ul> <li>Test1</li> <li><em>Test2</em></li> <li><del>Test3</del></li> </ul> <p><a href='" . plugins_url('../assets', dirname(__FILE__)) . '/images/mario.png' . "'><img class=\"alignright size-medium\" src='" . plugins_url('../assets', dirname(__FILE__)) . '/images/mario.png' . "' width=\"191\" height=\"296\"></a></p> <h2>Header 2</h2> <ol> <li>You</li> <li><strong>Know</strong></li> <li>How</li> </ol> <hr> <h3>Header 3</h3> <blockquote><p>One day I will succeed</p></blockquote> <p><a href=\"https://sneakytime.com/rr/#.WF0ucbYrJTa\">Don’t click this link!</a></p> <p><img class=\"aligncenter size-medium\" src='" . plugins_url('../assets', dirname(__FILE__)) . '/images/pokemon.png' . "' width=\"260\" height=\"240\"></p> <h4>Header 4</h4> <h5>Header 5</h5> <p><img class=\"alignleft size-medium\" src='" . plugins_url('../assets', dirname(__FILE__)) . '/images/teletubbies.png' . "' width=\"140\" height=\"165\"></p> <h6>Header 6</h6>";

        $status = 'i';
        $args = [
            'ID' => $id,
            'post_title' => 'Checklist Test Post',
            'post_status' => 'Draft',
            'post_content' => $htmlContent,
        ];
        $post = wp_insert_post($args);

        if (is_int($post)) {
            update_option('vo_test_html_post_id', $post);
        } else {
            $status = 'f';
        }
        $post = get_post($post);
        $successMessage = "Please check the following page. Are all the html elements styled according to the design? <a href='" . $post->guid . "'>" . $post->guid . "</a>";
        $failedMessage = "Post could not be created. Please test the styling yourself.";
        $fixMessage = "";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Checks if you search engines are allowed to index this site
     */
    public static function check_for_404_template()
    {
        if (is_file(get_template_directory() . '/404.php')) {
            $status = 's';
        }

        $successMessage = "You have created a 404 page template";
        $failedMessage = "A custom 404.php page does not exist yet. Please create one.";
        $fixMessage = "";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Checks if you search engines are allowed to index this site
     */
    public static function check_for_print_css()
    {
        if (is_file(get_template_directory() . '/print.css')) {
            $status = 's';
        } else {
            $status = 'n';
        }

        $successMessage = "You have created a print.css";
        $failedMessage = "print.css does not exist yet. Check if this is required for the current project.";
        $fixMessage = "";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Checks if you search engines are allowed to index this site
     */
    public static function check_for_google_analytics()
    {
        $file = file_get_contents(get_site_url() . '/random404pageplease');
        $analyticsOnPage = strpos($file, '/analytics.js');

        if ($analyticsOnPage) {
            $status = 's';
        }

        $successMessage = "Google Analytics is active";
        $failedMessage = "We have not found the Google Analytics script";
        $fixMessage = "Install a plugin or insert the script manually";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Checks if you have a minified css file
     */
    public static function check_for_minified_css()
    {
        $located = locate_template('style.min.css');
        if ($located != '') {
            $status = 's';
        } else {
            $linecount = 0;
            $handle = fopen(get_template_directory() . '/style.css', 'r');
            while (!feof($handle)) {
                $line = fgets($handle, 4096);
                $linecount = $linecount + substr_count($line, PHP_EOL);
            }
            fclose($handle);

            if ($linecount < 50) {
                $status = 's';
            }
        }

        $successMessage = "We have found a minified css file";
        $failedMessage = "We cannot find a minified css file";
        $fixMessage = "Make sure you have generated and enqueued one.";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Check if administrators use one of the 1000 weakest passwords
     */
    public static function check_admins_passwords()
    {
        $args = array(
            'role__in' => array('Administrator'),
        );
        $users = get_users($args);
        $jsonPasswords = file_get_contents(plugin_dir_path(dirname(__FILE__)) . '../assets/simple-passwords.json');
        $jsonPasswords = json_decode($jsonPasswords);

        $successMessage = "Your admins are not using one of the weakest 1000 passwords.";
        $failedMessage = "The following admins have VERY weak passwords: ";
        $fixMessage = "Reset their passwords.";

        $status = 's';
        foreach ($users as $user) {
            foreach ($jsonPasswords as $password) {
                if (wp_check_password($password, $user->user_pass, $user->ID)) {
                    $status = 'f';
                    $failedMessage .= $user->user_login . ' (ID: ' . $user->ID . '), ';
                }

            }
        }

        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Checks if XMLRPC is turned off
     */
    public static function xmlrpc_turned_off()
    {
        if (has_filter('xmlrpc_enabled')) {
            $status = 's';
        }

        $successMessage = "You've turned off xml-rpc";
        $failedMessage = "XML-RPC is enabled. Turn this off if you don't use it.";
        $fixMessage = "add_filter('xmlrpc_enabled', '__return_false');";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Checks if it is disallowed to edit files using the WP editor
     */
    public static function disallowed_file_edits()
    {
        if (defined('DISALLOW_FILE_EDIT'))
            $status = 's';

        $successMessage = "You've turned off DISALLOW_FILE_EDIT";
        $failedMessage = "DISALLOW_FILE_EDIT is not defined yet.";
        $fixMessage = "define('DISALLOW_FILE_EDIT', true);";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Check if the JSON API is turned off
     */
    public static function wp_json_turned_off()
    {
        $json = file_get_contents(get_site_url() . '/wp-json');
        $json = json_decode($json);
        $status = 's';
        if (!empty($json->name))
            $status = 'n';

        $successMessage = "You've turned off the JSON API";
        $failedMessage = "The JSON API is enabled. Turn this off if you don't use it.";
        $fixMessage = "Install the plugin 'Disable REST API'";
        Check::return_json_object($successMessage, $failedMessage, $fixMessage, $status);
    }

    /**
     * Echoes json object for you
     * @param $successMessage string
     * @param $failedMessage string
     * @param $fixMessage string
     * @param $status 'success'| 's' | 'failed' | 'f' | 'n' | 'i'
     * @param $location 'list'|'sidebar'
     * @param $debug
     */
    public static function return_json_object($successMessage, $failedMessage, $fixMessage, $status = 'failed', $location = 'list', $debug = [])
    {
        if ($status == '') {
            $status = 'failed';
        } else if ($status == 's') {
            $status = 'success';
        } else if ($status == 'f') {
            $status = 'failed';
        } else if ($status == 'n') {
            $status = 'notice';
        } else if ($status == 'i') {
            $status = 'info';
        }

        $messages = [];
        $messages['successMessage'] = $successMessage;
        $messages['failedMessage'] = $failedMessage;
        $messages['fixMessage'] = $fixMessage;

        echo json_encode(['status' => $status, 'messages' => $messages, 'location' => $location, 'debug' => $debug]);
        die();
    }

    /**
     * @return array
     */
    public function get_checks()
    {
        return $this->checks;
    }

    /**
     * Return list with checks
     */
    public function return_checks()
    {
        $check = new Check();
        echo json_encode($check->get_checks());
        die();
    }
}