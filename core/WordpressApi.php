<?php

namespace KbIntegrations\Core;

use \KbIntegrations\Core\Response as Response;
use \KbIntegrations\Core\Settings as Settings;
use \KbIntegrations\Core\Utilities as Utilities;
use \KbIntegrations\Core\Apis\ConversionCallsApi;
use \KbIntegrations\Core\Apis\PraxisApi;

/**
 * The class the sits in-between integration calls and wordpress.
 *
 * This is the class that takes responses from integrations and
 * returns them to wordpress.
 *
 * @author     Radley Anaya <radley.anaya@kellybrady.com>
 */
class WordpressApi {

  /**
   * Structure to pass data in a consistent manner.
   *
   * @var array
   */
  private $responses = array();

  /**
   * Settings.
   *
   * @var \KbIntegrations\Core\Settings
   */
  private $settings;

  /**
   * WordpressApi constructor.
   */
  public function __construct() {
    $this->settings = new Settings();
  }

  /**
   * Handles inserting lead data.
   *
   * @returns string.
   */
  public function integrate_data() {
    // Check if any integrations are set in post.
    if (!isset($_POST['kbi_integration'])) {
      return json_encode("No integrations are set.");
    }

    if (is_array($_POST['kbi_integration'])) {
      // Multiple integrations
      foreach ($_POST['kbi_integration'] as $integration_name) {
        $integration_options = $this->settings->retrieve_integration($integration_name);

        if ($integration_options['status'] !== "on") {
          continue;
        }

        $this->execute_integration($integration_name);
      }
    } else {
      // Single integration
      $integration_options = $this->settings->retrieve_integration($_POST['kbi_integration']);

      if ($integration_options['status'] !== "on") {
        return false;
      }

      $this->execute_integration($_POST['kbi_integration']);
    }

    $this->handle_redirect();
  }

  /**
   * Hold the logic for executing an integration.
   *
   * @param $integration_name
   */
  private function execute_integration($integration_name) {
    // Get integration options by integration name.
    $integration_options = $this->settings->retrieve_integration($integration_name);

    // Get the safetynet integration.
    $safetynet_integration = $this->settings->retrieve_integration("kb_plugin_safetynet");

    // Get Field Mapping for API from integrations options.
    $field_map = $this->settings->get_field_map($integration_options);

    // Set up API.
    $api = $this->get_api($integration_name);

    // Set our field mapping.
    $api->set_post_data($field_map);

    // Integrate with kb safety net. This is before post occurs, insert raw data.
    if ($safetynet_integration['status'] === "on" && is_plugin_active("kb-safetynet/kb-lead-saftey.php")) {
      ob_start();
      do_action("kbsn_insert_data", $api->get_post_data(), $_SERVER['HTTP_REFERER']);
      $kb_safetynet_data = json_decode(ob_get_clean());

      // Check if we have a lead id.
      if ($kb_safetynet_data->data->LeadId) {
        ob_start();
        do_action("kbsn_insert_status", (int)$kb_safetynet_data->data->LeadId, $api->get_api_name());
        $kb_safetynet_status = json_decode(ob_get_clean());
      }
    }

    // Post Data to Vendor.
    $api->post();

    // Check if errors and handle accordingly.
    if ($api->get_errors()) {
      $this->handle_errors($api->get_errors(), $api->get_api_name());
    } else {
      // Build response.
      $response = new Response();
      $response->set_http_status_code($api->get_http_status_code());
      $response->add_response_message($api->get_vendor_message());

      // Add response.
      $this->add_response($response);
    }

    // Integrate with kb safety net. This is after post occurs, insert vendor response.
    if ($safetynet_integration['status'] === "on" && isset($kb_safetynet_status->data->StatusId) && is_plugin_active("kb-safetynet/kb-lead-saftey.php")) {
      $api_response = !empty($api->get_vendor_message()) ? $api->get_vendor_message() : $api->get_errors();
      ob_start();
      do_action("kbsn_update_response", (int)$kb_safetynet_status->data->StatusId, $api->get_http_status_code(), $api_response);
      $update_response = json_decode(ob_get_clean());
    }
  }

  /**
   * Handles redirect after handling post.
   */
  private function handle_redirect() {
    if (isset($_POST['redirect_url'])) {
      wp_redirect($_POST['redirect_url']);
    } else if (wp_get_referer() !== false) {
      wp_redirect(wp_get_referer());
    } else {
      wp_redirect(get_home_url());
    }
    exit;
  }

  /**
   * Handles api errors. Sends email.
   *
   * @param $error_message
   * @param $api_name
   */
  private function handle_errors($error_message, $api_name) {
    $notification_settings = $this->settings->get_email_addresses();

    if ($notification_settings['status'] === "on" && !empty($notification_settings['email_addresses'])) {
      $headers = array('Content-Type: text/html; charset=UTF-8');
      $referrer = $_SERVER['HTTP_REFERER'];
      $to = $notification_settings['email_addresses'];
      $subject = "KB Integrations Message - Integration Failure";
      $message = "Integration that failed: $api_name<br>";
      $message .= "Site Url: " . home_url() . "<br>";
      $message .= "Referrer Url: $referrer<br>";
      $message .= "Error Message: " . $error_message;

      wp_mail($to, $subject, $message, $headers);
    }
  }

  /**
   * Gets the correct API for each integration.
   *
   * @param $integration_name
   * @return false|ConversionCallsApi|PraxisApi
   */
  private function get_api($integration_name) {
    $api = false;

    switch($integration_name) {
      case "conversion_calls":
        $api = new ConversionCallsApi();
        break;
      case "praxis":
        $api = new PraxisApi();
        break;
    }

    return $api;
  }

  /**
   * Add a response.
   *
   * @param $response
   */
  private function add_response($response) {
    array_push($this->responses, $response);
  }
}