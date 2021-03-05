<?php
namespace KbIntegrations\Core;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 */
class I18n {

  /**
   * The text domain.
   *
   * @var string
   */
  protected $text_domain;

  /**
   * I18n constructor.
   */
  public function __construct($plugin_name) {
    $this->text_domain = $plugin_name;
  }

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
      $this->text_domain,
			false,
			dirname(dirname( plugin_basename( __FILE__ ))) . '/languages/'
		);
	}
}
