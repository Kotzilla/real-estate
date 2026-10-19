<?php

namespace App\Livewire\People;

use App\Enums\PersonType;
use App\Models\Person;
use App\Traits\ResetsFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * People Table Livewire Component
 *
 * The contacts: owners, customers and outside agents (the sidebar's links filter by type). Search by name in either
 * language, phone, email, ID or passport number; the name opens the contact's page.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Livewire
 */
class Table extends Component
{
    use ResetsFilters, WithPagination;

    // the search cleared by the Reset button (ResetsFilters); type is the sidebar's, so it stays
    protected const FILTERS = ['q'];

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: 'all')]
    public string $type = 'all';

    /**
     * Back to the first page whenever the search or the type changes
     *
     * @return void
     */
    public function updated(): void
    {
        $this->resetPage();
    }

    /**
     * The contacts matching the search and type, by name
     *
     * @return LengthAwarePaginator<int, Person>
     */
    protected function getPeople(): LengthAwarePaginator
    {
        return Person::query()
            ->withCount(['properties', 'contracts'])
            ->when($this->q !== '', function (Builder $query) {
                $term = '%'.$this->q.'%';
                $phone = preg_replace('/[\s-]/', '', $this->q);
                $query->where(fn (Builder $sub) => $sub->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('first_name_th', 'like', $term)
                    ->orWhere('last_name_th', 'like', $term)
                    ->orWhere('nickname', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('id_card_no', 'like', $term)
                    ->orWhere('passport_no', 'like', $term)
                    ->when($phone !== '', fn (Builder $q) => $q->orWhere('phone', 'like', '%'.$phone.'%')));
            })
            ->when(in_array($this->type, PersonType::values(), true), fn (Builder $query) => $query->where('type', $this->type))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(25);
    }

    /**
     * Render the Livewire component
     *
     * @return View
     */
    public function render(): View
    {
        Gate::authorize('viewAny', Person::class);

        return view('livewire.people.table', ['people' => $this->getPeople()]);
    }

    /**
     * Pagination view used by this table
     *
     * @return string
     */
    public function paginationView(): string
    {
        return 'vendor.pagination.kt-livewire';
    }
}
