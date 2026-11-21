@props(['backUrl', 'backLabel' => 'Cancel', 'saveLabel' => 'Save changes'])

<div class="management-edit-actions">
    <button class="admin-button admin-button--primary" type="submit">{{ $saveLabel }}</button>
    <a class="admin-button admin-button--outline" href="{{ $backUrl }}">{{ $backLabel }}</a>
</div>
