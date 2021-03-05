<?php
namespace KbIntegrations\Core;

use \KbIntegrations\Core\Integrations as Integrations;

/**
 * This class defines all code necessary to run during the plugin's activation. This is our plugins bootstrap file.
 */
class Activator {

  /**
   * Activate plugin.
   *
   * This will create database tables and necessary items to properly use.
   */
  public static function activate() {

  }

  /**
   * Deactivate plugin.
   *
   * This will safely deactivate the plugin.
   */
  public static function deactivate() {
  }

  /**
   * Bootstrap plugin.
   */
  public static function run() {
    $plugin = new Integrations();
    $plugin->run();
  }
}