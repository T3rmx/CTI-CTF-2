<?php require BASE_PATH . '/templates/layout.php'; ?>
<?php $content = ob_start(); ?>
<div class="page-header">
    <h1>System Status</h1>
    <p>Current operational status of T3rmx services</p>
</div>

<div class="card">
    <div class="card-body">
        <div class="status-grid">
            <div class="status-item status-operational">
                <span class="status-indicator"></span>
                <span class="status-name">Portal Web Application</span>
                <span class="status-badge">Operational</span>
            </div>
            <div class="status-item status-operational">
                <span class="status-indicator"></span>
                <span class="status-name">API Gateway</span>
                <span class="status-badge">Operational</span>
            </div>
            <div class="status-item status-operational">
                <span class="status-indicator"></span>
                <span class="status-name">Database Services</span>
                <span class="status-badge">Operational</span>
            </div>
            <div class="status-item status-operational">
                <span class="status-indicator"></span>
                <span class="status-name">File Storage</span>
                <span class="status-badge">Operational</span>
            </div>
            <div class="status-item status-degraded">
                <span class="status-indicator"></span>
                <span class="status-name">Email Notifications</span>
                <span class="status-badge">Degraded</span>
            </div>
            <div class="status-item status-operational">
                <span class="status-indicator"></span>
                <span class="status-name">Monitoring Systems</span>
                <span class="status-badge">Operational</span>
            </div>
        </div>

        <h3>Recent Incidents</h3>
        <div class="incident-list">
            <div class="incident">
                <strong>November 10, 2024</strong> - Email delivery delays affecting notification system. Resolved within 2 hours.
            </div>
            <div class="incident">
                <strong>October 22, 2024</strong> - Brief database connectivity issues during peak hours. Root cause: connection pool exhaustion. Fixed by increasing pool size.
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); renderLayout('System Status', $content, false); ?>
