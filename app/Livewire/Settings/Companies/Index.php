<?php

namespace App\Livewire\Settings\Companies;

use App\Models\Company;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public bool $editing = false;

    public int $companyId = 0;

    public string $code = '';

    public string $name = '';

    public string $prologTemplate = '';

    public ?string $footerTemplate = null;

    public bool $isActive = true;

    public string $sortField = 'code';

    public string $sortDirection = 'asc';

    public function getCompaniesProperty()
    {
        return Company::query()
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
                Rule::unique('companies', 'code')->ignore($this->companyId),
            ],
            'name' => 'required|string|max:255',
            'prologTemplate' => 'required|string|max:2000',
            'footerTemplate' => 'nullable|string|max:1000',
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
        $company = Company::findOrFail($id);

        $this->companyId = $company->id;
        $this->code = $company->code;
        $this->name = $company->name;
        $this->prologTemplate = $company->prolog_template;
        $this->footerTemplate = $company->footer_template;
        $this->isActive = $company->is_active;
        $this->editing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editing) {
            Company::where('id', $this->companyId)->update([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'prolog_template' => $validated['prologTemplate'],
                'footer_template' => $validated['footerTemplate'],
                'is_active' => $validated['isActive'],
            ]);

            $this->dispatch('show-toast', type: 'success', message: 'Company updated successfully');
        } else {
            Company::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'prolog_template' => $validated['prologTemplate'],
                'footer_template' => $validated['footerTemplate'],
                'is_active' => $validated['isActive'],
            ]);

            $this->dispatch('show-toast', type: 'success', message: 'Company created successfully');
        }

        $this->cancel();
    }

    public function delete(int $id): void
    {
        $company = Company::findOrFail($id);

        if ($company->participants()->exists()) {
            $this->dispatch('show-toast', type: 'error', message: 'Cannot delete company that is in use by participants');

            return;
        }

        $company->delete();

        $this->dispatch('show-toast', type: 'success', message: 'Company deleted successfully');
    }

    public function toggleActive(int $id): void
    {
        $company = Company::findOrFail($id);
        $company->is_active = ! $company->is_active;
        $company->save();

        $status = $company->is_active ? 'activated' : 'deactivated';
        $this->dispatch('show-toast', type: 'success', message: "Company {$status} successfully");
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
        $this->companyId = 0;
        $this->code = '';
        $this->name = '';
        $this->prologTemplate = '';
        $this->footerTemplate = null;
        $this->isActive = true;
        $this->editing = false;
    }

    public function render()
    {
        return view('livewire.settings.companies.index', [
            'companies' => $this->companies,
        ]);
    }
}
