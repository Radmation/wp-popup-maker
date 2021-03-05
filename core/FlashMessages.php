<?php
namespace KbIntegrations\Core;

/**
 * Create wp messages that get deleted out after view.
 * Uses wp_options.
 *
 * Useful for displaying messages to admins.
 *
 * @author     Radley Anaya <radley.anaya@kellybrady.com>
 */
class FlashMessages {

  /**
   * Shows a flash message on the WP Admin screens.
   *
   * @param string $message the content of the flash message
   * @param string $class add any classes to the flash message
   */
  public static function queue_flash_message($message, $class = '') {
    $default_allowed_classes = array('error', 'updated');
    $allowed_classes = apply_filters('flash_messages_allowed_classes', $default_allowed_classes);
    $default_class = apply_filters('flash_messages_default_class', 'updated');

    if (!in_array($class, $allowed_classes)) $class = $default_class;

    $flash_messages = maybe_unserialize(get_option('wp_flash_messages', array()));
    $flash_messages[$class][] = $message;

    update_option('wp_flash_messages', $flash_messages);
  }

  /**
   * Removed a flash message on the WP Admin screens.
   */
  public function show_flash_messages() {
    $flash_messages = maybe_unserialize(get_option('wp_flash_messages', ''));

    if (is_array($flash_messages)) {
      foreach ($flash_messages as $class => $messages) {
        foreach ($messages as $message) {
          echo "<div class='$class'><p>$message</p></div>";
        }
      }
    }

    // Clear flash messages
    delete_option('wp_flash_messages');
  }
}