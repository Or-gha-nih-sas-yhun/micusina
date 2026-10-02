<style>
    #logoutConfirmation[hidden] { display: none !important; }
    #logoutConfirmation { align-items: center; background: rgba(239, 246, 255, .92) !important; display: flex; inset: 0; justify-content: center; padding: 20px; position: fixed !important; z-index: 2147483647 !important; }
    #logoutConfirmation .logout-confirmation__dialog { background: #fff; border-radius: 20px; box-shadow: 0 18px 50px rgba(43, 58, 85, .16); color: #3d4149; max-width: 382px; padding: 26px; width: 100%; }
    .logout-confirmation__dialog h2 { font-size: 21px; font-weight: 800; margin: 0 0 16px; }
    .logout-confirmation__dialog p { color: #9aa1ad; font-size: 14px; line-height: 1.55; margin: 0 0 24px; }
    .logout-confirmation__actions { display: flex; gap: 12px; }
    .logout-confirmation__actions button { border: 0; border-radius: 9px; cursor: pointer; flex: 1; font-size: 14px; font-weight: 800; min-height: 44px; padding: 11px 16px; }
    .logout-confirmation__cancel { background: #eeeeef; color: #858992; }
    .logout-confirmation__submit { background: #f56060; box-shadow: 0 7px 14px rgba(245, 96, 96, .22); color: #fff; }
    .logout-confirmation__submit:hover { background: #e94f50; }
</style>

<div class="logout-confirmation" id="logoutConfirmation" hidden>
    <div class="logout-confirmation__dialog" role="dialog" aria-modal="true" aria-labelledby="logoutConfirmationTitle">
        <h2 id="logoutConfirmationTitle">Log out?</h2>
        <p id="logoutConfirmationMessage">Are you sure you want to log out?</p>
        <div class="logout-confirmation__actions">
            <button class="logout-confirmation__cancel" type="button" data-logout-cancel>Cancel</button>
            <button class="logout-confirmation__submit" type="button" data-logout-submit>Log Out</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById('logoutConfirmation');
        if (!modal) return;
        if (modal.dataset.logoutConfirmationReady) return;
        modal.dataset.logoutConfirmationReady = 'true';
        // Keep the fixed backdrop outside headers, dropdowns, and page containers.
        document.body.appendChild(modal);
        var pendingForm = null;
        var cancel = modal.querySelector('[data-logout-cancel]');
        var submit = modal.querySelector('[data-logout-submit]');
        var title = modal.querySelector('#logoutConfirmationTitle');
        var message = modal.querySelector('#logoutConfirmationMessage');
        var close = function () { modal.hidden = true; pendingForm = null; };
        var open = function (form) {
            pendingForm = form;
            title.textContent = form.dataset.confirmationTitle || 'Log out?';
            message.textContent = form.dataset.confirmationMessage || 'Are you sure you want to log out?';
            submit.textContent = form.dataset.confirmationSubmit || 'Log Out';
            modal.hidden = false;
            cancel.focus();
        };

        document.querySelectorAll('form[data-logout-form]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                open(form);
            });

            var trigger = form.querySelector('[data-logout-trigger]');
            if (trigger) {
                trigger.addEventListener('click', function (event) {
                    event.preventDefault();
                    open(form);
                });
            }
        });

        document.querySelectorAll('form[data-confirmation-form]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                open(form);
            });
        });
        cancel.addEventListener('click', close);
        submit.addEventListener('click', function () { if (pendingForm) pendingForm.submit(); });
        modal.addEventListener('click', function (event) { if (event.target === modal) close(); });
        document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && !modal.hidden) close(); });
    });
</script>
