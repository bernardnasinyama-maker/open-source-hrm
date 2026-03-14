<?php
$extra = '

/* ── Force all login text white ─────────────────────────── */
.fi-simple-main label,
.fi-simple-main span,
.fi-simple-main p,
.fi-simple-main a,
.fi-simple-main .fi-fo-field-wrp-label,
.fi-simple-main .fi-fo-field-wrp-label *,
.fi-simple-main .fi-checkbox-label,
.fi-simple-main .fi-fo-field-wrp-hint,
.fi-simple-main [class*="fi-"] {
    color: #e2e8f0 !important;
}

/* Remember me checkbox label */
.fi-simple-main .fi-checkbox-input + span,
.fi-simple-main .fi-fo-field-wrp-label label {
    color: #e2e8f0 !important;
}

/* Asterisk required star */
.fi-simple-main .fi-fo-field-wrp-label sup {
    color: #f87171 !important;
}
';

$current = file_get_contents('public/css/login-custom.css');
file_put_contents('public/css/login-custom.css', $current . $extra);
echo "Login text color fixed!\n";
echo "Hard refresh with Ctrl+Shift+R\n";
