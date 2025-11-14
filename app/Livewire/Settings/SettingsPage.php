<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SettingsPage extends Component
{
    // Notification Settings
    public $emailNotifications;
    public $pushNotifications;
    public $weeklyDigest;
    public $taskReminders;
    public $projectUpdates;

    // Privacy Settings
    public $profileVisibility;
    public $dataCollection;

    // Preferences
    public $timezone;
    public $language;
    public $dateFormat;
    public $timeFormat;

    public function mount()
    {
        $settings = Auth::user()->getOrCreateSettings();

        // Load notification settings
        $this->emailNotifications = $settings->email_notifications;
        $this->pushNotifications = $settings->push_notifications;
        $this->weeklyDigest = $settings->weekly_digest;
        $this->taskReminders = $settings->task_reminders;
        $this->projectUpdates = $settings->project_updates;

        // Load privacy settings
        $this->profileVisibility = $settings->profile_visibility;
        $this->dataCollection = $settings->data_collection;

        // Load preferences
        $this->timezone = $settings->timezone;
        $this->language = $settings->language;
        $this->dateFormat = $settings->date_format;
        $this->timeFormat = $settings->time_format;
    }

    public function toggleSetting($key)
    {
        $this->$key = !$this->$key;
        $this->saveSetting($key);
    }

    public function saveSetting($key)
    {
        $settings = Auth::user()->getOrCreateSettings();

        // Map component properties to database columns
        $columnName = match($key) {
            'emailNotifications' => 'email_notifications',
            'pushNotifications' => 'push_notifications',
            'weeklyDigest' => 'weekly_digest',
            'taskReminders' => 'task_reminders',
            'projectUpdates' => 'project_updates',
            'profileVisibility' => 'profile_visibility',
            'dataCollection' => 'data_collection',
            'timezone' => 'timezone',
            'language' => 'language',
            'dateFormat' => 'date_format',
            'timeFormat' => 'time_format',
            default => $key,
        };

        $settings->update([$columnName => $this->$key]);

        session()->flash('message', 'Settings updated successfully!');
    }

    public function updatePreference($key)
    {
        $this->saveSetting($key);
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.settings.settings-page');
    }
}
