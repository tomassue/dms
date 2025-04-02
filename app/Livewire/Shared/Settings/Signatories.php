<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefDivision;
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
    public $name,
        $user_id,
        $ref_position_id,
        $ref_division_id;

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('ref_signatories')->where(function ($query) {
                    return $query->where('ref_position_id', $this->ref_position_id)
                        ->where('ref_division_id', $this->ref_division_id);
                })->ignore($this->signatoryId), # Ignore the current record
            ],
            'ref_position_id' => 'required|exists:ref_positions,id',
            'ref_division_id' => 'required|exists:ref_divisions,id',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'name',
            'user_id' => 'user',
            'ref_position_id' => 'position',
            'ref_division_id' => 'division'
        ];
    }

    public function clear()
    {
        $this->reset();
        $this->resetValidation();
    }

    public function render()
    {
        return view(
            'livewire.shared.settings.signatories',
            [
                'signatories' => $this->loadSignatories(),
                'users' => $this->loadUsers(), # Load users for dropdown
                'divisions' => $this->loadDivisions(), # Load divisions for dropdown
            ]
        );
    }

    # TODO: When loading signatories, users, and divisions; since this is shared, make sure to only retrieve data associated with their role (office).
    public function loadSignatories()
    {
        return RefSignatories::query()
            ->paginate(10);
    }

    public function loadUsers()
    {
        return User::query()
            ->whereNot('name', 'Super Admin')
            ->get();
    }

    public function loadDivisions()
    {
        return RefDivision::all();
    }

    public function createSignatory()
    {
        $this->validate($this->rules(), [], $this->attributes());

        try {
            DB::transaction(function () {
                $signatory = new RefSignatories();
                $signatory->user_id = $this->user_id;
                $signatory->ref_position_id = $this->ref_position_id;
                $signatory->ref_division_id = $this->ref_division_id;
                $signatory->name = $this->name;
                $signatory->save();

                $this->clear();
                $this->dispatch('hide-signatory-modal');
                $this->dispatch('success', message: 'Signatory created successfully!');
            });
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('success', message: 'Signatory created successfully!');
        }
    }
}
