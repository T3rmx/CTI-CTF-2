<?php require BASE_PATH . '/templates/layout.php'; ?>
<?php $content = ob_start(); ?>
<div class="page-header">
    <h1>About T3rmx</h1>
    <p>Building the future of network infrastructure management</p>
</div>

<div class="card">
    <div class="card-body">
        <h2>Our Story</h2>
        <p>Founded in 2018, T3rmx has grown from a small networking consultancy to a leading provider of enterprise network management solutions. Our platform serves over 200 clients across North America, managing more than 50,000 network devices.</p>

        <h3>Our Mission</h3>
        <p>To provide reliable, secure, and efficient network infrastructure management tools that enable organizations to focus on their core business while we handle the complexity of their network operations.</p>

        <h3>Core Services</h3>
        <ul>
            <li><strong>Network Monitoring</strong> - Real-time visibility into network health and performance</li>
            <li><strong>Device Management</strong> - Centralized configuration and lifecycle management</li>
            <li><strong>Security Services</strong> - Threat detection, incident response, and compliance monitoring</li>
            <li><strong>Cloud Management</strong> - Hybrid cloud infrastructure orchestration</li>
            <li><strong>Technical Support</strong> - 24/7 expert support for critical network issues</li>
        </ul>

        <h3>Leadership Team</h3>
        <div class="team-grid">
            <div class="team-member">
                <strong>Alex Chen</strong>
                <span>CEO & Co-founder</span>
            </div>
            <div class="team-member">
                <strong>Sarah Kim</strong>
                <span>CTO</span>
            </div>
            <div class="team-member">
                <strong>Michael Torres</strong>
                <span>VP of Engineering</span>
            </div>
            <div class="team-member">
                <strong>Lisa Park</strong>
                <span>Head of Security</span>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); renderLayout('About', $content, false); ?>
