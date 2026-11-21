<dialog id="management-edit-dialog" class="management-dialog" aria-labelledby="management-edit-title">
    <header class="management-dialog-header">
        <h2 id="management-edit-title">Edit</h2>
        <button type="button" data-dialog-close aria-label="Close form window" autofocus>×</button>
    </header>
    <div class="management-dialog-body" data-edit-body aria-busy="false"></div>
</dialog>

<dialog id="management-delete-dialog" class="management-dialog management-dialog--delete"
        aria-labelledby="management-delete-title" aria-describedby="management-delete-message">
    <header class="management-dialog-header">
        <h2 id="management-delete-title">Confirm deletion</h2>
        <button type="button" data-dialog-close aria-label="Close confirmation">×</button>
    </header>
    <div class="management-dialog-body">
        <p id="management-delete-message"></p>
    </div>
    <footer class="management-dialog-footer">
        <button type="button" class="admin-button admin-button--outline" data-dialog-close autofocus>Cancel</button>
        <button type="button" class="admin-button admin-button--danger" data-dialog-confirm>Delete</button>
    </footer>
</dialog>
