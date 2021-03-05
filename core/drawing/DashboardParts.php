<?php

namespace KbIntegrations\Core\Drawing;

use KbIntegrations\Core\IntegrationTypes;
use \KbIntegrations\Core\Settings as Settings;

/**
 * Draws re-usable dashboard parts.
 */
class DashboardParts {
  use IntegrationTypes;

  /**
   * Draw integration heading.
   */
  public static function draw_integration_heading($integration_name) {
    $html = "<h2>$integration_name</h2>";
    $html .= "<hr>";

    return $html;
  }

  /**
   * Draws the navigation.
   *
   * @return string
   */
  public static function draw_navigation() {
    $integrations = self::get_integrations();
    $settings = new Settings();
    $notification_settings = $settings->get_notification_settings();
    $html = "";

    if (!empty(self::get_integrations())) {
      $html .= "<p><strong>Vendor Integrations:</strong></p>";
      $html .= "<ul class='nav nav-pills flex-column' role='tablist' aria-orientation='vertical'>";

      // Draw vendor integrations.
      foreach ($integrations as $vendor_integration) {
        // Skip plugin types
        if ($vendor_integration['type'] !== "vendor") {
          continue;
        }

        $html .= self::draw_integration_list_item($vendor_integration, $settings);
      }

      $html .= "<p class='hr'><strong>Plugin Integrations:</strong></p>";

      // Draw plugin integrations.
      foreach ($integrations as $plugin_integration) {
        // Skip plugin types
        if ($plugin_integration['type'] !== "plugin") {
          continue;
        }

        $html .= self::draw_integration_list_item($plugin_integration, $settings);
      }

      // Inject email address settings.
      $html .= "<li class='nav-item'>";
      $html .= "<hr>";
      $html .= "<p class=''><strong>Failure Notifications:</strong></p>";
      $html .= "<a id='failure-tab' data-toggle='pill' href='#failure' role='tab' aria-controls='failure' class='nav-link bg-secondary text-white'>";
      $html .= "Failure Notifications";
      if ($notification_settings['status'] === "on" && $notification_settings['email_addresses'] !== false && $notification_settings['email_addresses'] !== "") {
        $html .= "<span class='ml-2 badge badge-success'>On</span>";
      } else {
        $html .= "<span class='ml-2 badge badge-danger'>Off</span>";
      }
      $html .= "</a>";
      $html .= "</li>";

      // Inject  instructions.
      $html .= "<li class='nav-item'>";
      $html .= "<hr>";
      $html .= "<p><strong>How to use this plugin instructions:</strong></p>";
      $html .= "<a id='instructions-tab' data-toggle='pill' href='#instructions' role='tab' aria-controls='instructions' class='nav-link bg-secondary text-white'>";
      $html .= "Plugin Instructions";
      $html .= "</a>";
      $html .= "</li>";

      $html .= "</ul>";
    }

    return $html;
  }

