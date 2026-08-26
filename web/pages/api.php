<?php require BASE_PATH . '/templates/layout.php'; ?>
<?php $content = ob_start(); ?>
<div class="page-header">
    <h1>API Documentation</h1>
    <p>T3rmx Portal API Reference</p>
</div>

<div class="card">
    <div class="card-header">
        <h3>REST API v2</h3>
    </div>
    <div class="card-body">
        <p class="text-muted">API documentation for integrating with the T3rmx Portal. Authentication required for all endpoints.</p>

        <div class="api-endpoints">
            <div class="api-endpoint">
                <span class="method method-get">GET</span>
                <code>/api/v2/devices</code>
                <span>List all managed devices</span>
            </div>
            <div class="api-endpoint">
                <span class="method method-get">GET</span>
                <code>/api/v2/devices/{id}</code>
                <span>Get device details</span>
            </div>
            <div class="api-endpoint">
                <span class="method method-post">POST</span>
                <code>/api/v2/alerts</code>
                <span>Create alert rule</span>
            </div>
            <div class="api-endpoint">
                <span class="method method-get">GET</span>
                <code>/api/v2/reports</code>
                <span>List available reports</span>
            </div>
            <div class="api-endpoint">
                <span class="method method-get">GET</span>
                <code>/api/v2/network/status</code>
                <span>Network health status</span>
            </div>
        </div>

        <p class="text-muted" style="margin-top: 1rem;"><small>API access requires valid authentication token. Contact your administrator for API key provisioning.</small></p>
    </div>
</div>
<?php $content = ob_get_clean(); renderLayout('API', $content, false); ?>
