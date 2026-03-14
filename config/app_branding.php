<?php
return [
    "name"          => env("APP_BRAND_NAME",    "CRBC Uganda HRM"),
    "short_name"    => env("APP_BRAND_SHORT",   "CRBC HRM"),
    "company"       => env("APP_COMPANY_NAME",  "CRBC Uganda Ltd"),
    "project"       => env("APP_PROJECT_NAME",  "Kayunga-Bbaale-Galiraya Road (87KM)"),
    "currency"      => env("APP_CURRENCY",      "UGX"),
    "currency_symbol" => env("APP_CURRENCY_SYM","UGX"),
    "country"       => env("APP_COUNTRY",       "Uganda"),
    "timezone"      => env("APP_TIMEZONE",      "Africa/Kampala"),
    "logo_path"     => env("APP_LOGO_PATH",     null),
    "primary_color" => env("APP_PRIMARY_COLOR", "#6366f1"),
    "admin_email"   => env("APP_ADMIN_EMAIL",   "admin@company.com"),
    "support_email" => env("APP_SUPPORT_EMAIL", "support@company.com"),
    "paye_enabled"  => env("APP_PAYE_ENABLED",  true),
    "nssf_enabled"  => env("APP_NSSF_ENABLED",  true),
    "working_hours" => env("APP_WORKING_HOURS", "08:00"),
    "late_threshold"=> env("APP_LATE_THRESHOLD","08:30"),
];
