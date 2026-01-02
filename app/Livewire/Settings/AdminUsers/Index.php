<?php

namespace App\Livewire\Settings\AdminUsers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public bool $editing = false;

    public int $userId = 0;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $isActive = true;

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public function getUsersProperty()
    {
        return User::query()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(25);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],
            'password' => [
                $this->editing ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'isActive' => 'required|boolean',
        ];
    }

    public function create(): void
    {
        $this->editing = false;
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->isActive = $user->is_active;
        $this->editing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editing) {
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $validated['isActive'],
            ];

            if (! empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            User::where('id', $this->userId)->update($updateData);

            $this->dispatch('show-toast', type: 'success', message: 'Admin user updated successfully');
        } else {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_active' => $validated['isActive'],
            ]);

            $this->dispatch('show-toast', type: 'success', message: 'Admin user created successfully');
        }

        $this->cancel();
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            $this->dispatch('show-toast', type: 'error', message: 'You cannot delete your own account');

            return;
        }

        $totalAdmins = User::count();

        if ($totalAdmins <= 1) {
            $this->dispatch('show-toast', type: 'error', message: 'Cannot delete the last admin user');

            return;
        }

        $user->delete();

        $this->dispatch('show-toast', type: 'success', message: 'Admin user deleted successfully');
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id() && $user->is_active) {
            $this->dispatch('show-toast', type: 'error', message: 'You cannot deactivate your own account');

            return;
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';
        $this->dispatch('show-toast', type: 'success', message: "Admin user {$status} successfully");
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function cancel(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->userId = 0;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->isActive = true;
        $this->editing = false;
    }

    public function render()
    {
        return view('livewire.settings.admin-users.index', [
            'users' => $this->users,
        ]);
    }
}
