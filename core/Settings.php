<?php
namespace KbIntegrations\Core;

use \KbIntegrations\Core\FlashMessages as FlashMessages;
use \KbIntegrations\Core\Utilities as Utilities;

/**
 * Class Settings handles our settings.
 *
 * @package KbIntegrations\Core
 */
class Settings {
  use IntegrationTypes;

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
   * Saves integration option.
   */
  public function save_integration() {
    self::do_backend_nonce_check();

    $options_array = array();

    $skip_keys = array(
      "",
      "kbi_integrations_nonce",
      "_wp_http_referer",
      "action"
    );

    $skip_values = array("");

    if (!isset($_POST['status'])) {
      $_POST['status'] = "off";
    }

    foreach ($_POST as $key => $value) {
      if (!in_array($key, $skip_keys) && !in_array($value, $skip_values)) {
        $options_array[$key] = $value;
      }
    }

    if ($this->get_integration($_POST['integration_name']) !== false) {
      if (get_option("kbi_" . $this->get_integration_name($_POST['integration_name'])) === $options_array) {
        FlashMessages::queue_flash_message("Success: " . $this->get_integration_nice_name($_POST['integration_name']) . " integration updated.", 'updated');
      } else {
        $option_updated = update_option("kbi_" . $this->get_integration_name($_POST['integration_name']), $options_array);

        if ($option_updated) {
          FlashMessages::queue_flash_message("Success: " . $this->get_integration_nice_name($_POST['integration_name']) . " integration updated.", 'updated');
        } else {
          FlashMessages::queue_flash_message("Error: " . $this->get_integration_nice_name($_POST['integration_name']) . " integration failed to update.", 'error');
        }
      }
    } else {
      FlashMessages::queue_flash_message("Error: Integration not found.", 'error');
    }

    wp_redirect(self::get_redirect_url());
    exit;
  }

  /**
   * Handles plugin post request to save email notification settings.
   */
  public function save_email_addresses() {
    self::do_backend_nonce_check();
    $email_settings = array();

    if (isset($_POST['kbi_email_addresses'])) {
      $email_settings['email_addresses'] = $_POST['kbi_email_addresses'];
    }

    if (isset($_POST['status'])) {
      $email_settings['status'] = $_POST['status'];
    }

    update_option("kbi_notification_settings", $email_settings);

    FlashMessages::queue_flash_message("Success: Failure notification settings updated.", 'updated');
    wp_redirect(self::get_redirect_url());
    exit;
  }

  /**
   * Get the email notification settings.
   *
   * @return false|mixed|void
   */
  public function get_email_addresses() {
    return get_option("kbi_notification_settings");
  }

  /**
   * Store access token in wp options using update_option().
   *
   * @param $integration_name
   * @param $access_token
   * @return bool
   */
  public function store_api_access_token(string $integration_name, array $access_token) {
    if ($this->get_integration_name($integration_name) === false) {
      return false;
    }

    $option_key = "kbi_" . $this->get_integration_name($integration_name) . "_access_token";

    return update_option($option_key, $access_token);
  }

  /**
   * Get access token from wp options using get_option().
   *
   * @param $integration_name
   * @return false|mixed|void|null
   */
  public function get_api_access_token(string $integration_name) {
    if ($this->get_integration_name($integration_name) === false) {
      return null;
    }

    $option_key = "kbi_" . $this->get_integration_name($integration_name) . "_access_token";

    return get_option($option_key);
  }

  /**
   * Get email addresses from wp options using get_option().
   * @return false|mixed|void
   */
  public function get_notification_settings() {
    return get_option("kbi_notification_settings");
  }

  /**
   * Gets integrations option from wp_options using get_option().
   *
   * @param $integration_key
   * @return false|mixed|void
   */
  public function retrieve_integration($integration_key) {
    if ($this->get_integration_name($integration_key) !== false) {
      return get_option("kbi_" . $this->get_integration_name($integration_key));
    }

    return null;
  }

  /**
   * Build and returns the field map needed for apis.
   *
   * @param $integration_options
   * @return array|false
   */
  public function get_field_map($integration_options) {
    if ($integration_options === false) {
      return false;
    }

    $field_count = Utilities::get_mapping_fields_count($integration_options);

    $field_map = array();

    // Build field map
    for ($i = 1; $i <= $field_count; $i++) {
      $key = $integration_options["map-to-" . $i]; // firstname
      $value = isset($_POST[$integration_options["map-from-" . $i]]) ? $_POST[$integration_options["map-from-" . $i]] : ""; // value of first_name = "Lael" utm_source

      $field_map[$key] = $value;
    }

    return $field_map;
  }

  /**
   * Verifies whether or not nonce checked is valid, from back-end/admin/cms.
   */
  protected function do_backend_nonce_check() {
    if (!isset($_POST['kbi_integrations_nonce']) || !wp_verify_nonce($_POST['kbi_integrations_nonce'], $_POST['action'])) {
      FlashMessages::queue_flash_message("Error: Nonce check fail.", 'error');
      wp_redirect(self::get_redirect_url());
      exit;
    }
  }

  /**
   * Get the URL to redirect the browser back to.
   *
   * @return false|string|void  Returns the redirect url.
   */
  protected function get_redirect_url() {
    if (isset($_POST['redirect']) && $_POST['redirect'] !== "") {
      return $_POST['redirect'];
    } else if (!empty(wp_get_referer())) {
      return wp_get_referer();
    } else {
      return admin_url("admin.php?page=" . KBI_PLUGIN_DOMAIN);
    }
  }
}