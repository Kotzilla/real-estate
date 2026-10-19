<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Form;

/**
 * Edit User Form
 *
 * Livewire form object for the Users page's edit modal, kept separate from the component so its fields don't push
 * Table past PHPMD's field-count limit.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Livewire
 */
class EditUserForm extends Form
{
    public ?int $userId = null;

    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $role = '';

    public string $phone = '';

    /**
     * Load the form from an existing user
     *
     * @param  User  $user
     * @return void
     */
    public function load(User $user): void
    {
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->username = (string) $user->username;
        $this->email = (string) $user->email;
        $this->role = $user->getRoleNames()->first() ?? UserRole::Agent->value;
        $this->phone = (string) $user->phone;
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
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($this->userId)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'role' => ['required', Rule::in(UserRole::values())],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Validation attribute names, so the messages read "username" and not "edit form username"
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
