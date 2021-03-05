<?php
namespace KbIntegrations\Core;

/**
 * Class Response. A simple class to format responses.
 *
 * @package KbIntegrations\Core\Integrations
 */
class Response {

  /**
   * Holds the http status code.
   *
   * @var int $http_status_code
   */
  protected $http_status_code;

  /**
   * Holds messages.
   *
   * @var array
   */
  protected $messages = array();

  /**
   * Sets the http status code.
   *
   * @param $status_code
   */
  public function set_http_status_code($status_code) {
    $this->http_status_code = $status_code;
  }

  /**
   * Set the response message.
   *
   * @param $message
   */
  public function add_response_message($message) {
    array_push($this->messages, $message);
  }
}
