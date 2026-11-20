@if ($errors->any())
    <ul role="alert">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form class="admin-edit-form" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <label for="name">Name</label>
    <input id="name" name="name" value="{{ old('name', $user?->name ?? '') }}" required maxlength="255">

    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email', $user?->email ?? '') }}" required maxlength="255">

    <label for="phone">Phone</label>
    <input id="phone" name="phone" value="{{ old('phone', $user?->phone ?? '') }}" maxlength="20">

    <label for="role">Role</label>
    <select id="role" name="role" required>
        @foreach (['organizer' => 'Organizer', 'venue_owner' => 'Venue owner', 'admin' => 'Admin'] as $value => $label)
            <option value="{{ $value }}" @selected(old('role', $user?->role ?? 'organizer') === $value)>{{ $label }}</option>
        @endforeach
    </select>

    <label for="password">{{ $editing ? 'New password (leave blank to keep current)' : 'Password' }}</label>
    <input id="password" type="password" name="password" autocomplete="new-password" @required(! $editing) minlength="8">

    <label for="password_confirmation">Confirm password</label>
    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" @required(! $editing) minlength="8">

    <button @class(['admin-button', 'admin-button--primary', 'admin-button--page']) type="submit">
        @unless ($editing)
            <svg aria-hidden="true" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M8 3v10M3 8h10"/></svg>
        @endunless
        {{ $editing ? 'Save changes' : 'Create user' }}
    </button>
</form>
