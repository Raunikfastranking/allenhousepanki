<?php
$newsletterButtonLabel = isset($newsletterButton) ? (string) $newsletterButton : 'Subscribe';
require_once dirname(__DIR__) . '/shared/includes/newsletter-form-init.php';
cms_newsletter_form_init_script($newsletterButtonLabel);
