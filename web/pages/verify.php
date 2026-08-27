<?php
require BASE_PATH . '/templates/layout.php';

$results = [];
$submitted = false;
$error = '';

function load_verification_data() {
    foreach (['/opt/t3rmx/verifier/flags.json', '/var/www/app/flags/flags.json'] as $path) {
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data) && isset($data['hmac_secret'])) {
                return $data;
            }
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = true;
    $flags = array_map('trim', array_filter([$_POST['flag1'] ?? '', $_POST['flag2'] ?? '', $_POST['flag3'] ?? ''], function ($f) {
        return trim($f) !== '';
    }));

    $data = load_verification_data();
    if (!$data) {
        $error = 'Verification data is unavailable.';
    } elseif (empty($flags)) {
        $error = 'Enter at least one flag to verify.';
    } else {
        $secret = $data['hmac_secret'];
        $types = ['developer', 'laour', 'root'];

        foreach ($flags as $flag) {
            $found = false;
            foreach ($types as $type) {
                $computed = hash_hmac('sha256', $flag, $secret);
                if (isset($data[$type]) && hash_equals($data[$type], $computed)) {
                    $results[] = ['flag' => $flag, 'valid' => true, 'type' => $type];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $results[] = ['flag' => $flag, 'valid' => false, 'type' => null];
            }
        }
    }
}

$content = ob_start();
?>
<div class="page-header">
    <h1>Flag Verification</h1>
    <p>Submit captured flags to validate them against the T3rmx proof-of-compromise system</p>
</div>

<?php if ($error): ?>
<div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($submitted): ?>
<div class="card">
    <div class="card-header">
        <h3>Results</h3>
    </div>
    <div class="card-body">
        <?php if (empty($results)): ?>
        <p class="text-muted">No flags were processed.</p>
        <?php else: ?>
        <?php
            $allValid = true;
            foreach ($results as $r) {
                if (!$r['valid']) { $allValid = false; break; }
            }
        ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Flag Type</th>
                    <th>Flag</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $r): ?>
                <tr>
                    <td>
                        <?php if ($r['valid']): ?>
                        <span style="color:var(--success, #2ecc71);font-weight:bold;">VALID</span>
                        <?php else: ?>
                        <span style="color:#e74c3c;font-weight:bold;">INVALID</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $r['valid'] ? htmlspecialchars($r['type']) : 'unknown' ?></td>
                    <td><code><?= htmlspecialchars($r['flag']) ?></code></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($allValid && count($results) === 3): ?>
        <div class="alert" style="margin-top:1rem;background:rgba(46,204,113,0.15);border:1px solid var(--success, #2ecc71);">
            All flags verified successfully! T3rmx infrastructure fully compromised.
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Verify Flags</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/verify" class="form">
            <div class="form-group">
                <label for="flag1">Flag 1</label>
                <input type="text" id="flag1" name="flag1" placeholder="TCI{...}" autocomplete="off">
            </div>
            <div class="form-group">
                <label for="flag2">Flag 2</label>
                <input type="text" id="flag2" name="flag2" placeholder="TCI{...}" autocomplete="off">
            </div>
            <div class="form-group">
                <label for="flag3">Flag 3</label>
                <input type="text" id="flag3" name="flag3" placeholder="TCI{...}" autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary">Verify</button>
        </form>
        <small class="text-muted">Flags use the format TCI{...}. The verification system only confirms a flag is the genuine proof-of-compromise value; it never reveals the value itself.</small>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('Flag Verification', $content, false);
?>