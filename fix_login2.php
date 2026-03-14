<?php

$loginCss = <<<'CSS'
/* ── Background ─────────────────────────────────────────── */
.fi-simple-layout {
    background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%) !important;
    min-height: 100vh !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

/* ── Card box ────────────────────────────────────────────── */
.fi-simple-main {
    background: rgba(15, 12, 41, 0.85) !important;
    backdrop-filter: blur(20px) !important;
    border: 1px solid rgba(139, 92, 246, 0.3) !important;
    border-radius: 18px !important;
    box-shadow: 0 25px 60px rgba(0,0,0,0.6) !important;
    width: 100% !important;
    max-width: 420px !important;
    padding: 2.5rem !important;
    margin: 0 auto !important;
}

/* ── Brand name ──────────────────────────────────────────── */
.fi-logo {
    color: #c4b5fd !important;
    font-size: 1.4rem !important;
    font-weight: 800 !important;
    text-align: center !important;
    display: block !important;
}

/* ── Heading ─────────────────────────────────────────────── */
.fi-simple-header {
    text-align: center !important;
    margin-bottom: 1.5rem !important;
}
.fi-simple-header-heading {
    color: #e2e8f0 !important;
    font-size: 1.2rem !important;
    font-weight: 700 !important;
}
.fi-simple-header-subheading {
    color: rgba(196,181,253,0.6) !important;
    font-size: 0.85rem !important;
}

/* ── Labels ──────────────────────────────────────────────── */
label,
.fi-fo-field-wrp-label label,
.fi-label {
    color: #c4b5fd !important;
    font-size: 0.85rem !important;
    font-weight: 500 !important;
}

/* ── Inputs ──────────────────────────────────────────────── */
.fi-input {
    background: rgba(255,255,255,0.06) !important;
    border: 1px solid rgba(139,92,246,0.3) !important;
    color: #f1f5f9 !important;
    border-radius: 8px !important;
    width: 100% !important;
}
.fi-input:focus {
    border-color: #8b5cf6 !important;
    box-shadow: 0 0 0 3px rgba(139,92,246,0.15) !important;
    outline: none !important;
}
.fi-input::placeholder {
    color: rgba(255,255,255,0.2) !important;
}

/* ── Input wrapper full width ────────────────────────────── */
.fi-input-wrp,
.fi-fo-field-wrp,
.fi-fo-component-ctn {
    width: 100% !important;
}

/* ── Sign in button ──────────────────────────────────────── */
.fi-btn {
    background: linear-gradient(135deg, #7c3aed, #4f46e5) !important;
    color: white !important;
    border: none !important;
    border-radius: 10px !important;
    font-weight: 600 !important;
    font-size: 0.95rem !important;
    padding: 0.7rem 1.5rem !important;
    width: 100% !important;
    display: block !important;
    text-align: center !important;
    box-shadow: 0 4px 15px rgba(124,58,237,0.4) !important;
    transition: all 0.2s !important;
    cursor: pointer !important;
}
.fi-btn:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 20px rgba(124,58,237,0.55) !important;
}

/* ── Links ───────────────────────────────────────────────── */
a {
    color: #a78bfa !important;
}

/* ── Remember me checkbox ────────────────────────────────── */
.fi-checkbox-label {
    color: rgba(196,181,253,0.7) !important;
    font-size: 0.82rem !important;
}

/* ── Form full width fix ─────────────────────────────────── */
form, .fi-sc, .fi-sc > * {
    width: 100% !important;
}
CSS;

file_put_contents('public/css/login-custom.css', $loginCss);
echo "Login CSS fixed\n";
echo "Hard refresh browser with Ctrl+Shift+R\n";
