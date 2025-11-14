<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfilePage extends Component
{
    use WithFileUploads;

    public $isEditing = false;

    // Current profile data
    public $name;
    public $email;
    public $avatar;
    public $phone;
    public $location;
    public $bio;
    public $joinDate;

    // Edit form data
    public $editName;
    public $editEmail;
    public $editPhone;
    public $editLocation;
    public $editBio;
    public $newAvatar;

    public function mount()
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->avatar = $user->avatar;
        $this->phone = $user->phone ?? '';
        $this->location = $user->location ?? '';
        $this->bio = $user->bio ?? '';
        $this->joinDate = $user->created_at->format('F j, Y');

        $this->initializeEditFields();
    }

    public function initializeEditFields()
    {
        $this->editName = $this->name;
        $this->editEmail = $this->email;
        $this->editPhone = $this->phone;
        $this->editLocation = $this->location;
        $this->editBio = $this->bio;
    }

    public function startEditing()
    {
        $this->isEditing = true;
        $this->initializeEditFields();
    }

    public function cancelEditing()
    {
        $this->isEditing = false;
        $this->initializeEditFields();
    }

    public function saveProfile()
    {
        $this->validate([
            'editName' => 'required|min:2|max:255',
            'editEmail' => 'required|email|max:255',
            'editPhone' => 'nullable|max:20',
            'editLocation' => 'nullable|max:255',
            'editBio' => 'nullable|max:500',
            'newAvatar' => 'nullable|image|max:2048', // 2MB max
        ]);

        $user = Auth::user();

        $updateData = [
            'name' => $this->editName,
            'email' => $this->editEmail,
            'phone' => $this->editPhone,
            'location' => $this->editLocation,
            'bio' => $this->editBio,
        ];

        // Handle avatar upload
        if ($this->newAvatar) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $avatarPath = $this->newAvatar->store('avatars', 'public');
            $updateData['avatar'] = $avatarPath;
            $this->avatar = $avatarPath;
        }

        $user->update($updateData);

        // Update component properties
        $this->name = $this->editName;
        $this->email = $this->editEmail;
        $this->phone = $this->editPhone;
        $this->location = $this->editLocation;
        $this->bio = $this->editBio;

        $this->newAvatar = null;
        $this->isEditing = false;

        session()->flash('message', 'Profile updated successfully!');
    }

    public function removeAvatar()
    {
        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
            $this->avatar = null;
        }

        session()->flash('message', 'Avatar removed successfully!');
    }

    public function render()
    {
        return view('livewire.profile.profile-page');
    }
}
