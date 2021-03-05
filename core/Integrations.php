<?php
namespace KbIntegrations\Core;

use \KbIntegrations\Core\Loader as Loader;
use \KbIntegrations\Core\I18n as I18n;
use \KbIntegrations\Core\Admin\Admin as Admin;
use \KbIntegrations\Core\Settings as Settings;
use \KbIntegrations\Core\FlashMessages as FlashMessages;
use \KbIntegrations\Core\WordpressApi as WordpressApi;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @author     Radley Anaya <radley.anaya@kellybrady.com>
 */
class Integrations {

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @var Loader $loader Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * The unique identifier of this plugin.
   *
   * @var string $kb_integrations The string used to uniquely identify this plugin.
   */
  protected $kb_integrations;

  /**
   * The current version of the plugin.
   *
   * @var string $version The current version of the plugin.
   */
  protected $version;

  /**
   * WordpressApi;
   * @var object  $wp_api WordpressApi instance.
   */
  protected $wp_api;

  /**
   * Define the core functionality of the plugin.
   *
   * Set the plugin name and the plugin version that can be used throughout the plugin.
   * Load the dependencies, define the locale, and set the hooks for the admin area and
   * the public-facing side of the site.
   */
  public function __construct() {
    if (defined('KB_INTEGRATIONS_VERSION')) {
      $this->version = KB_INTEGRATIONS_VERSION;
    }

    $this->kb_integrations = 'kb-integrations';
    $this->wp_api = new WordpressApi();

    $this->load_dependencies();
    $this->set_locale();
    $this->define_admin_hooks();
  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   */
  public function run() {
    $this->loader->run();
  }

  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @return    string    The name of the plugin.
   */
  public function get_plugin_name() {
    return $this->kb_integrations;
  }

  /**
   * The reference to the class that orchestrates the hooks with the plugin.
   *
   * @return    Loader    Orchestrates the hooks of the plugin.
   */
  public function get_loader() {
    return $this->loader;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @return    string    The version number of the plugin.
   */
  public function get_version() {
    return $this->version;
  }

  /**
   * Load the required dependencies for this plugin.
   *
   * Include the following files that make up the plugin:
   *
   * - Loader. Orchestrates the hooks of the plugin.
   * - Ii18n. Defines internationalization functionality.
   * - Admin. Defines all hooks for the admin area.
   * - Public. Defines all hooks for the public side of the site.
   *
   * Create an instance of the loader which will be used to register the hooks
   * with WordPress.
   */
  private function load_dependencies() {
    /**
     * The class responsible for orchestrating the actions and filters of the
     * core plugin.
     */
    $this->loader = new Loader();
  }

  /**
   * Define the locale for this plugin for internationalization.
   *
   * Uses the \KbLeadSafety\Component\I18n class in order to set the domain and to register the hook
   * with WordPress.
   */
  private function set_locale() {
    $plugin_i18n = new I18n($this->get_plugin_name());

    $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   */
  private function define_admin_hooks() {
    $plugin_admin = new Admin($this->get_plugin_name(), $this->get_version());
    $plugin_settings = new Settings();
    $flash_messages = new FlashMessages();

    // Add our admin styles and scripts.
    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

    // Add our hook to save our settings - admins only
    $this->loader->add_action('admin_post_kbi_save_settings', $plugin_settings, 'save_integration');

    // Add our hook to save our plugin integration settings
    $this->loader->add_action('admin_post_kbi_save_plugin_integrations', $plugin_settings, 'save_integration');

    // Add our hook to save failure notification email addresses
    $this->loader->add_action('admin_post_kbi_save_email_addresses', $plugin_settings, 'save_email_addresses');

    // Add our flash messages
    $this->loader->add_action('admin_notices', $flash_messages, 'show_flash_messages');

    // Tie into native wp hook 'admin_menu' to register new page.
    $this->loader->add_action('admin_menu', $plugin_admin, 'register_admin_pages');

    // Create our own WP hooks to pass data to plugin. Use 'WordpressApi' to handle building of responses and dispatching to appropriate apis.
    $this->loader->add_action('admin_post_nopriv_kbi_integrate_data', $this->wp_api, 'integrate_data', 10, 0);
    $this->loader->add_action('admin_post_kbi_integrate_data', $this->wp_api, 'integrate_data', 10, 0);
  }
}