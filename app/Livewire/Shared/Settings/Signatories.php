<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefDivision;
use App\Models\RefPosition;
use App\Models\RefSignatories;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Signatories extends Component
{
    use WithPagination;

    public $search;
    public $editMode;
    public $signatoryId;
    public $user_id;

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id|unique:ref_signatories,user_id,' . $this->signatoryId,
        ];
    }

    public function attributes()
    {
        return [
            'user_id' => 'user'
        ];
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

    public function createSignatory()
    {
        $this->validate($this->rules(), [], $this->attributes());

        try {
            DB::transaction(function () {
                $signatory = new RefSignatories();
                $signatory->user_id = $this->user_id;
                $signatory->save();

                $this->clear();
                $this->dispatch('hide-signatory-modal');
                $this->dispatch('success', message: 'Signatory created successfully!');
            });
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    // public function editSignatory($signatoryId)
    // {
    //     try {
    //         $signatory = RefSignatories::find($signatoryId);
    //         $this->dispatch('set-user-select', value: $signatory->user_id);
    //         $this->dispatch('show-signatory-modal');
    //     } catch (\Throwable $th) {
    //         //throw $th;
    //         $this->dispatch('success', message: 'Signatory updated successfully!');
    //     }
    // }

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
