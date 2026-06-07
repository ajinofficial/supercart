<?php if (session()->get('logged_in') === true && (int) session()->get('us_role_id') === 2): ?>
<a class="user-notification-link" href="<?= base_url('user/notifications') ?>" aria-label="Notifications" title="Notifications">
    <i class="fa-solid fa-bell"></i>
    <span class="user-notification-badge" data-user-notification-count hidden>0</span>
</a>
<style>
    .user-notification-link {
        position: relative;
        width: 38px;
        height: 38px;
        flex: 0 0 auto;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
        border: 1px solid rgba(16, 36, 29, .12);
        border-radius: 50%;
        color: inherit;
        background: rgba(255, 255, 255, .72);
        text-decoration: none;
    }
    .user-notification-link:hover { background: #fff; }
    .user-notification-badge {
        position: absolute;
        top: -5px;
        right: -6px;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        border-radius: 10px;
        color: #fff;
        background: #e53935;
        font-size: 10px;
        font-weight: 800;
        line-height: 1;
    }
</style>
<script>
    (function () {
        const badge = document.querySelector('[data-user-notification-count]');
        if (!badge) return;

        function updateCount(value) {
            const count = Math.max(0, Number(value || 0));
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.hidden = count === 0;
        }

        function refreshCount() {
            fetch(<?= json_encode(base_url('user/notifications/unread-count')) ?>, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (response) { return response.ok ? response.json() : null; })
                .then(function (payload) {
                    if (payload && payload.status) updateCount(payload.unread_count);
                })
                .catch(function () {});
        }

        refreshCount();
        window.setInterval(refreshCount, 15000);
    })();
</script>
<?php endif; ?>
