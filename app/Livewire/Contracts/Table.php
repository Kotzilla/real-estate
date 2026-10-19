<?php

namespace App\Livewire\Contracts;

use App\Enums\ContractStatus;
use App\Enums\ContractType;
use App\Models\Contract;
use App\Traits\ResetsFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Contracts Table Livewire Component
 *
 * Leases and sales: search by code, property or customer, filter by type and status, sort by date signed (newest
 * first by default), end date or commission. The code opens the contract; the row prints it in English or Thai.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Livewire
 */
class Table extends Component
{
    use ResetsFilters, WithPagination;

    // search and filters cleared by the Reset button (ResetsFilters)
    protected const FILTERS = ['q', 'type', 'status', 'sort', 'dir'];

    // columns the headers sort by: property value => query column
    protected const SORTS = ['signed' => 'signed_on', 'end' => 'end_on', 'commission' => 'commission'];

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: 'all')]
    public string $type = 'all';

    #[Url(except: 'all')]
    public string $status = 'all';

    public string $sort = 'signed';

    public string $dir = 'desc';

    /**
     * Back to the first page whenever the search or a filter changes
     *
     * @return void
     */
    public function updated(): void
    {
        $this->resetPage();
    }

    /**
     * Sort by a column: a first click sorts ascending, another click on the same column flips the direction
     *
     * @param  string  $field
     * @return void
     */
    public function sortBy(string $field): void
    {
        if (! isset(self::SORTS[$field])) {
            return;
        }

        $this->dir = $this->sort === $field && $this->dir === 'asc' ? 'desc' : 'asc';
        $this->sort = $field;
        $this->resetPage();
    }

    /**
     * The contracts matching the search and filters
     *
     * @return LengthAwarePaginator<int, Contract>
     */
    protected function getContracts(): LengthAwarePaginator
    {
        return Contract::query()
            ->with(['property', 'customer', 'agent'])
            ->when($this->q !== '', function (Builder $query) {
                $term = '%'.$this->q.'%';
                $query->where(fn (Builder $sub) => $sub->where('code', 'like', $term)
                    ->orWhereHas('property', fn (Builder $p) => $p->where('code', 'like', $term)->orWhere('name', 'like', $term))
                    ->orWhereHas('customer', fn (Builder $c) => $c->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)->orWhere('first_name_th', 'like', $term)));
            })
            ->when(in_array($this->type, ContractType::values(), true), fn (Builder $query) => $query->where('type', $this->type))
            ->when(in_array($this->status, ContractStatus::values(), true), fn (Builder $query) => $query->where('status', $this->status))
            ->orderBy(self::SORTS[$this->sort] ?? 'signed_on', $this->dir === 'asc' ? 'asc' : 'desc')
            ->orderByDesc('id')
            ->paginate(25);
    }

    /**
     * Render the Livewire component
     *
     * @return View
     */
    public function render(): View
    {
        Gate::authorize('viewAny', Contract::class);

        return view('livewire.contracts.table', ['contracts' => $this->getContracts()]);
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
