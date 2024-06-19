<div class="wrap">
    <h1>Hubspot Company Sync Settings</h1>
    <form method="post" action="options.php">
        <?php settings_fields( 'hubspot_company_sync_settings' ); ?>
        <?php do_settings_sections( 'hubspot_company_sync_settings' ); ?>
        <?php submit_button(); ?>
    </form>
</div>