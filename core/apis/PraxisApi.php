<?php
namespace KbIntegrations\Core\Apis;

use \KbIntegrations\Core\Apis\BaseApi as BaseApi;
use KbIntegrations\Core\Settings as Settings;

/**
 * Handles integration to Praxis API.
 */
class PraxisApi extends BaseApi {

  /**
   * The protocol to use.
   *
   * @var string
   */
  protected $protocol;

  /**
   * The base url for the api.
   *
   * @var string
   */
  protected $base_endpoint;

  /**
   * Endpoint to post new leads.
   *
   * @var string
   */
  protected $webleads_endpoint;

  /**
   * Endpoint for access token.
   *
   * @var string
   */
  protected $access_token_endpoint;

  /**
   * Access token.
   *
   * @var
   */
  protected $access_token;

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
   * Client Id.
   *
   * @var string
   */
  protected $client_id;

  /**
   * Client secret.
   *
   * @var string
   */
  protected $client_secret;

  /**
   * Grant type.
   *
   * @var string
   */
  protected $grant_type = "grant_type=client_credentials";

  /**
   * Name of the api. Used in storing and getting local access tokens.
   *
   * @var string
   */
  protected $api_name = "praxis";

  /**
   * ConversionCallsApi constructor.
   */
  public function __construct() {
    $this->protocol = "https://";
    $this->base_endpoint = (IS_DEV_ENVIRONMENT) ? "api-dev.shared.pcmsuites.software" : "api-prod.shared.pcmsuites.software";
    $this->webleads_endpoint = $this->protocol . $this->base_endpoint . "/api/webleads";
    $this->access_token_endpoint = $this->protocol . $this->base_endpoint . "/oauth/token";

    // Retrieved from wp-config.
    $this->client_id = PRAXIS_CLIENT_ID;  // Used as username
    $this->client_secret = PRAXIS_CLIENT_SECRET; // Used as password
  }

  /**
   * Handles post request to api.
   *
   * @return false|void
   */
  public function post() {
    // Get access token before attempting post.
    $this->get_access_token();

    // If local access token is not something went wrong.
    if (!isset($this->access_token)) {
      return false;
    }

    // Check to see if data is set before attempting post. If not then something failed.
    if (!isset($this->data)) {
      $this->set_errors("Post data is empty.");
      return false;
    }

    // Attempt post to create new web lead.
    $request_headers = array(
      "Content-length: " . strlen($this->data) . "",
      "Authorization: Bearer " . $this->access_token['access_token'] . "",
      "Host: $this->base_endpoint",
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
    curl_setopt($ch, CURLOPT_URL, $this->webleads_endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);

    $response = curl_exec($ch);
    $errors = curl_error($ch);

    $status_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $this->set_http_status_code($status_code);

    // We have no curl errors.
    if (empty($errors)) {
      // Need to check what server says.
      if ($status_code !== 200) {
        $this->set_errors("Failed to save " . $this->api_name . " lead data. Status code: $status_code." . " Response: " . print_r($response, true));
        error_log($this->get_errors());
      } else {
        $this->set_vendor_message($response);
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
   * Set the http status code.
   *
   * @param int $status_code
   */
  private function set_http_status_code(int $status_code) {
    $this->http_status_code = $status_code;
  }

  /**
   * Gets access token from API and stores it locally.
   *
   * @return false|mixed
   */
  private function get_access_token() {
    // First try to get local access token. Sets the access token if one was found.
    $this->get_local_access_token();

    // If local access token is set and not expired then we have nothing to do.
    if (isset($this->access_token) && $this->is_access_token_expired() === false) {
      return true;
    }

    if ($this->client_id === null || !isset($this->client_id) || $this->client_secret === null || !isset($this->client_secret)) {
      $this->set_errors("Failed to get $this->api_name api credentials. Please verify they are set in the wp-config.php file.");
      error_log("Failed to get $this->api_name api credentials. Please verify they are set in the wp-config.php file.");
      return false;
    }

    // Make api call to get new access token.
    $request_headers = array(
      "Content-length: ".strlen($this->grant_type)."",
      "Host: $this->base_endpoint",
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
    curl_setopt($ch, CURLOPT_USERPWD, $this->client_id . ":" . $this->client_secret);
    curl_setopt($ch, CURLOPT_URL, $this->access_token_endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->grant_type);
    $response = curl_exec($ch);
    $errors = curl_error($ch);

    // Everything ran we as expected.
    if (empty($errors)) {
      $status_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

      // Check to see if server returned something other than 200 status code.
      if ($status_code !== 200) {
        $this->set_errors("Failed to get " . $this->api_name . " access token.");
        error_log($this->get_errors() . " Status code: $status_code" . " Response: " . print_r($response, true));
        return false;
      } else {
        $access_token = json_decode($response, true);

        // Calculate how many seconds until the expiration token expires.
        $seconds = (int)$access_token['expires_in'];

        // Get the current time and add seconds to it.
        $expires_on = strtotime("+$seconds seconds");

        // Inject our new param to the access token.
        $access_token["expires_on"] = $expires_on;

        // Save the local access token.
        $this->store_local_access_token($access_token);

        // Set the access token property.
        $this->access_token = $access_token;
      }
    }

    // Got errors.
    if (!empty($errors)) {
      $this->set_errors($errors);
    }

    curl_close($ch);
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
   * Set errors.
   *
   * @param string $errors
   */
  private function set_errors(string $errors) {
    $this->errors = $errors;
  }

  /**
   * Store local access token using settings.
   *
   * @param $access_token
   */
  private function store_local_access_token($access_token) {
    $settings = new Settings();
    $settings->store_api_access_token($this->api_name, $access_token);
  }

  /**
   * Get local access token and set access token property.
   */
  private function get_local_access_token() {
    $settings = new Settings();
    $access_token = $settings->get_api_access_token($this->api_name);

    if ($access_token !== false) {
      $this->access_token = $access_token;
    }
  }

  /**
   * Determine if the access token is expired.
   *
   * @return bool
   */
  private function is_access_token_expired(): bool {
    if (!isset($this->access_token)) {
      return true;
    }

    $current_time = strtotime("now");
    $expires_time = (int)$this->access_token['expires_on'];
    $difference = $expires_time - $current_time;

    if ($difference <= 0) {
      return true;
    }

    return false;
  }
}