  /**
   * Draws the content.
   */
  public static function draw_content() {
    $html = "";

    if (!empty(self::get_integrations())) {
      $settings = new Settings();
      $html .= "<div class='tab-content'>";
      foreach (self::get_integrations() as $integration) {
        $html .= "<div class='tab-pane fade' id='" . $integration['name'] . "' role='tabpanel' aria-labelledby='".$integration['name']."-tab'>";
        if ($integration['type'] === "vendor") {
          $html .= DashboardParts::draw_integration_heading($integration['nice_name'] . " <small class='text-muted'>Vendor</small>");
          $html .= FormParts::draw_form_opening();
          $html .= FormParts::draw_enable_buttons($settings->retrieve_integration($integration['name']));
          $html .= FormParts::draw_field_mappings($settings->retrieve_integration($integration['name']));
          $html .= wp_nonce_field('kbi_save_settings', 'kbi_integrations_nonce');
          $html .= FormParts::draw_hidden_field("action", "kbi_save_settings");
          $html .= FormParts::draw_hidden_field("integration_name", $integration['name']);
          $html .= "<p class='mb-0'>"._("To enable this integration on your forms add this hidden input to each form you wish to integrate:")."</p>";
          $html .= "<p><code>".htmlspecialchars("<input type='hidden' name='kbi_integration' value='".$integration['name']."'>")."</code></p>";
          $html .= FormParts::draw_submit_button("Save Integration");

          if (!empty($integration['extras'])) {
            $html .= FormParts::draw_extras($integration['extras']);
          }

          $html .= FormParts::draw_form_closing();
        } else if ($integration['type'] === "plugin") {
          $html .= DashboardParts::draw_integration_heading($integration['nice_name'] . " <small class='text-muted'>Plugin</small>");
          $html .= FormParts::draw_form_opening();
          $html .= FormParts::draw_enable_buttons($settings->retrieve_integration($integration['name']));
          $html .= wp_nonce_field('kbi_save_settings', 'kbi_integrations_nonce');
          $html .= FormParts::draw_hidden_field("action", "kbi_save_settings");
          $html .= FormParts::draw_hidden_field("integration_name", $integration['name']);
          $html .= FormParts::draw_submit_button("Save Integration");
          $html .= FormParts::draw_form_closing();
        }

        $html .= "</div>";
      }

      // Inject Failure Notifications.
      $html .= "<div class='tab-pane fade' id='failure' role='tabpanel' aria-labelledby='failure-tab'>";
      $html .= DashboardParts::draw_integration_heading("Failure Notifications <small class='text-muted'>Email Addresses</small>");
      $html .= "<p class='mb-4'><strong>"._("In the event that an integration fails you can be notified via email. This is highly recommended to use.")."</strong></p>";
      $html .= "<form method='post' action='".esc_html(admin_url('admin-post.php'))."'>";
      $html .= FormParts::draw_enable_buttons($settings->get_notification_settings());
      $html .= FormParts::draw_text_field("Email Address(es)", "kbi_email_addresses", $settings->get_notification_settings()['email_addresses'], "Separate multiple email addresses by a comma.");
      $html .= wp_nonce_field('kbi_save_email_addresses', 'kbi_integrations_nonce');
      $html .= FormParts::draw_hidden_field("action", "kbi_save_email_addresses");
      $html .= FormParts::draw_submit_button("Save Failure Notification Settings");
      $html .= "</form>";
      $html .= "</div>";

      // Inject instructions.
      $html .= "<div class='tab-pane fade' id='instructions' role='tabpanel' aria-labelledby='instructions-tab'>";
      $html .= DashboardParts::draw_integration_heading("How to use this plugin? <small class='text-muted'>Instructions</small>");
      $html .= "<p class='mb-1'><strong>"._("Let Plugin Handle Post")."</strong></p>";
      $html .= "<p class='mb-1'>"._("To allow this plugin to handle your form posts add this hidden input to each form you wish to have handled:")."</p>";
      $html .= "<div class='border-info border-left-6 pl-2 py-2'>";
      $html .= "<p class='m-0'><code>". htmlspecialchars("<input type='hidden' name='action' value='kbi_integrate_data'>")."</code></p>";
      $html .= "</div>";
      $html .= "<p>"._("Note: This will be in addition to each hidden input for each vendor integration.")."</p>";
      $html .= "<hr>";
      $html .= "<p class='mb-1'><strong>"._("TBD: Handle Post Yourself/Use Plugin Hook")."</strong></p>";
      $html .= "<p class='mb-1'>"._("TBD: If you wish to handle your own form posts you can use the plugins hooks. The hooks are:")."</p>";
      $html .= "<div class='border-info border-left-6 pl-2 py-2'>";
      $html .= "<p class='m-0'><code>&#36;". htmlspecialchars("vendors_responses = do_action('kbi_integrate_data');")."</code></p>";
      $html .= "<p class='m-0'>"._("TBD: This will return a json object containing the vendor(s) response(s).")."</p>";
      $html .= "</div>";
      $html .= "<p>"._("Note: If you are using our hook then you will still need to include the hidden inputs for each integration you want enabled.")."</p>";
      $html .= "</div>";

      $html .= "</div>"; // ./tab-content
    }

    return $html;
  }

  /**
   * Draw an integration list item.
   *
   * @param array $integration
   * @param object $settings
   * @return string
   */
  protected static function draw_integration_list_item(array $integration, $settings) {
    $integration_settings = $settings->retrieve_integration($integration['name']);

    $html = "<li class='nav-item'>";
    $html .= "<a id='" . $integration['name'] . "-tab' data-toggle='pill' href='#" . $integration['name'] . "' role='tab' aria-controls='" . $integration['name'] . "' class='nav-link bg-secondary text-white'>";
    $html .= $integration['nice_name'];
    if ($integration_settings['status'] === "on") {
      $html .= "<span class='ml-2 badge badge-success'>On</span>";
    } else {
      $html .= "<span class='ml-2 badge badge-danger'>Off</span>";
    }
    $html .= "</a>";
    $html .= "</li>";

    return $html;
  }
}