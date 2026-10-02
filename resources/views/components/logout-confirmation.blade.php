<style>
    #logoutConfirmation[hidden] { display: none !important; }
    #logoutConfirmation { align-items: center; background: rgba(0, 0, 0, .62) !important; display: flex; inset: 0; justify-content: center; padding: 20px; position: fixed !important; z-index: 2147483647 !important; }
    #logoutConfirmation .logout-confirmation__dialog { background: #fff; border-radius: 12px; box-shadow: 0 18px 50px rgba(0, 0, 0, .3); color: #1f2937; max-width: 390px; padding: 24px; width: 100%; }
    #logoutConfirmation .logout-confirmation__dialog h2 { font-size: 20px; margin: 0 0 10px; }
    #logoutConfirmation .logout-confirmation__dialog p { margin: 0 0 22px; }
    #logoutConfirmation .logout-confirmation__actions { display: flex; gap: 10px; justify-content: flex-end; }
    #logoutConfirmation .logout-confirmation__actions button { border: 0 !important; border-radius: 6px; cursor: pointer; font-weight: 700; padding: 10px 16px; }
    #logoutConfirmation .logout-confirmation__cancel { background: #e5e7eb !important; color: #111827 !important; }
    #logoutConfirmation .logout-confirmation__submit { background: #f56060 !important; color: #fff !important; }
    #logoutConfirmation .logout-confirmation__submit:hover { background: #e94f50 !important; }
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
