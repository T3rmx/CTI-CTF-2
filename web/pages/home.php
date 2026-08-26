<?php require BASE_PATH . '/templates/layout.php'; ?>
<?php
$content = ob_start();
?>
<div class="hero">
    <h1>T3rmx Network Management Portal</h1>
    <p>Enterprise-grade network infrastructure monitoring and management</p>
    <?php if (!isLoggedIn()): ?>
    <div class="hero-actions">
        <a href="/login" class="btn btn-primary btn-lg">Access Portal</a>
        <a href="/about" class="btn btn-lg">Learn More</a>
    </div>
    <?php endif; ?>
</div>

<div class="features">
    <div class="feature-card">
        <div class="feature-icon">&#128225;</div>
        <h3>Network Monitoring</h3>
        <p>Real-time monitoring of your network infrastructure with intelligent alerting and automated response.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">&#128736;</div>
        <h3>Device Management</h3>
        <p>Centralized management for all your network devices including routers, switches, and firewalls.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">&#128274;</div>
        <h3>Security Services</h3>
        <p>Comprehensive security monitoring and incident response capabilities for enterprise environments.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">&#128202;</div>
        <h3>Analytics & Reports</h3>
        <p>Detailed analytics and customizable reports for network performance and security posture.</p>
    </div>
</div>

<div class="stats-bar">
    <div class="stat">
        <span class="stat-number">200+</span>
        <span class="stat-label">Enterprise Clients</span>
    </div>
    <div class="stat">
        <span class="stat-number">99.9%</span>
        <span class="stat-label">Uptime SLA</span>
    </div>
    <div class="stat">
        <span class="stat-number">24/7</span>
        <span class="stat-label">Support</span>
    </div>
    <div class="stat">
        <span class="stat-number">50K+</span>
        <span class="stat-label">Devices Managed</span>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('Home', $content, false);
?>
