<x-filament-panels::page class="!p-0 !overflow-hidden">
<style>
/* ── Layout fix ─────────────────────────────────────────── */
[x-data*="flowforge"] {
    display: flex !important;
    flex-direction: column !important;
    height: calc(100vh - 9rem) !important;
}
[x-data*="flowforge"] > div:nth-child(2) {
    flex: 1 !important;
    overflow: hidden !important;
}
[x-data*="flowforge"] > div:nth-child(2) > div {
    display: flex !important;
    flex-direction: row !important;
    gap: 14px !important;
    height: 100% !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    align-items: flex-start !important;
    padding: 4px 4px 16px 4px !important;
}

/* ── Column ─────────────────────────────────────────────── */
.flowforge-column {
    min-width: 275px !important;
    max-width: 275px !important;
    flex-shrink: 0 !important;
    border-radius: 14px !important;
    border: 1px solid rgba(139,92,246,0.2) !important;
    background: linear-gradient(160deg, #1e1b4b 0%, #1e293b 100%) !important;
    overflow: hidden !important;
}
.flowforge-column-header {
    background: rgba(139,92,246,0.12) !important;
    border-bottom: 1px solid rgba(139,92,246,0.2) !important;
    padding: 12px 14px !important;
}
.flowforge-column-header h3 {
    color: #c4b5fd !important;
    font-weight: 700 !important;
    font-size: 12px !important;
    text-transform: uppercase !important;
    letter-spacing: 0.07em !important;
}
.flowforge-column-content {
    max-height: calc(100vh - 15rem) !important;
    overflow-y: auto !important;
    padding: 10px !important;
    scrollbar-width: thin !important;
    scrollbar-color: rgba(139,92,246,0.3) transparent !important;
}

/* ── Cards ──────────────────────────────────────────────── */
.flowforge-card {
    background: linear-gradient(135deg, #2d2a6e 0%, #1e2d5a 100%) !important;
    border: 1px solid rgba(139,92,246,0.25) !important;
    border-radius: 12px !important;
    margin-bottom: 10px !important;
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease !important;
    overflow: hidden !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
}
.flowforge-card:hover {
    transform: translateY(-3px) !important;
    box-shadow: 0 8px 28px rgba(99,102,241,0.35) !important;
    border-color: rgba(139,92,246,0.6) !important;
}
.flowforge-card .flowforge-card-content h4 {
    color: #e2e8f0 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    line-height: 1.45 !important;
    padding: 12px 12px 4px !important;
}
/* Schema text inside cards */
.flowforge-card .fi-in-text {
    color: rgba(196,181,253,0.7) !important;
    font-size: 11px !important;
}
.flowforge-card .fi-icon {
    color: rgba(139,92,246,0.6) !important;
}

/* ── Empty column ────────────────────────────────────────── */
.flowforge-column-content .border-dashed {
    border-color: rgba(139,92,246,0.2) !important;
    border-radius: 10px !important;
    background: rgba(139,92,246,0.04) !important;
}
.flowforge-column-content .border-dashed p {
    color: rgba(196,181,253,0.4) !important;
    font-size: 12px !important;
}
.flowforge-column-content .border-dashed svg {
    color: rgba(139,92,246,0.3) !important;
}

/* ── Badges ─────────────────────────────────────────────── */
.fi-badge {
    border-radius: 20px !important;
}

/* ── Search bar ─────────────────────────────────────────── */
.fi-ta-search-field .fi-input {
    background: rgba(139,92,246,0.08) !important;
    border-color: rgba(139,92,246,0.2) !important;
    color: #e2e8f0 !important;
    border-radius: 8px !important;
}

/* ── Scrollbar ──────────────────────────────────────────── */
::-webkit-scrollbar { height: 5px; width: 5px; }
::-webkit-scrollbar-track { background: rgba(139,92,246,0.05); border-radius: 3px; }
::-webkit-scrollbar-thumb { background: rgba(139,92,246,0.3); border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: rgba(139,92,246,0.5); }

/* ── Add button ─────────────────────────────────────────── */
.fi-ac-icon-btn-action {
    color: #a78bfa !important;
}
.fi-ac-icon-btn-action:hover {
    color: #c4b5fd !important;
}
</style>
    <div class="h-[calc(100vh-8rem)] overflow-hidden">
        {{ $this->board }}
    </div>
</x-filament-panels::page>