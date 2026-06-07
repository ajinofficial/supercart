<?php
$page = 'dashboard_template';
include('header.php');
include('menus.php');
$templates = is_array($templates ?? null) ? $templates : [];
?>

<style>
    .templates-page {
        width: 100%;
    }
    .templates-card {
        background: #fff;
        border: 1px solid #e3e9f2;
        border-radius: 14px;
        overflow: hidden;
    }
    .templates-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid #e8eef6;
        background: #fff;
    }
    .templates-card-body {
        padding: 18px 20px;
    }
    .templates-title {
        margin: 0;
        font-size: 1.22rem;
        font-weight: 800;
        color: #102a43;
    }
    .templates-subtitle {
        margin: 4px 0 0;
        color: #607488;
        font-size: 0.84rem;
    }
    .templates-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 0;
        border-radius: 9px;
        background: var(--theme-color);
        color: #fff;
        text-decoration: none;
        padding: 9px 13px;
        font-size: 0.83rem;
        font-weight: 800;
        box-shadow: 0 8px 18px rgba(var(--theme-rgb), 0.22);
    }
    .templates-btn:hover {
        color: #fff;
        background: var(--theme-color-dark);
    }
    .templates-table {
        margin-bottom: 0;
    }
    .templates-table th,
    .templates-table td {
        padding: 13px 12px;
        font-size: 0.83rem;
        color: #33485f;
    }
    .templates-table th {
        background: #f8fafc;
        color: #50657b;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .templates-table tr.active-row {
        background: #f3fbf8;
    }
    .templates-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 4px 8px;
        background: #e9f8ee;
        color: #137a3d;
        font-size: 0.74rem;
        font-weight: 800;
    }
    .templates-actions {
        display: inline-flex;
        gap: 7px;
        align-items: center;
    }
    .templates-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid #d7e2ed;
        border-radius: 8px;
        color: #193b57;
        background: #fff;
        padding: 7px 9px;
        text-decoration: none;
        font-size: 0.78rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .templates-action:hover {
        background: #f2f7fc;
        color: #193b57;
    }
    .templates-empty {
        border: 1px dashed #d7e2ed;
        border-radius: 10px;
        padding: 28px 16px;
        color: #607488;
        font-size: 0.9rem;
        text-align: center;
        background: #fbfdff;
    }
    @media (max-width: 992px) {
        .templates-card-header {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<div class="templates-page">
    <div class="templates-card shadow-sm">
        <div class="templates-card-header">
            <div>
                <h2 class="templates-title">Dashboard Templates</h2>
                <p class="templates-subtitle">Create, list, edit, and open saved dashboard JSON templates.</p>
            </div>
            <a class="templates-btn" href="<?= base_url('admin/dashboard-template/create') ?>">
                <i data-lucide="plus"></i>
                <span>Create Template</span>
            </a>
        </div>

        <div class="templates-card-body">
            <?php if (!empty($templates)): ?>
                <div class="table-responsive">
                <table class="table templates-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Template Name</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($templates as $row): ?>
                            <?php
                                $rowId = (int) ($row['id'] ?? 0);
                                $isActive = (int) ($row['dt_is_active'] ?? 0) === 1;
                            ?>
                            <tr class="<?= $isActive ? 'active-row' : '' ?>">
                                <td>#<?= esc((string) $rowId) ?></td>
                                <td><strong><?= esc((string) ($row['dt_name'] ?? 'Dashboard Template')) ?></strong></td>
                                <td><?= esc((string) ($row['dt_slug'] ?? '')) ?></td>
                                <td>
                                    <?php if ($isActive): ?>
                                        <span class="templates-badge">Active</span>
                                    <?php else: ?>
                                        Draft
                                    <?php endif; ?>
                                </td>
                                <td><?= esc((string) ($row['updated_at'] ?? $row['created_at'] ?? '-')) ?></td>
                                <td>
                                    <div class="templates-actions">
                                        <a class="templates-action" href="<?= base_url('admin/dashboard-template/edit/' . $rowId) ?>">
                                            <i data-lucide="pencil"></i>
                                            <span>Edit</span>
                                        </a>
                                        <a class="templates-action" href="<?= base_url('user/dashboard') ?>" target="_blank" rel="noopener">
                                            <i data-lucide="external-link"></i>
                                            <span>Open</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php else: ?>
                <div class="templates-empty">No dashboard templates saved yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>

<?php include('footer.php'); ?>
