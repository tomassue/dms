<?php

namespace App\Livewire\Shared;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Title('Account Settings')]
class AccountSettings extends Component
{
    public $page = 1;
    public $editProfile;
    public $user;

    public $name,
        $username,
        $email,
        $office,
        $division,
        $position;

    public $current_password,
        $new_password,
        $confirm_password;

    public function mount()
    {
        $this->user = auth()->user();
        $this->loadPersonalDetails();
    }

    public function refresh()
    {
        $this->user = auth()->user();
        $this->loadPersonalDetails();
        $this->render();
    }

    public function clear()
    {
        $this->resetExcept('user', 'page');
        $this->resetValidation();
        $this->loadPersonalDetails();
    }

    public function render()
    {
        return view(
            'livewire.shared.account-settings',
            [
                'activity' => $this->loadActivityLogs()
            ]
        );
    }

    public function loadPersonalDetails()
    {
        $this->name = $this->user->name;
        $this->username = $this->user->username;
        $this->email = $this->user->email;
        $this->office = Auth::user()->getRoleNames()->first();
        $this->division = $this->user->user_metadata->division->name ?? null;
        $this->position = $this->user->user_metadata->position->name ?? null;
    }

    public function editPersonalDetails()
    {
        $this->editProfile = true;
    }

    public function savePersonalDetails()
    {
        $this->validate(['name' => 'required'], [], []);

        try {
            DB::transaction(function () {
                User::updateOrCreate(
                    ['id' => $this->user->id],
                    [
                        'name' => $this->name
                    ]
                );

                /**
                 * * You might wonder why we are manually refreshing the component.
                 * This time, we are not rendering data from the model but we assign them to the properties.
                 * Thus, when we update the properties, we access the properties then update them.
                 * We call back the method to refresh new data assigned to the properties.
                 */
                $this->clear();
                $this->dispatch('success', message: 'Personal details successfully saved.');
            });

            $this->refresh();
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function saveNewPassword()
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => [
                'required',
                'min:8',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                'different:current_password'
            ],
            'confirm_password' => ['required', 'same:new_password'],
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
            'new_password.regex' => 'Password must contain at least 1 letter, 1 number, and 1 special character.',
            'new_password.different' => 'New password must be different from current password.',
            'confirm_password.same' => 'The passwords do not match.',
        ]);

        try {
            DB::transaction(function () {
                $this->user->update([
                    'password' => Hash::make($this->new_password),
                    'password_changed_at' => now() // Track when password was changed
                ]);

                $this->dispatch('success', message: 'Password updated successfully!');
                return redirect()->route('dashboard');
            });
        } catch (\Throwable $th) {
            $this->reset(['current_password', 'new_password', 'confirm_password']);
            $this->dispatch('error', message: 'Failed to update password. Please try again.');
            report($th); // Log the error for debugging
        }
    }

    public function loadActivityLogs()
    {
        $activity = Activity::where('causer_type', User::class)
            ->where('causer_id', $this->user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'event' => $activity->event,
                    'log_name' => str_replace('_', ' ', $activity->log_name),
                    'subject_id' => $activity->subject_id,
                    'time' => Carbon::parse($activity->created_at)->format('g:i A'),
                    'date' => Carbon::parse($activity->created_at)->format('F d,Y')
                ];
            });

        return $activity;
    }
}
