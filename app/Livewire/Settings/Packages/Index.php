<?php

namespace App\Livewire\Settings\Packages;

use App\Models\Package;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public bool $editing = false;

    public int $packageId = 0;

    public string $code = '';

    public string $name = '';

    public ?string $description = null;

    public bool $isActive = true;

    public string $sortField = 'code';

    public string $sortDirection = 'asc';

    public function getPackagesProperty()
    {
        return Package::query()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(25);
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('packages', 'code')->ignore($this->packageId),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
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
        $package = Package::findOrFail($id);

        $this->packageId = $package->id;
        $this->code = $package->code;
        $this->name = $package->name;
        $this->description = $package->description;
        $this->isActive = $package->is_active;
        $this->editing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editing) {
            Package::where('id', $this->packageId)->update([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'is_active' => $validated['isActive'],
            ]);

            $this->dispatch('show-toast', type: 'success', message: 'Package updated successfully');
        } else {
            Package::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'is_active' => $validated['isActive'],
            ]);

            $this->dispatch('show-toast', type: 'success', message: 'Package created successfully');
        }

        $this->cancel();
    }

    public function delete(int $id): void
    {
        $package = Package::findOrFail($id);

        if ($package->participants()->exists()) {
            $this->dispatch('show-toast', type: 'error', message: 'Cannot delete package that is in use by participants');

            return;
        }

        $package->delete();

        $this->dispatch('show-toast', type: 'success', message: 'Package deleted successfully');
    }

    public function toggleActive(int $id): void
    {
        $package = Package::findOrFail($id);
        $package->is_active = ! $package->is_active;
        $package->save();

        $status = $package->is_active ? 'activated' : 'deactivated';
        $this->dispatch('show-toast', type: 'success', message: "Package {$status} successfully");
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
        $this->packageId = 0;
        $this->code = '';
        $this->name = '';
        $this->description = null;
        $this->isActive = true;
        $this->editing = false;
    }

    public function render()
    {
        return view('livewire.settings.packages.index', [
            'packages' => $this->packages,
        ]);
    }
}
