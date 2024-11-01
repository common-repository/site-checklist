<?php

namespace VOCheck;

class Plugin {

    protected $pluginPath;
    protected $pluginUrl;

    public function __construct($path)
    {
        $this->pluginPath = $path;
        $this->pluginUrl = WP_PLUGIN_URL . '/site-checklist';

        new Check;

        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_menu', array($this, 'add_menu_and_page'));
        add_action('vocheck_after_saving_settings', array($this, 'save_plugin_settings'));
    }

    public function enqueue_assets()
    {
        wp_enqueue_style('vo-style', plugins_url( '../assets', dirname(__FILE__)) . '/css/admin.css', []); //, time()
        wp_register_script('vo-script', plugins_url( '../assets', dirname(__FILE__)) . '/js/main.js', ['jquery'], false);
        $params = [
            'ajaxurl' => admin_url( 'admin-ajax.php'),
        ];
        wp_localize_script('vo-script', 'params', $params );
        wp_enqueue_script('vo-script');
    }
    public function add_menu_and_page()
    {
        add_management_page(
            'Site Checklist',
            'Checklist',
            'manage_options',
            'site-checklist.php',
            array($this, 'render_checklist_page')
        );
    }

    public function render_checklist_page()
    {
        include $this->pluginPath . '/views/admin/checklist.php';
    }
}