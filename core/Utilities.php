<?php
namespace KbIntegrations\Core;

/**
 * Class Utilities provides useful functions to plugin.
 *
 * @package KbIntegrations\Core
 */
class Utilities {

  /**
   * Gets the field count for fields that need to be mapped.
   *
   * @param array $integration_options
   * @return false|float|int
   */
  public static function get_mapping_fields_count(array $integration_options) {
    if (!is_array($integration_options)) {
      return false;
    }

    return (count($integration_options) - 2) / 2;
  }
}