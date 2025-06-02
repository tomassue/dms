<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefDivision;
use App\Models\RefPosition;
use App\Models\RefSignatories;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('Signatories')]
class Signatories extends Component
{
    use WithPagination;

    public $search;
    public $editMode;
    public $signatoryId;
    public $title, $name, $office_id;

    public function rules()
    {
        $rules = [
            'name' => 'required|unique:ref_signatories,name,' . $this->signatoryId,
            'title' => 'required',
        ];

        if (Auth::user()->hasRole('Super Admin')) {
            $rules['office_id'] = 'required';
        }

        return $rules;
    }

    public function clear()
    {
        $this->reset();
        $this->resetValidation();
        $this->dispatch('reset-user-select');
    }

    public function render()
    {
        return view(
            'livewire.shared.settings.signatories',
            [
                'signatories' => $this->loadSignatories(),
                'users' => $this->loadUsers(), # Load users for dropdown
                'offices' => $this->loadOffices(), // Load offices for dropdown
            ]
        );
    }

    /**
     * * When loading signatories - users; since this is shared, make sure to only retrieve data associated with their role (office).
     * For client perspective, make sure to only retrieve data associated with their role (the office).
     */
    public function loadSignatories()
    {
        return RefSignatories::query()
            ->withTrashed()
            ->with('user')
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->paginate(10);
    }

    public function loadUsers()
    {
        return User::query()
            ->with(['roles', 'user_metadata'])
            ->withoutRole('Super Admin')
            ->where('id', '!=', auth()->id())
            ->get()
            ->map(function ($user) {
                $parts = array_filter([
                    $user->roles()->first()?->name,
                    $user->user_metadata?->division?->name,
                    $user->user_metadata?->position?->name,
                ]);

                return [
                    'label' => $user->name,
                    'description' => $parts ? implode(' | ', $parts) : 'No details available',
                    'value' => $user->id
                ];
            });
    }

    public function loadOffices()
    {
        $offices = Role::whereNot('name', 'Super Admin')
            ->get();

        return $offices;
    }

    public function saveSignatory()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $data = [
                    'name' => $this->name,
                    'title' => $this->title
                ];

                if (Auth::user()->hasRole('Super Admin')) {
                    $data['office_id'] = $this->office_id;
                } else {
                    $data['office_id'] = auth()->user()->roles()->first()->id;
                }

                RefSignatories::updateOrCreate(
                    ['id' => $this->signatoryId],
                    $data
                );

                $this->clear();
                $this->dispatch('hide-signatory-modal');
                $this->dispatch('success', message: 'Signatory created successfully!');
            });
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editSignatory(RefSignatories $signatory)
    {
        try {
            $this->name = $signatory->name;
            $this->title = $signatory->title;
            $this->office_id = $signatory->office_id;

            $this->signatoryId = $signatory->id;
            $this->editMode = true;

            $this->dispatch('show-signatory-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('success', message: 'Signatory updated successfully!');
        }
    }

    public function deleteSignatory($signatoryId)
    {
        try {
            $signatory = RefSignatories::find($signatoryId);
            $signatory->delete();
            $this->clear();
            $this->dispatch('success', message: 'Signatory deleted successfully!');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function restoreSignatory($signatoryId)
    {
        try {
            $signatory = RefSignatories::withTrashed()->find($signatoryId);
            $signatory->restore();
            $this->clear();
            $this->dispatch('success', message: 'Signatory restored successfully!');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
