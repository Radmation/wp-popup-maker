<?php
namespace KbIntegrations\Core\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @author     Radley Anaya <radley.anaya@kellybrady.com>
 */
class Admin {

  /**
   * The ID of this plugin.
   *
   * @var string $kb_integrations The ID of this plugin.
   */
  private $kb_integrations;

  /**
   * The version of this plugin.
   *
   * @var string $version The current version of this plugin.
   */
  private $version;

  /**
   * Holds our admin pages.
   *
   * @var array
   */
  private $admin_pages = array();

  /**
   * Initialize the class and set its properties.
   *
   * @param string $kb_integrations The name of this plugin.
   * @param string $version The version of this plugin.
   *
   */
  public function __construct($kb_integrations, $version) {
    $this->kb_integrations = $kb_integrations;
    $this->version = $version;

    array_push($this->admin_pages, $kb_integrations);
  }

  /**
   * Register the stylesheets for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_styles() {
    $styles = (IS_DEV_ENVIRONMENT) ? 'kbi-styles.css' : 'kbi-styles.min.css';

    if ($this->is_plugin_page()) {
      wp_enqueue_style($this->kb_integrations, KBI_PLUGIN_ROOT_PATH_URI. 'dist/admin/css/' . $styles, null, $this->version, 'all');
    }
  }

  /**
   * Register the JavaScript for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts() {
    $scripts = (IS_DEV_ENVIRONMENT) ? 'kbi-scripts.js' : 'kbi-scripts.min.js';

    if ($this->is_plugin_page()) {
      wp_enqueue_script($this->kb_integrations, KBI_PLUGIN_ROOT_PATH_URI . 'dist/admin/js/' . $scripts, null, $this->version, true);
    }
  }

  /**
   * Register admin pages.
   *
   */
  public function register_admin_pages() {
    // Add submenu page to the Tools main menu.
    add_management_page("KB Integrations", "KB Integrations", "manage_options", $this->kb_integrations, array(
      $this,
      "draw_settings_page"
    ));
  }

  /**
   * Render our settings page.
   */
  public function draw_settings_page() {
    require_once("partials/settings.php");
  }

  /**
   * Checks pages to see if the page is one belonging to our plugin.
   *
   * @return bool is_plugin_page
   */
  private function is_plugin_page() {
    if (!isset($_GET["page"])) {
      return false;
    }

    if (in_array($_GET["page"], $this->admin_pages)) {
      return true;
    }

    return false;
  }
}