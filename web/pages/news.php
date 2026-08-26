<?php require BASE_PATH . '/templates/layout.php'; ?>
<?php $content = ob_start(); ?>
<div class="page-header">
    <h1>News & Updates</h1>
    <p>Latest from T3rmx</p>
</div>

<div class="card">
    <div class="card-body">
        <article class="news-item">
            <h3>Portal Version 4.2 Released</h3>
            <small class="text-muted">November 12, 2024</small>
            <p>We're pleased to announce the release of T3rmx Portal v4.2, featuring improved dashboard performance, enhanced reporting capabilities, and several security patches. All clients are encouraged to update their deployments.</p>
        </article>

        <article class="news-item">
            <h3>Scheduled Maintenance Window</h3>
            <small class="text-muted">November 5, 2024</small>
            <p>Routine maintenance is scheduled for November 15th, 2:00 AM - 4:00 AM EST. During this window, the portal may experience brief interruptions. Our team will be monitoring all systems throughout the maintenance period.</p>
        </article>

        <article class="news-item">
            <h3>New Support Portal Features</h3>
            <small class="text-muted">October 28, 2024</small>
            <p>We've added new features to the support system including priority ticket escalation, automated status updates, and improved ticket routing. These changes are live for all customers on the Enterprise plan.</p>
        </article>

        <article class="news-item">
            <h3>Q3 Security Report Published</h3>
            <small class="text-muted">October 15, 2024</small>
            <p>Our quarterly security report is now available in the Documents section. The report covers threat trends, incident response metrics, and recommended security configurations for the upcoming quarter.</p>
        </article>
    </div>
</div>
<?php $content = ob_get_clean(); renderLayout('News', $content, false); ?>
