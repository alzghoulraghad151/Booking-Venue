@if ($errors->any())
    <ul role="alert">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="management-user-fields">
        <div>
        <label for="name">Name</label>
        <input id="name" name="name" value="{{ old('name', $user?->name ?? '') }}" required maxlength="255">
        </div>
        <div>
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $user?->email ?? '') }}" required maxlength="255">
        </div>
        <div>
        <label for="phone">Phone</label>
        <input id="phone" name="phone" value="{{ old('phone', $user?->phone ?? '') }}" maxlength="20">
        </div>
        <div>
        <label for="role">Role</label>
        <select id="role" name="role" required>
        @foreach (['organizer' => 'Organizer', 'venue_owner' => 'Venue owner', 'admin' => 'Admin'] as $value => $label)
        <option value="{{ $value }}" @selected(old('role', $user?->role ?? 'organizer') === $value)>{{ $label }}</option>
        @endforeach
        </select>
        </div>
        <div>
        <label for="password">{{ $editing ? 'New password (leave blank to keep current)' : 'Password' }}</label>
        <input id="password" type="password" name="password" autocomplete="new-password" @required(! $editing) minlength="8">
        </div>
        <div>
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" @required(! $editing) minlength="8">
        </div>
    </div>

    @if ($editing)
        <x-admin.edit-actions :back-url="route('admin.users.show', $user)" back-label="Back to user details" save-label="Save changes" />
    @else
        <x-admin.edit-actions :back-url="route('admin.users.index')" save-label="Create user" />
    @endif
</form>
