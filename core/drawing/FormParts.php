<?php
namespace KbIntegrations\Core\Drawing;

use \KbIntegrations\Core\Utilities as Utilities;
/**
 * Draws re-usable form parts.
 */
class FormParts {

  /**
   * Draws enable buttons.
   *
   * @return string
   */
  public static function draw_enable_buttons($integration_options = null) : string {
    $html = "<div class='btn-group btn-group-toggle' data-toggle='buttons'>";
    $html .= "<label class='btn btn-primary'>";
    $html .= "<input type='radio' name='status' value='on' ".($integration_options['status'] === "on" ? "checked" : "")."> Enabled";
    $html .= "</label>";
    $html .= "<label class='btn btn-primary'>";
    $html .= "<input type='radio' name='status' value='off' ".($integration_options['status'] === "off" ? "checked" : "dfgds")."> Disabled";
    $html .= "</label>";
    $html .= "</div>";
    $html .= "<hr>";

    return $html;
  }

  /**
   * Draw submit button.
   *
   * @param string $button_text
   * @return string
   */
  public static function draw_submit_button($button_text = "Save Settings", $id = null) : string {
    return "<div class='form-group'><button class='btn btn-success btn-save' ".($id !== null ? "id='$id'": "").">$button_text</button></div>";
  }

  /**
   * Draw field mappings.
   *
   * @param null $settings
   * @return string
   */
  public static function draw_field_mappings($integration_options = null) : string {
    $html = "<h4>Field Mappings</h4>";
    $html .= "<div class='field-mappings'>";

    if ($integration_options !== null && is_array($integration_options)) {
      $count = Utilities::get_mapping_fields_count($integration_options);

      for ($i = 1; $i <= $count; $i++) {
        $value_from = $integration_options['map-from-' . $i];
        $value_to = $integration_options['map-to-' . $i];

        $html .= "<div class='row align-items-center field-mapping-row'>";
        $html .= "<div class='col form-group'>";
        $html .= "<input type='text' name='map-from-$i' value='$value_from' class='mapping-field mapping-field-from form-control'>";
        $html .= "</div>";
        $html .= "<div class='col-md-auto form-group'>";
        $html .= "<code>=></code>";
        $html .= "</div>";
        $html .= "<div class='col form-group'>";
        $html .= "<input type='text' name='map-to-$i' value='$value_to' class='mapping-field mapping-field-to form-control'>";
        $html .= "</div>";
        $html .= "<div class='col-md-auto form-group'>";
        $html .= "<button class='btn btn-danger remove-field-mapping'>Remove</button>";
        $html .= "</div>";
        $html .= "</div>";
      }
    }

    $html .= "</div>";
    $html .= "<div class='form-group'><button type='button' class='btn btn-primary add-field-mapping'>Add Field Mapping</button></div>";
    $html .= "<hr>";

    return $html;
  }

  /**
   * Draw form field for hidden input.
   *
   * @param string $name
   * @param string $value
   * @return string
   */
  public static function draw_hidden_field($name, $value, $id = null) : string {
    return "<input type='hidden' " . (isset($id) ? "id='$id'" : "") . " name='$name' value='$value'>";
  }

  /**
   * Draw form field for text.
   *
   * @param $label
   * @param $name
   * @param null $value
   * @param null $help_text
   * @return string
   */
  public static function draw_text_field($label, $name, $value = null, $help_text = null) : string {
    $html = "<div class='form-group'>";
    $html .= "<label>$label</label>";
    $html .= "<input type='text' class='form-control' name='$name' value='$value'>";

    if ($help_text !== null) {
      $html .= "<small class='form-text text-muted'>$help_text</small>";
    }

    $html .= "</div>";

    return $html;
  }

  /**
   * Draw extras.
   *
   * @param $extras
   * @return string
   */
  public static function draw_extras($extras) {
    $html = "";

    if ($extras['wp_config']) {
      $html .= "<hr>";
      $html .= "<h4>Configuration Settings</h4>";
      foreach ($extras['wp_config'] as $wp_config_constant) {
        if (!defined($wp_config_constant)) {
          $html .= "<div class='alert alert-danger'><strong>Configuration Error Detected!</strong><br>Constant <code>$wp_config_constant</code> is not defined in the wp-config.php file.</div>";
        } else {
          $html .= "<div class='alert alert-success'><strong>Configuration Found.</strong><br>Constant <code>$wp_config_constant</code> is defined in the wp-config.php.</div>";
        }
      }
    }

    return $html;
  }

  /**
   * Draw form opening tag.
   * @return string
   */
  public static function draw_form_opening() {
    return "<form method='post' action='".esc_html(admin_url('admin-post.php'))."'>";
  }

  public static function draw_form_closing() {
    return "</form>";
  }
}