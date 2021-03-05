<?php

namespace KbIntegrations\Core;

/**
 * Trait IntegrationTypes stores the types of integrations
 * the plugin handles. It also provides ways to get integrations.
 *
 * @package KbIntegrations\Core
 */
trait IntegrationTypes {

  protected static $integration_types = array(
    "conversion_calls" => array(
      "name" => "conversion_calls",
      "nice_name" => "Conversion Calls",
      "type" => "vendor",
      "extras" => array()
    ),
    "praxis" => array(
      "name" => "praxis",
      "nice_name" => "Praxis",
      "type" => "vendor",
      "extras" => array(
        "wp_config" => array(
          "PRAXIS_CLIENT_ID",
          "PRAXIS_CLIENT_SECRET",
        )
      )
    ),
    "kb_plugin_safetynet" => array(
      "name" => "kb_plugin_safetynet",
      "nice_name" => "KB Safety Net",
      "type" => "plugin",
      "extras" => array()
    ),
  );

  /**
   * Get the integration.
   *
   * @param string $integration_key
   * @return false|string[]
   */
  protected function get_integration(string $integration_key) {
    if (isset(self::$integration_types[$integration_key])) {
      return self::$integration_types[$integration_key];
    }

    return false;
  }

  /**
   * Get the integration name.
   *
   * @param string $integration_key
   * @return false|string
   */
  public function get_integration_name(string $integration_key) {
    if (isset(self::$integration_types[$integration_key])) {
      return self::$integration_types[$integration_key]['name'];
    }

    return false;
  }

  /**
   * Get the integration nice name.
   *
   * @param string $integration_key
   * @return false|string
   */
  protected function get_integration_nice_name(string $integration_key) {
    if (isset(self::$integration_types[$integration_key])) {
      return self::$integration_types[$integration_key]['nice_name'];
    }

    return false;
  }

  /**
   * Get the integrations.
   *
   * @return \string[][]
   */
  protected static function get_integrations() {
    return self::$integration_types;
  }
}