<?php
namespace KbIntegrations\Core\Apis;

/**
 * Class BaseApi defines the contract that inheriting classes with implement. Forces a consistent pattern.
 *
 * @package KbIntegrations\Core\Apis
 */
abstract class BaseApi {

  /**
   * Send data function.
   */
  public function post() {}

  /**
   * Set the post data. Needed to be done before posting data.
   *
   * @param array $field_map
   */
  public function set_post_data(array $field_map) {}

  /**
   * Get the post data.
   *
   * @return string
   */
  public function get_post_data() : string {}

  /**
   * Get the http status code.
   *
   * @return int
   */
  public function get_http_status_code() : int {}

  /**
   * Get the vendor message/response.
   *
   * @return string
   */
  public function get_vendor_message() : string {}

  /**
   * Get errors.
   *
   * @return string
   */
  public function get_errors() : string {}

  /**
   * Get the name of the api.
   *
   * @return string
   */
  public function get_api_name() : string {}

  /**
   * Set the http status code.
   *
   * @param int $status_code
   */
  private function set_http_status_code(int $status_code) {}

  /**
   * Set the vendor message.
   *
   * @param string $vendor_message
   */
  private function set_vendor_message(string $vendor_message) {}

  /**
   * Get errors.
   *
   * @param string $errors
   */
  private function set_errors(string $errors) {}
}