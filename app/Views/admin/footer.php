    </div> <!-- end main -->
</div> <!-- end container -->

<div class="admin-confirm-modal" id="adminConfirmModal" aria-hidden="true">
    <div class="admin-confirm-card" role="dialog" aria-modal="true" aria-labelledby="adminConfirmTitle">
        <div class="admin-confirm-icon">
            <i data-lucide="triangle-alert"></i>
        </div>
        <div class="admin-confirm-content">
            <h3 id="adminConfirmTitle">Confirm Action</h3>
            <p id="adminConfirmMessage">Are you sure?</p>
        </div>
        <div class="admin-confirm-actions">
            <button type="button" class="admin-confirm-btn secondary" id="adminConfirmCancel">Cancel</button>
            <button type="button" class="admin-confirm-btn danger" id="adminConfirmOk">Delete</button>
        </div>
    </div>
</div>

<script>
    window.AdminConfirm = (function () {
        const modal = document.getElementById('adminConfirmModal');
        const title = document.getElementById('adminConfirmTitle');
        const message = document.getElementById('adminConfirmMessage');
        const cancelBtn = document.getElementById('adminConfirmCancel');
        const okBtn = document.getElementById('adminConfirmOk');
        let activeResolve = null;

        function close(result) {
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');

            if (activeResolve) {
                activeResolve(result);
                activeResolve = null;
            }
        }

        cancelBtn.addEventListener('click', function () {
            close(false);
        });

        okBtn.addEventListener('click', function () {
            close(true);
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                close(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('active')) {
                close(false);
            }
        });

        return {
            open: function (options) {
                const settings = options || {};
                title.textContent = settings.title || 'Confirm Action';
                message.textContent = settings.message || 'Are you sure?';
                okBtn.textContent = settings.confirmText || 'Confirm';
                cancelBtn.textContent = settings.cancelText || 'Cancel';
                cancelBtn.style.display = settings.cancelText === '' ? 'none' : '';

                modal.classList.add('active');
                modal.setAttribute('aria-hidden', 'false');
                okBtn.focus();

                return new Promise(function (resolve) {
                    activeResolve = resolve;
                });
            }
        };
    })();

    lucide.createIcons();
</script>

</body>
</html>
