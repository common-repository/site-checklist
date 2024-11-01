<?php

if (!current_user_can('administrator')) {
    die();
}

if (isset($_POST['vocheck_settings_submit']) && check_admin_referer('vocheck_saving_settings')) {
    foreach ($_POST['vocheck'] as $key => $item) {
        $key = sanitize_text_field($key);
        if ($key == 'browserstack_site_page') {
            $item = esc_url_raw($item, array('http', 'https'));
        } else {
            $item = sanitize_text_field($item);
        }
        update_option($key, $item);
    }
}

$browserStackUsername = sanitize_text_field(get_option('browserstack_username'));
$browserStackAccessKey = sanitize_text_field(get_option('browserstack_access_key'));
$browserStackSitePage = esc_url(get_option('browserstack_site_page'));
?>

<div class="wrap vo-check-wrapper">
    <div class="intro-wrap">
        <div class="intro">
            <h2>Site Checklist</h2>
            <h3>Going live just became easier.</h3>
        </div>
    </div>
    <div class="panels">
        <ul class="inline-list">
			<span class="inline-list-links">
				<li class="current"><a href="#general">General Checks</a></li>
				<li><a href="#screenshots">Screenshots</a></li>
				<li><a href="#settings">Settings</a></li>
				<li><a href="#about">About</a></li>
			</span>
        </ul>
        <div id="general" class="panel visible clearfix">
            <div>
                <button class="button vocheck-button vocheck-checks vocheck-button-primary">Run Checks</button>
                <img class="vocheck-loading"
                     src="<?php echo plugins_url('../assets', dirname(__FILE__)) . '/images/cow-round.png'; ?>">

                <div class="vocheck-overview-count-container">Filter:
                <span class="vocheck-overview-count vocheck-overview-count--success"></span>
                <span class="vocheck-overview-count vocheck-overview-count--info"></span>
                <span class="vocheck-overview-count vocheck-overview-count--notice"></span>
                <span class="vocheck-overview-count vocheck-overview-count--failed"></span>
            </div>
            </div>

            <div class="panel-left">
                <div class="vocheck-container">
                    <ul class="vocheck-list"></ul>
                </div>
            </div>
            <div class="panel-right">
                <div class="vocheck-sidebar">

                </div>
            </div>
        </div>
        <div id="screenshots" class="panel clearfix">
            <div>
                <button class="button vocheck-button vocheck-screenshots vocheck-button-primary">Generate Screenshots
                </button>
                <img class="vocheck-loading"
                     src="<?php echo plugins_url('../assets', dirname(__FILE__)) . '/images/cow-round.png'; ?>">
            </div>

            <div class="vocheck-container">
                <div class="vocheck-screenshots-container">
                </div>

            </div>

        </div>
        <div id="settings" class="panel clearfix">

            <form method="post" action="">
                <h3>Browserstack</h3>
                <p>You can both get the username and access key from the following page: <a
                            href="https://www.browserstack.com/screenshots/api">https://www.browserstack.com/screenshots/api</a>.
                    Make sure you are logged in.</p>
                <label for="vocheck[browserstack_username]">Browserstack Username</label>
                <input id="vocheck[browserstack_username]" name="vocheck[browserstack_username]"
                       value="<?php echo $browserStackUsername; ?>" type="text">
                <label for="vocheck[browserstack_access_key]">Browserstack Access Key</label>
                <input id="vocheck[browserstack_access_key]" name="vocheck[browserstack_access_key]"
                       value="<?php echo $browserStackAccessKey; ?>" type="text">
                <label for="vocheck[browserstack_site_page]">Browserstack/Google PageSpeed url to check</label>
                <p class="description">Fill in the url you want to check with Browserstack and Google PageSpeed if this
                    is different than
                    the homepage (<?php echo site_url(); ?>).</p>
                <input id="vocheck[browserstack_site_page]" name="vocheck[browserstack_site_page]"
                       value="<?php echo $browserStackSitePage; ?>" type="url">

                <?php wp_nonce_field('vocheck_saving_settings'); ?>
                <input class="button vocheck-button vocheck-button-primary" name="vocheck_settings_submit"
                       id="vocheck_settings_submit" type="submit" value="Save Settings">
            </form>

        </div>
        <div id="about" class="panel clearfix">
            <h3>About the Author</h3>
            <p>This plugin is created and maintained by Marijn Bent at Van Ons.</p>
            <a href="http://marijnbent.nl" target="_blank">Marijn Bent</a><br>
            <a href="http://van-ons.nl" target="_blank">Van Ons</a>

            <h3>Add your own checks</h3>
            <p>Do you have a list of things you want to check on your own. You can easily download this plugin and add
                your own checks within 5 minutes. If you do, please send me a mail or tweet. I would love to hear about
                it :).</p>

            <a href="http://twitter.com/marijnbent" target="_blank">Marijn's Twitter</a><br>
            <a href="mailto:marijn@marijnbent.nl">Marijn's Email</a><br>

            <h3>Thanks</h3>
            <a href="http://danielpost.com/">Daniel Post</a> for various frontend ideas and implementations<br>
        </div>
    </div>
</div>

<div>

</div>




