@extends('backEnd.layouts.master')
@section('title', 'Git & Live Server Synchronization')

@section('css')
<style>
    /* Dark DevOps Theme Palette */
    :root {
        --devops-bg: #0b1120;
        --devops-card-bg: #0f172a;
        --devops-card-hover: #131d36;
        --devops-inner-bg: #090d16;
        --devops-border: #1e293b;
        --devops-border-light: #334155;
        --devops-text-main: #f8fafc;
        --devops-text-muted: #94a3b8;
        --devops-primary: #3b82f6;
        --devops-emerald: #10b981;
        --devops-emerald-glow: rgba(16, 185, 129, 0.25);
        --devops-purple: #8b5cf6;
        --devops-purple-glow: rgba(139, 92, 246, 0.25);
        --devops-amber: #f59e0b;
        --devops-rose: #f43f5e;
        --devops-cyan: #06b6d4;
    }

    .devops-sync-container {
        background-color: var(--devops-bg);
        color: var(--devops-text-main);
        border-radius: 1.25rem;
        padding: 1.75rem;
        margin-bottom: 2rem;
        border: 1px solid var(--devops-border);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Top Strip Status Pills */
    .devops-top-strip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--devops-border);
        margin-bottom: 1.5rem;
    }

    .devops-pill-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(30, 41, 59, 0.7);
        border: 1px solid var(--devops-border);
        color: var(--devops-text-muted);
    }

    .devops-pill-badge.active-node {
        color: #38bdf8;
        border-color: rgba(56, 189, 248, 0.3);
        background: rgba(14, 165, 233, 0.1);
    }

    .devops-pill-badge.node-user {
        color: #cbd5e1;
        background: rgba(30, 41, 59, 0.9);
    }

    .pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: var(--devops-emerald);
        box-shadow: 0 0 10px var(--devops-emerald);
        animation: pulseAnimation 2s infinite;
    }

    @keyframes pulseAnimation {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.85); }
    }

    /* Header Section */
    .devops-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .devops-title-group {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .devops-title-icon {
        width: 48px;
        height: 48px;
        border-radius: 0.875rem;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(249, 115, 22, 0.1));
        border: 1px solid rgba(245, 158, 11, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #f59e0b;
        box-shadow: 0 0 20px rgba(245, 158, 11, 0.15);
    }

    .devops-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #ffffff;
        margin: 0;
        letter-spacing: -0.02em;
    }

    .devops-subtitle {
        font-size: 0.85rem;
        color: var(--devops-text-muted);
        margin: 0.25rem 0 0 0;
    }

    .devops-btn-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-devops {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1.15rem;
        border-radius: 0.625rem;
        font-size: 0.825rem;
        font-weight: 600;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border: 1px solid transparent;
        text-decoration: none;
    }

    .btn-devops-dark {
        background: #1e293b;
        color: #e2e8f0;
        border-color: #334155;
    }
    .btn-devops-dark:hover {
        background: #334155;
        color: #ffffff;
    }

    .btn-devops-emerald {
        background: #10b981;
        color: #ffffff;
        box-shadow: 0 4px 14px var(--devops-emerald-glow);
    }
    .btn-devops-emerald:hover {
        background: #059669;
        box-shadow: 0 6px 20px var(--devops-emerald-glow);
        color: #ffffff;
    }

    .btn-devops-amber {
        background: #f97316;
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(249, 115, 22, 0.25);
    }
    .btn-devops-amber:hover {
        background: #ea580c;
        color: #ffffff;
    }

    /* Metric Status Cards Grid */
    .devops-metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .metric-card {
        background: var(--devops-card-bg);
        border: 1px solid var(--devops-border);
        border-radius: 0.875rem;
        padding: 1.25rem;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    .metric-card:hover {
        border-color: var(--devops-border-light);
        background: var(--devops-card-hover);
    }

    .metric-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.65rem;
    }

    .metric-label {
        font-size: 0.725rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--devops-text-muted);
    }

    .badge-pill-cyan {
        background: rgba(6, 182, 212, 0.15);
        color: #38bdf8;
        border: 1px solid rgba(6, 182, 212, 0.3);
        border-radius: 9999px;
        padding: 0.15rem 0.6rem;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .badge-pill-purple {
        background: rgba(139, 92, 246, 0.15);
        color: #c084fc;
        border: 1px solid rgba(139, 92, 246, 0.3);
        border-radius: 9999px;
        padding: 0.15rem 0.6rem;
        font-size: 0.7rem;
        font-weight: 600;
        font-family: 'JetBrains Mono', monospace;
    }

    .badge-pill-amber {
        background: rgba(245, 158, 11, 0.15);
        color: #fbbf24;
        border: 1px solid rgba(245, 158, 11, 0.3);
        border-radius: 9999px;
        padding: 0.15rem 0.6rem;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .badge-pill-blue {
        background: rgba(59, 130, 246, 0.15);
        color: #60a5fa;
        border: 1px solid rgba(59, 130, 246, 0.3);
        border-radius: 9999px;
        padding: 0.15rem 0.6rem;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .metric-value {
        font-size: 1.15rem;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 0.35rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .metric-sub {
        font-size: 0.75rem;
        color: var(--devops-text-muted);
        display: flex;
        align-items: center;
        gap: 0.4rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .text-emerald { color: #10b981 !important; }
    .text-amber { color: #f59e0b !important; }
    .text-rose { color: #f43f5e !important; }

    /* Middle Row Panels */
    .devops-panels-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .devops-panel {
        background: var(--devops-card-bg);
        border: 1px solid var(--devops-border);
        border-radius: 0.875rem;
        padding: 1.5rem;
    }

    .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .panel-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.95rem;
        font-weight: 700;
        color: #ffffff;
        margin: 0;
    }

    .panel-header-link {
        font-size: 0.75rem;
        color: #f59e0b;
        text-decoration: none;
        font-weight: 600;
    }
    .panel-header-link:hover {
        color: #fbbf24;
        text-decoration: underline;
    }

    .panel-desc {
        font-size: 0.8rem;
        color: var(--devops-text-muted);
        line-height: 1.45;
        margin-bottom: 1rem;
    }

    .terminal-code-box {
        background: var(--devops-inner-bg);
        border: 1px solid var(--devops-border);
        border-radius: 0.625rem;
        padding: 0.85rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }

    .terminal-code-text {
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
        font-size: 0.75rem;
        color: #38bdf8;
        word-break: break-all;
        line-height: 1.4;
        max-height: 60px;
        overflow-y: auto;
    }

    .btn-copy-code {
        background: #1e293b;
        color: #e2e8f0;
        border: 1px solid #334155;
        border-radius: 0.5rem;
        padding: 0.4rem 0.85rem;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        white-space: nowrap;
        transition: all 0.2s ease;
    }
    .btn-copy-code:hover {
        background: #334155;
        color: #ffffff;
    }

    .panel-footer-note {
        font-size: 0.75rem;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Workflow Steps List */
    .workflow-steps-list {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        margin-bottom: 1rem;
    }

    .workflow-step-item {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        font-size: 0.8rem;
        color: #e2e8f0;
        background: rgba(30, 41, 59, 0.4);
        padding: 0.65rem 0.85rem;
        border-radius: 0.5rem;
        border: 1px solid rgba(51, 65, 85, 0.5);
    }

    .workflow-step-item i {
        color: var(--devops-emerald);
        font-size: 0.95rem;
        margin-top: 0.1rem;
    }

    /* Automation & Hooks Card */
    .automation-card {
        background: var(--devops-card-bg);
        border: 1px solid var(--devops-border);
        border-radius: 0.875rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .automation-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 1rem;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 1.25rem;
    }

    .hooks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .hook-checkbox-box {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        background: var(--devops-inner-bg);
        border: 1px solid var(--devops-border);
        border-radius: 0.625rem;
        padding: 0.85rem 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .hook-checkbox-box:hover {
        border-color: #334155;
        background: #0f172a;
    }

    .hook-checkbox-box input[type="checkbox"] {
        margin-top: 0.25rem;
        width: 17px;
        height: 17px;
        accent-color: #3b82f6;
        cursor: pointer;
    }

    .hook-info h5 {
        font-size: 0.85rem;
        font-weight: 700;
        color: #ffffff;
        margin: 0 0 0.2rem 0;
    }

    .hook-info p {
        font-size: 0.75rem;
        color: var(--devops-text-muted);
        margin: 0;
        font-family: 'JetBrains Mono', monospace;
    }

    /* Tables Style (Recent Commits & Activity Logs) */
    .devops-table-card {
        background: var(--devops-card-bg);
        border: 1px solid var(--devops-border);
        border-radius: 0.875rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .table-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .table-title {
        font-size: 1rem;
        font-weight: 700;
        color: #ffffff;
        margin: 0;
    }

    .table-subtitle {
        font-size: 0.75rem;
        color: var(--devops-text-muted);
        margin: 0.2rem 0 0 0;
    }

    .table-count-badge {
        font-size: 0.725rem;
        color: #94a3b8;
        background: #1e293b;
        padding: 0.25rem 0.65rem;
        border-radius: 9999px;
        border: 1px solid #334155;
    }

    .devops-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.825rem;
    }

    .devops-table th {
        background: rgba(15, 23, 42, 0.8);
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--devops-border);
        text-align: left;
    }

    .devops-table td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid rgba(30, 41, 59, 0.6);
        color: #e2e8f0;
        vertical-align: middle;
    }

    .devops-table tbody tr:hover td {
        background: rgba(30, 41, 59, 0.35);
    }

    .commit-hash-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.75rem;
        color: #38bdf8;
        background: rgba(56, 189, 248, 0.1);
        border: 1px solid rgba(56, 189, 248, 0.25);
        padding: 0.15rem 0.5rem;
        border-radius: 0.375rem;
    }

    .head-badge {
        background: #10b981;
        color: #ffffff;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.1rem 0.45rem;
        border-radius: 9999px;
        text-transform: uppercase;
    }

    .status-badge-success {
        background: rgba(16, 185, 129, 0.15);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.3);
        padding: 0.2rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.725rem;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    .status-badge-failed {
        background: rgba(244, 63, 94, 0.15);
        color: #fb7185;
        border: 1px solid rgba(244, 63, 94, 0.3);
        padding: 0.2rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.725rem;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    .status-badge-running {
        background: rgba(6, 182, 212, 0.15);
        color: #38bdf8;
        border: 1px solid rgba(6, 182, 212, 0.3);
        padding: 0.2rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.725rem;
        font-weight: 700;
    }

    .btn-view-output {
        background: #1e293b;
        color: #cbd5e1;
        border: 1px solid #334155;
        border-radius: 0.375rem;
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.2s ease;
    }
    .btn-view-output:hover {
        background: #334155;
        color: #ffffff;
    }

    /* Terminal Console Modal Style */
    .dark-modal-content {
        background: #090d16;
        border: 1px solid var(--devops-border);
        border-radius: 1rem;
        color: #e2e8f0;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
    }

    .terminal-titlebar {
        background: #0f172a;
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid var(--devops-border);
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .terminal-dots {
        display: flex;
        gap: 6px;
    }
    .dot-red { width: 12px; height: 12px; border-radius: 50%; background: #ef4444; }
    .dot-yellow { width: 12px; height: 12px; border-radius: 50%; background: #f59e0b; }
    .dot-green { width: 12px; height: 12px; border-radius: 50%; background: #10b981; }

    .terminal-body {
        background: #050811;
        padding: 1.25rem;
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
        font-size: 0.8rem;
        line-height: 1.6;
        max-height: 500px;
        overflow-y: auto;
        color: #38bdf8;
    }

    /* Live Pipeline Steps Cards */
    .pipeline-step-item {
        background: #0f172a;
        border: 1px solid #1e293b;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s ease;
    }
    .pipeline-step-item.active {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, 0.08);
    }
    .pipeline-step-item.success {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.08);
    }
    .pipeline-step-item.failed {
        border-color: #f43f5e;
        background: rgba(244, 63, 94, 0.08);
    }

    /* Form Inputs in Dark Theme */
    .devops-input {
        background: #090d16;
        border: 1px solid #334155;
        border-radius: 0.5rem;
        color: #ffffff;
        padding: 0.55rem 0.85rem;
        font-size: 0.85rem;
        width: 100%;
    }
    .devops-input:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-3">
    <div class="devops-sync-container">

        <!-- 1. Top Strip System Pills -->
        <div class="devops-top-strip">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="devops-pill-badge active-node">
                    <span class="pulse-dot"></span>
                    Node: {{ $systemMetrics['node'] ?? 'mondolshopbd.com' }}
                </span>
                <span class="devops-pill-badge">
                    <i class="fe-phone-call text-cyan"></i>
                    Softphone
                </span>
                <span class="devops-pill-badge">
                    {{ $systemMetrics['php_version'] }} • {{ $systemMetrics['database'] }} • {{ $systemMetrics['redis'] }}
                </span>
            </div>
            <div>
                <span class="devops-pill-badge node-user">
                    <i class="fe-user"></i>
                    {{ $systemMetrics['user_name'] }}
                    <span class="text-muted">({{ $systemMetrics['user_email'] }})</span>
                </span>
            </div>
        </div>

        <!-- 2. Page Header -->
        <div class="devops-header">
            <div class="devops-title-group">
                <div class="devops-title-icon">
                    <i class="fe-git-branch"></i>
                </div>
                <div>
                    <h1 class="devops-title">Git & Live Server Synchronization</h1>
                    <p class="devops-subtitle">Git repository synchronization, automated post-pull hooks, and Ed25519 deploy key management.</p>
                </div>
            </div>
            <div class="devops-btn-group">
                <button type="button" class="btn-devops btn-devops-dark" id="btnCheckUpdates">
                    <i class="fe-refresh-cw" id="checkUpdatesIcon"></i>
                    <span>Check Updates</span>
                </button>
                <button type="button" class="btn-devops btn-devops-emerald" id="btnTriggerDeploy">
                    <i class="fe-play"></i>
                    <span>Deploy Now</span>
                </button>
                <button type="button" class="btn-devops btn-devops-amber" data-bs-toggle="modal" data-bs-target="#repoSettingsModal">
                    <i class="fe-link"></i>
                    <span>Connect Git Repository</span>
                </button>
            </div>
        </div>

        <!-- 3. Top Metrics Cards Grid (4 Columns) -->
        <div class="devops-metrics-grid">
            <!-- Card 1: ACTIVE BRANCH -->
            <div class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">Active Branch</span>
                    <span class="badge-pill-cyan">{{ $info['current_branch'] }}</span>
                </div>
                <div class="metric-value">{{ basename($info['remote_url'], '.git') ?: 'mondolshopbd' }}.git</div>
                <div class="metric-sub" title="{{ $info['remote_url'] }}">
                    <i class="fe-git-commit text-cyan"></i>
                    <span>{{ Str::limit($info['remote_url'], 32) }}</span>
                </div>
            </div>

            <!-- Card 2: CURRENT COMMIT -->
            <div class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">Current Commit</span>
                    <span class="badge-pill-purple">{{ $info['short_commit'] }}</span>
                </div>
                <div class="metric-value" title="{{ $info['commit_message'] }}">
                    {{ Str::limit($info['commit_message'], 32) }}
                </div>
                <div class="metric-sub">
                    <span>maccpro</span>
                    <span>•</span>
                    <span>{{ $info['last_deployed_at'] }}</span>
                </div>
            </div>

            <!-- Card 3: REMOTE STATUS -->
            <div class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">Remote Status</span>
                    <span class="badge-pill-amber" id="remoteBehindBadge">{{ $remoteStatus['behind_count'] > 0 ? $remoteStatus['behind_count'] . ' BEHIND' : 'UP TO DATE' }}</span>
                </div>
                <div class="metric-value text-emerald" id="treeStatusText">{{ $remoteStatus['status_text'] }}</div>
                <div class="metric-sub" id="treeDetailText">{{ $remoteStatus['detail_text'] }}</div>
            </div>

            <!-- Card 4: DEPLOYMENT MODE -->
            <div class="metric-card">
                <div class="metric-top">
                    <span class="metric-label">Deployment Mode</span>
                    <span class="badge-pill-blue">MANUAL PULL</span>
                </div>
                <div class="metric-value">Manual Git Pull & Deploy</div>
                <div class="metric-sub">
                    <i class="fe-zap text-amber"></i>
                    <span>Pull on demand or via secure webhook</span>
                </div>
            </div>
        </div>

        <!-- 4. Middle Row: Deploy Key & Workflow Panels -->
        <div class="devops-panels-grid">
            <!-- Panel 1: Server SSH Deploy Key -->
            <div class="devops-panel">
                <div class="panel-header">
                    <h3 class="panel-title">
                        <span class="pulse-dot"></span>
                        Server SSH Deploy Key (Ed25519)
                    </h3>
                    <a href="https://github.com/maccpro/mondolshopbd/settings/keys" target="_blank" rel="noopener" class="panel-header-link">
                        GitHub Deploy Keys <i class="fe-external-link"></i>
                    </a>
                </div>
                <p class="panel-desc">Add this server public key to your Git provider repository settings under <strong>Deploy Keys</strong> with read access.</p>
                
                <div class="terminal-code-box">
                    <div class="terminal-code-text" id="deployKeyText">
                        {{ $info['settings']->ssh_public_key ?: 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIEXsS1J49QN+LmxW1HIA0yzWALfRfjAsBWHfEa2IJsUA mondolshop-deploy@mondolshopbd.com' }}
                    </div>
                    <button type="button" class="btn-copy-code" id="btnCopyKey" onclick="copyDeployKey()">
                        <i class="fe-copy" id="copyIcon"></i>
                        <span id="copyBtnText">Copy Key</span>
                    </button>
                </div>

                <div class="panel-footer-note">
                    <span class="text-emerald">
                        <i class="fe-check-circle"></i> Ed25519 deploy key verified on server.
                    </span>
                    <button type="button" class="btn btn-link text-muted p-0 font-12" onclick="generateNewKey()">
                        Regenerate
                    </button>
                </div>
            </div>

            <!-- Panel 2: Manual Pull & Release Workflow -->
            <div class="devops-panel">
                <div class="panel-header">
                    <h3 class="panel-title">
                        <i class="fe-info text-cyan"></i>
                        Manual Pull & Release Workflow
                    </h3>
                </div>
                <p class="panel-desc">Continuous Deployment and live webhooks are enabled. All production releases are triggered manually on demand after tests pass.</p>

                <div class="workflow-steps-list">
                    <div class="workflow-step-item">
                        <i class="fe-check"></i>
                        <div><strong>Step 1:</strong> Push commits to GitHub (CI tests run automatically)</div>
                    </div>
                    <div class="workflow-step-item">
                        <i class="fe-check"></i>
                        <div><strong>Step 2:</strong> Confirm GitHub CI tests pass / green badge</div>
                    </div>
                    <div class="workflow-step-item">
                        <i class="fe-check"></i>
                        <div><strong>Step 3:</strong> Click <strong>"Deploy Now"</strong> above to pull & optimize live server</div>
                    </div>
                </div>

                <div class="panel-footer-note">
                    <span>Trigger Mode: Manual UI Click</span>
                    <span>Protected Branch: <code>{{ $info['current_branch'] }}</code></span>
                </div>
            </div>
        </div>

        <!-- 5. Automation & Post-Pull Execution Hooks Card -->
        <div class="automation-card">
            <div class="automation-header">
                <i class="fe-settings text-amber"></i>
                <span>Automation & Post-Pull Execution Hooks</span>
            </div>

            <form id="automationHooksForm">
                @csrf
                <input type="hidden" name="protocol" value="{{ $info['settings']->protocol }}">
                <input type="hidden" name="repository_url" value="{{ $info['settings']->repository_url }}">
                <input type="hidden" name="branch" value="{{ $info['settings']->branch }}">

                <div class="hooks-grid">
                    <!-- Hook 1: Migrations -->
                    <label class="hook-checkbox-box">
                        <input type="checkbox" name="auto_run_migrations" id="hook_migrations" value="1" {{ $info['settings']->auto_run_migrations ? 'checked' : '' }}>
                        <div class="hook-info">
                            <h5>Run Database Migrations</h5>
                            <p>Execute php artisan migrate --force</p>
                        </div>
                    </label>

                    <!-- Hook 2: Optimize -->
                    <label class="hook-checkbox-box">
                        <input type="checkbox" name="auto_run_optimize" id="hook_optimize" value="1" {{ $info['settings']->auto_run_optimize !== false ? 'checked' : '' }}>
                        <div class="hook-info">
                            <h5>Re-cache & Optimize</h5>
                            <p>Clear and re-cache config, routes, and views</p>
                        </div>
                    </label>

                    <!-- Hook 3: Composer -->
                    <label class="hook-checkbox-box">
                        <input type="checkbox" name="auto_run_composer" id="hook_composer" value="1" {{ $info['settings']->auto_run_composer ? 'checked' : '' }}>
                        <div class="hook-info">
                            <h5>Composer Install</h5>
                            <p>Install dependencies with optimize-autoloader</p>
                        </div>
                    </label>

                    <!-- Hook 4: Queue Restart -->
                    <label class="hook-checkbox-box">
                        <input type="checkbox" name="auto_run_queue_restart" id="hook_queue" value="1" {{ $info['settings']->auto_run_queue_restart !== false ? 'checked' : '' }}>
                        <div class="hook-info">
                            <h5>Restart Queue Workers</h5>
                            <p>Broadcast queue:restart signal to workers</p>
                        </div>
                    </label>

                    <!-- Hook 5: NPM Build Assets -->
                    <label class="hook-checkbox-box">
                        <input type="checkbox" name="auto_run_npm_build" id="hook_npm" value="1" {{ $info['settings']->auto_run_npm_build ? 'checked' : '' }}>
                        <div class="hook-info">
                            <h5>NPM Build Assets</h5>
                            <p>Execute npm run build for frontend</p>
                        </div>
                    </label>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn-devops btn-devops-dark" id="btnSaveHooks">
                        <i class="fe-save"></i>
                        <span>Save Automation Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- 6. Recent Git Commits (Server Head) Table -->
        <div class="devops-table-card">
            <div class="table-header-row">
                <div>
                    <h3 class="table-title">Recent Git Commits (Server Head)</h3>
                    <p class="table-subtitle">Browse commit history and inspect server head status.</p>
                </div>
                <div class="table-count-badge">
                    {{ count($recentCommits) }} commits listed
                </div>
            </div>

            <div class="table-responsive">
                <table class="devops-table">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Commit</th>
                            <th>Message</th>
                            <th style="width: 130px;">Author</th>
                            <th style="width: 130px;">Date</th>
                            <th style="width: 100px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentCommits as $commit)
                        <tr>
                            <td>
                                <span class="commit-hash-pill">{{ $commit['short_hash'] }}</span>
                                @if($commit['is_head'])
                                <span class="head-badge ms-1">Current Head</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-white">
                                {{ $commit['message'] }}
                            </td>
                            <td class="text-muted">{{ $commit['author'] }}</td>
                            <td class="text-muted">{{ $commit['date'] }}</td>
                            <td style="text-align: right;">
                                @if($commit['is_head'])
                                <span class="badge bg-success-subtle text-success font-11">Active</span>
                                @else
                                <span class="text-muted font-11">Committed</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No git commits found or git history not accessible.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 7. Live Deployment Activity Logs Table -->
        <div class="devops-table-card">
            <div class="table-header-row">
                <div>
                    <h3 class="table-title">Live Deployment Activity Logs</h3>
                    <p class="table-subtitle">History of automated webhook and manual release runs with complete console output.</p>
                </div>
                <div class="table-count-badge">
                    {{ count($deploymentLogs) }} logs recorded
                </div>
            </div>

            <div class="table-responsive">
                <table class="devops-table">
                    <thead>
                        <tr>
                            <th style="width: 110px;">Status</th>
                            <th>Commit</th>
                            <th style="width: 140px;">Trigger</th>
                            <th style="width: 100px;">Duration</th>
                            <th style="width: 130px;">Time</th>
                            <th style="width: 130px; text-align: right;">Terminal Log</th>
                        </tr>
                    </thead>
                    <tbody id="deploymentLogsTbody">
                        @forelse($deploymentLogs as $log)
                        <tr>
                            <td>
                                @if($log->status === 'success')
                                <span class="status-badge-success">SUCCESS</span>
                                @elseif($log->status === 'failed')
                                <span class="status-badge-failed">FAILED</span>
                                @else
                                <span class="status-badge-running">RUNNING</span>
                                @endif
                            </td>
                            <td>
                                <span class="commit-hash-pill">{{ $log->short_commit }}</span>
                                <span class="ms-1 text-muted">{{ Str::limit($log->commit_message ?: 'Release execution', 45) }}</span>
                            </td>
                            <td>
                                <span class="text-muted font-12">
                                    {{ $log->trigger_type === 'webhook' ? 'Webhook' : 'Manual UI Click' }}
                                </span>
                            </td>
                            <td>{{ $log->duration_seconds ?? 0 }}s</td>
                            <td class="text-muted">{{ $log->created_at ? $log->created_at->diffForHumans() : 'Just now' }}</td>
                            <td style="text-align: right;">
                                <button type="button" class="btn-view-output" onclick="viewLogModal({{ $log->id }})">
                                    <i class="fe-terminal"></i>
                                    <span>View Output</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No deployment logs recorded yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- ==========================================
     MODALS SECTION
     ========================================== -->

<!-- Modal 1: Live Deployment Streaming Modal -->
<div class="modal fade" id="liveDeployModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content dark-modal-content">
            <div class="terminal-titlebar">
                <div class="d-flex align-items-center gap-2">
                    <div class="terminal-dots">
                        <span class="dot-red"></span>
                        <span class="dot-yellow"></span>
                        <span class="dot-green"></span>
                    </div>
                    <span class="fw-bold font-13 ms-2 text-white">Live Release Pipeline Stream</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary" id="deployTimerBadge">00:00</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="deployModalCloseBtn" style="display: none;"></button>
                </div>
            </div>
            <div class="p-3">
                <!-- 5 Steps Indicators -->
                <div class="row g-2 mb-3">
                    <div class="col">
                        <div class="pipeline-step-item" id="pipelineStepCard1">
                            <div>
                                <small class="text-muted d-block">Step 01</small>
                                <span class="fw-bold font-12">Git Fetch & Pull</span>
                            </div>
                            <span id="stepIcon1"><i class="fe-clock text-muted"></i></span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="pipeline-step-item" id="pipelineStepCard2">
                            <div>
                                <small class="text-muted d-block">Step 02</small>
                                <span class="fw-bold font-12">Composer</span>
                            </div>
                            <span id="stepIcon2"><i class="fe-clock text-muted"></i></span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="pipeline-step-item" id="pipelineStepCard3">
                            <div>
                                <small class="text-muted d-block">Step 03</small>
                                <span class="fw-bold font-12">Migrations</span>
                            </div>
                            <span id="stepIcon3"><i class="fe-clock text-muted"></i></span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="pipeline-step-item" id="pipelineStepCard4">
                            <div>
                                <small class="text-muted d-block">Step 04</small>
                                <span class="fw-bold font-12">Optimize</span>
                            </div>
                            <span id="stepIcon4"><i class="fe-clock text-muted"></i></span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="pipeline-step-item" id="pipelineStepCard5">
                            <div>
                                <small class="text-muted d-block">Step 05</small>
                                <span class="fw-bold font-12">Queue Restart</span>
                            </div>
                            <span id="stepIcon5"><i class="fe-clock text-muted"></i></span>
                        </div>
                    </div>
                </div>

                <!-- Live Terminal Body -->
                <div class="terminal-body" id="liveTerminalLogs">
                    <span class="text-muted">Initializing deployment pipeline session...</span>
                </div>
            </div>
            <div class="p-3 border-top border-secondary d-flex justify-content-between align-items-center">
                <span class="text-muted font-12" id="deployStatusNote">Executing release pipeline. Please do not close this window.</span>
                <button type="button" class="btn-devops btn-devops-dark" id="btnDoneDeploy" data-bs-dismiss="modal" style="display: none;" onclick="location.reload()">
                    Done & Refresh Dashboard
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: View Terminal Log Modal -->
<div class="modal fade" id="viewLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content dark-modal-content">
            <div class="terminal-titlebar">
                <div class="d-flex align-items-center gap-2">
                    <div class="terminal-dots">
                        <span class="dot-red"></span>
                        <span class="dot-yellow"></span>
                        <span class="dot-green"></span>
                    </div>
                    <span class="fw-bold font-13 ms-2 text-white" id="modalLogTitle">Deployment Console Output</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="terminal-body" id="modalLogContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-info" role="status"></div>
                </div>
            </div>
            <div class="p-3 border-top border-secondary d-flex justify-content-between">
                <button type="button" class="btn-devops btn-devops-dark" onclick="copyModalLog()">
                    <i class="fe-copy"></i> Copy Output
                </button>
                <button type="button" class="btn-devops btn-devops-dark" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Git Repository Settings Modal -->
<div class="modal fade" id="repoSettingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content dark-modal-content">
            <div class="terminal-titlebar">
                <span class="fw-bold text-white font-14">Git Repository Connection Settings</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="repoSettingsForm">
                @csrf
                <div class="p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted font-12 fw-bold text-uppercase">Git Protocol</label>
                        <select name="protocol" id="setting_protocol" class="devops-input">
                            <option value="ssh" {{ $info['settings']->protocol === 'ssh' ? 'selected' : '' }}>SSH (git@github.com:...)</option>
                            <option value="https" {{ $info['settings']->protocol === 'https' ? 'selected' : '' }}>HTTPS (https://github.com/...)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted font-12 fw-bold text-uppercase">Repository URL</label>
                        <input type="text" name="repository_url" id="setting_repo_url" class="devops-input" value="{{ $info['settings']->repository_url }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted font-12 fw-bold text-uppercase">Branch</label>
                        <input type="text" name="branch" id="setting_branch" class="devops-input" value="{{ $info['settings']->branch ?: 'main' }}" required>
                    </div>

                    <div class="mb-3" id="httpsTokenGroup" style="{{ $info['settings']->protocol === 'https' ? '' : 'display: none;' }}">
                        <label class="form-label text-muted font-12 fw-bold text-uppercase">GitHub Personal Access Token (PAT)</label>
                        <input type="password" name="https_token" id="setting_token" class="devops-input" placeholder="ghp_...">
                    </div>

                    <div class="alert alert-dark border-secondary p-2 mb-0">
                        <small class="text-muted d-block">
                            <i class="fe-info text-cyan"></i> Saving will automatically initialize Git tracking and bind existing files safely on the server.
                        </small>
                    </div>
                </div>
                <div class="p-3 border-top border-secondary d-flex justify-content-between">
                    <button type="button" class="btn-devops btn-devops-dark" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-devops btn-devops-amber" id="btnSaveRepoSettings">
                        <i class="fe-save"></i> Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    // Copy SSH Deploy Key
    function copyDeployKey() {
        const keyText = document.getElementById('deployKeyText').innerText.trim();
        navigator.clipboard.writeText(keyText).then(() => {
            const btnText = document.getElementById('copyBtnText');
            const icon = document.getElementById('copyIcon');
            btnText.innerText = 'Copied!';
            icon.className = 'fe-check text-emerald';
            setTimeout(() => {
                btnText.innerText = 'Copy Key';
                icon.className = 'fe-copy';
            }, 2500);
        }).catch(err => {
            toastr.error('Failed to copy key: ' + err);
        });
    }

    // Copy Modal Log
    function copyModalLog() {
        const text = document.getElementById('modalLogContent').innerText;
        navigator.clipboard.writeText(text).then(() => {
            toastr.success('Terminal log copied to clipboard.');
        });
    }

    // Toggle HTTPS Token input
    document.getElementById('setting_protocol').addEventListener('change', function() {
        const group = document.getElementById('httpsTokenGroup');
        group.style.display = this.value === 'https' ? 'block' : 'none';
    });

    // Check Updates AJAX Button
    document.getElementById('btnCheckUpdates').addEventListener('click', function() {
        const icon = document.getElementById('checkUpdatesIcon');
        icon.classList.add('fe-spin');

        fetch("{{ route('admin.system.update.check-updates') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            icon.classList.remove('fe-spin');
            if (data.success && data.remote_status) {
                const rs = data.remote_status;
                const badge = document.getElementById('remoteBehindBadge');
                const title = document.getElementById('treeStatusText');
                const desc = document.getElementById('treeDetailText');

                if (rs.behind_count > 0) {
                    badge.className = 'badge-pill-amber';
                    badge.innerText = rs.behind_count + ' BEHIND';
                    toastr.info(`There are ${rs.behind_count} new commits available on GitHub!`, 'Updates Available');
                } else {
                    badge.className = 'badge bg-success-subtle text-success';
                    badge.innerText = 'UP TO DATE';
                    toastr.success('Repository is fully synchronized and up to date.', 'Up to Date');
                }
                title.innerText = rs.status_text;
                desc.innerText = rs.detail_text;
            } else {
                toastr.warning('Could not retrieve remote git status.');
            }
        })
        .catch(err => {
            icon.classList.remove('fe-spin');
            toastr.error('Error checking updates: ' + err.message);
        });
    });

    // Save Automation Hooks AJAX
    document.getElementById('automationHooksForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSaveHooks');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fe-loader fe-spin"></i> Saving...';

        const formData = new FormData(this);

        fetch("{{ route('admin.system.update.save') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            if (data.success) {
                toastr.success(data.message, 'Settings Saved');
            } else {
                toastr.error(data.message || 'Failed to save settings.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            toastr.error('An error occurred: ' + err.message);
        });
    });

    // Save Repository Settings AJAX
    document.getElementById('repoSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSaveRepoSettings');
        btn.disabled = true;
        btn.innerHTML = '<i class="fe-loader fe-spin"></i> Initializing...';

        const formData = new FormData(this);

        fetch("{{ route('admin.system.update.save') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fe-save"></i> Save Configuration';
            if (data.success) {
                toastr.success(data.message, 'Success');
                const modal = bootstrap.Modal.getInstance(document.getElementById('repoSettingsModal'));
                if (modal) modal.hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(data.message || 'Failed to initialize repository.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fe-save"></i> Save Configuration';
            toastr.error('An error occurred: ' + err.message);
        });
    });

    // View Past Deployment Log in Modal
    function viewLogModal(logId) {
        const modal = new bootstrap.Modal(document.getElementById('viewLogModal'));
        const body = document.getElementById('modalLogContent');
        body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-info" role="status"></div></div>';
        modal.show();

        fetch(`{{ url('admin/system/update/log') }}/${logId}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.log) {
                const log = data.log;
                document.getElementById('modalLogTitle').innerText = `Release Output [${log.short_commit || 'HEAD'}] • ${log.created_at ? new Date(log.created_at).toLocaleString() : ''}`;
                body.innerHTML = `<pre style="color: #38bdf8; margin: 0; font-family: inherit;">${log.log_output || 'No output captured for this run.'}</pre>`;
            } else {
                body.innerHTML = '<div class="text-danger p-3">Failed to load deployment log record.</div>';
            }
        })
        .catch(err => {
            body.innerHTML = `<div class="text-danger p-3">Error fetching log: ${err.message}</div>`;
        });
    }

    // Trigger Live Deployment SSE Stream
    document.getElementById('btnTriggerDeploy').addEventListener('click', function() {
        if (!confirm('Are you sure you want to execute system deployment now?')) {
            return;
        }

        const modalEl = document.getElementById('liveDeployModal');
        const modal = new bootstrap.Modal(modalEl);
        const terminal = document.getElementById('liveTerminalLogs');
        const timerBadge = document.getElementById('deployTimerBadge');
        const statusNote = document.getElementById('deployStatusNote');
        const doneBtn = document.getElementById('btnDoneDeploy');
        const closeBtn = document.getElementById('deployModalCloseBtn');

        terminal.innerHTML = '<div class="text-cyan">[INFO] Opening deployment stream...</div>';
        doneBtn.style.display = 'none';
        closeBtn.style.display = 'none';
        statusNote.innerText = 'Executing release pipeline. Please do not close this window.';
        statusNote.className = 'text-muted font-12';

        // Reset Step Cards
        for (let i = 1; i <= 5; i++) {
            const card = document.getElementById('pipelineStepCard' + i);
            const icon = document.getElementById('stepIcon' + i);
            card.className = 'pipeline-step-item';
            icon.innerHTML = '<i class="fe-clock text-muted"></i>';
        }

        modal.show();

        // Timer
        let seconds = 0;
        const timerInterval = setInterval(() => {
            seconds++;
            const m = String(Math.floor(seconds / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            timerBadge.innerText = `${m}:${s}`;
        }, 1000);

        // SSE EventSource
        const eventSource = new EventSource("{{ route('admin.system.update.stream') }}");

        eventSource.addEventListener('step_start', function(e) {
            const data = JSON.parse(e.data);
            const card = document.getElementById('pipelineStepCard' + data.step);
            const icon = document.getElementById('stepIcon' + data.step);
            if (card) {
                card.className = 'pipeline-step-item active';
                icon.innerHTML = '<i class="fe-loader fe-spin text-primary"></i>';
            }
        });

        eventSource.addEventListener('step_success', function(e) {
            const data = JSON.parse(e.data);
            const card = document.getElementById('pipelineStepCard' + data.step);
            const icon = document.getElementById('stepIcon' + data.step);
            if (card) {
                card.className = 'pipeline-step-item success';
                icon.innerHTML = '<i class="fe-check-circle text-emerald"></i>';
            }
        });

        eventSource.addEventListener('output', function(e) {
            const data = JSON.parse(e.data);
            const line = document.createElement('div');
            line.textContent = data.message;
            if (data.message.includes('[ERROR]') || data.message.includes('failed')) {
                line.style.color = '#f43f5e';
            } else if (data.message.includes('[SUCCESS]')) {
                line.style.color = '#10b981';
                line.style.fontWeight = 'bold';
            } else if (data.message.includes('===')) {
                line.style.color = '#fbbf24';
                line.style.fontWeight = 'bold';
            }
            terminal.appendChild(line);
            terminal.scrollTop = terminal.scrollHeight;
        });

        eventSource.addEventListener('pipeline_complete', function(e) {
            clearInterval(timerInterval);
            eventSource.close();
            const data = JSON.parse(e.data);
            statusNote.innerText = '✅ Deployment completed successfully in ' + data.duration_formatted;
            statusNote.className = 'text-emerald fw-bold font-12';
            doneBtn.style.display = 'inline-flex';
            closeBtn.style.display = 'block';
            toastr.success('System release deployed successfully!');
        });

        eventSource.addEventListener('pipeline_error', function(e) {
            clearInterval(timerInterval);
            eventSource.close();
            const data = JSON.parse(e.data);
            const card = document.getElementById('pipelineStepCard' + data.step);
            const icon = document.getElementById('stepIcon' + data.step);
            if (card) {
                card.className = 'pipeline-step-item failed';
                icon.innerHTML = '<i class="fe-x-circle text-rose"></i>';
            }
            statusNote.innerText = '❌ Error: ' + data.message;
            statusNote.className = 'text-rose fw-bold font-12';
            doneBtn.style.display = 'inline-flex';
            closeBtn.style.display = 'block';
            toastr.error('Deployment pipeline failed: ' + data.message);
        });

        eventSource.onerror = function() {
            clearInterval(timerInterval);
            eventSource.close();
            doneBtn.style.display = 'inline-flex';
            closeBtn.style.display = 'block';
        };
    });

    // Generate New Key
    function generateNewKey() {
        if (!confirm('Generate a new Ed25519 deploy key? You will need to re-add the new key to GitHub.')) {
            return;
        }

        fetch("{{ route('admin.system.update.generate-key') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status) {
                toastr.success(data.message);
                document.getElementById('deployKeyText').innerText = data.public_key;
            } else {
                toastr.error(data.message || 'Key generation failed.');
            }
        })
        .catch(err => {
            toastr.error('Error: ' + err.message);
        });
    }
</script>
@endsection
