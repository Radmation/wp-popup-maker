<?php

namespace KbIntegrations\Core\Apis;

use \KbIntegrations\Core\Apis\BaseApi as BaseApi;
use \KbIntegrations\Core\Settings as Settings;

/**
 * Handles integration to Praxis API.
 *
 * URL: https://api.dev.pcmsuites.software/
 */
class ConversionCallsApi extends BaseApi {

  /**
   * The posting url.
   *
   * @var string
   */
  protected $base_endpoint = "https://www.lguploadsystem.com/capture_lead.cfm";

  /**
   * Post field data.
   *
   * @var
   */
  protected $data;

  /**
   * Http status code.
   *
   * @var
   */
  protected $http_status_code;

  /**
   * Response body from api.
   *
   * @var
   */
  protected $response_body;

  /**
   * Errors.
   *
   * @var string $errors
   */
  protected $errors;

  /**
   * Name of the api. Used in storing and getting local access tokens.
   *
   * @var string
   */
  protected $api_name = "conversion_calls";

  /**
   * Handles post request to api.
   *
   * @return false|void
   */
  public function post() {
    // Check to see if data is set before attempting post. If not then something failed.
    if (!isset($this->data)) {
      $this->set_errors("Post data is empty.");
      return false;
    }

    // Attempt post to create new web lead.
    $request_headers = array(
      "Content-length: " . strlen($this->data) . "",
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
    curl_setopt($ch, CURLOPT_URL, $this->base_endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);

    $response = curl_exec($ch);
    $errors = curl_error($ch);

    $status_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $this->set_http_status_code($status_code);

    // We have no curl errors.
    if (empty($errors)) {
      if ($status_code !== 200) {
        $this->set_errors("Failed to save " . $this->api_name . " lead data. Status code: $status_code." . " Response: " . print_r($response, true));
        error_log($this->get_errors());
      } else {
        if ($response === "Success" || $response === "Duplicate Lead Submission") {
          $this->set_vendor_message($response);
        } else {
          $this->set_errors("Failed to save " . $this->api_name . " lead data. Status code: $status_code." . " Response: " . print_r($response, true));
          error_log($this->get_errors());
        }
      }
    }

    // Got errors.
    if (!empty($errors)) {
      $this->set_errors($errors);
    }

    curl_close($ch);
  }

  /**
   * Set the data property.
   *
   * @param array $field_map
   */
  public function set_post_data(array $field_map) {
    $this->data = http_build_query($field_map);
  }

  /**
   * Get the data property.
   *
   * @return array
   */
  public function get_post_data() : string {
    return $this->data;
  }

  /**
   * Get the errors.
   *
   * @return string
   */
  public function get_errors() : string {
    return (string)$this->errors;
  }

  /**
   * Get the api name.
   *
   * @return string
   */
  public function get_api_name() : string {
    return (string)$this->api_name;
  }

  /**
   * Get the http status code.
   *
   * @return int
   */
  public function get_http_status_code() : int {
    return (int)$this->http_status_code;
  }

  /**
   * Get the vendor message.
   *
   * @return string
   */
  public function get_vendor_message() : string {
    return (string)$this->response_body;
  }

  /**
   * Set the vendor message.
   *
   * @param string $vendor_message
   */
  private function set_vendor_message(string $vendor_message) {
    $this->response_body = $vendor_message;
  }

  /**
   * Set the http status code.
   *
   * @param int $status_code
   */
  private function set_http_status_code(int $status_code) {
    $this->http_status_code = $status_code;
  }

  /**
   * Set errors.
   *
   * @param string $errors
   */
  private function set_errors(string $errors) {
    $this->errors = $errors;
  }
}