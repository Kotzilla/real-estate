<?php

namespace App\Livewire;

use App\Traits\LogActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Comments Livewire Component
 *
 * Staff notes on any model with App\Traits\HasComments, newest first, and a box to add one:
 * `@livewire('comments', ['model' => $enquiry])`. Reading needs the model's `view` policy, adding its `update`.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Livewire
 */
class Comments extends Component
{
    use LogActivity;

    public Model $model;

    public string $body = '';

    /**
     * Validation rules for a note
     *
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return ['body' => ['required', 'string', 'max:2000']];
    }

    /**
     * Validation attribute names, as the form labels them
     *
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return ['body' => __('comments.body')];
    }

    /**
     * Add the note, signed by the current user
     *
     * @return void
     */
    public function add(): void
    {
        $this->authorize('update', $this->model);
        $this->validate();

        $this->model->comments()->create(['user_id' => auth()->id(), 'body' => trim($this->body)]);
        $this->log($this->model, 'comment-create');
        $this->reset('body');
        $this->dispatch('toast', type: 'success', text: __('comments.added'));
    }

    /**
     * Render the Livewire component
     *
     * @return View
     */
    public function render(): View
    {
        Gate::authorize('view', $this->model);

        return view('livewire.comments', ['comments' => $this->model->comments()->with('user')->get()]);
    }
}
