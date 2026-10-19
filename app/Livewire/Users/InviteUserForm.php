<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
use Illuminate\Validation\Rule;
use Livewire\Form;

/**
 * Invite User Form
 *
 * Livewire form object for the Users page's invite modal, kept separate from the component so its fields don't push
 * Table past PHPMD's field-count limit. The account is created with a random password and a set-password link is
 * emailed, so nobody signs in until they have set one.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Livewire
 */
class InviteUserForm extends Form
{
    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $role = '';

    public string $phone = '';

    /**
     * Reset the form back to its blank defaults
     *
     * @return void
     */
    public function clear(): void
    {
        $this->reset(['name', 'username', 'email', 'phone']);
        $this->role = UserRole::Agent->value; // agents are who we invite most
    }

    /**
     * Validation rules for the form's current state
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(UserRole::values())],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Validation attribute names, so the messages read "username" and not "invite form username"
     *
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'name' => __('users.form.name_label'),
            'username' => __('users.form.username_label'),
            'email' => __('users.form.email_label'),
            'role' => __('users.form.role_label'),
            'phone' => __('users.form.phone_label'),
        ];
    }
}
