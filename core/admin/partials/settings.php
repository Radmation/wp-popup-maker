<?php
use \KbIntegrations\Core\Drawing\DashboardParts as DashboardParts;
use \KbIntegrations\Core\Drawing\FormParts as FormParts;
use \KbIntegrations\Core\Settings as Settings;

$settings = new Settings();
?>
<div class="wrap rounded bg-white border-left-4 border-bottom-4 border-right-4 border-info pb-4">
  <h1 class="plugin-title bg-info mb-4 p-4 text-white"><?php echo esc_html(get_admin_page_title()); ?></h1>
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-3">
        <?php echo DashboardParts::draw_navigation(); ?>
      </div>
      <div class="col-sm-9">
        <?php echo DashboardParts::draw_content(); ?>
      </div>
    </div>
  </div>
</div>