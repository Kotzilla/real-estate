<?php

namespace App\Livewire\Properties;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Models\Province;
use App\Traits\ResetsFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Properties Table Livewire Component
 *
 * The listings: search by code, name, area or owner, filter by sale / rent (the sidebar's links), type, status and
 * province, sort by code, price or last change (newest first by default). The name opens the listing's page.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Livewire
 */
class Table extends Component
{
    use ResetsFilters, WithPagination;

    // search and filters cleared by the Reset button (ResetsFilters); offer is the sidebar's, so it stays
    protected const FILTERS = ['q', 'type', 'status', 'province', 'sort', 'dir'];

    // columns the headers sort by: property value => query column
    protected const SORTS = ['code' => 'code', 'sale' => 'sale_price', 'rent' => 'rent_price', 'updated' => 'updated_at'];

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: 'all')]
    public string $offer = 'all'; // all | sale | rent

    #[Url(except: 'all')]
    public string $type = 'all';

    #[Url(except: 'all')]
    public string $status = 'all';

    #[Url(except: 'all')]
    public string $province = 'all';

    public string $sort = 'updated';

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
     * The listings matching the search and filters
     *
     * @return LengthAwarePaginator<int, Property>
     */
    protected function getProperties(): LengthAwarePaginator
    {
        return Property::query()
            ->with(['coverPhoto', 'owner', 'province', 'district'])
            ->when($this->q !== '', function (Builder $query) {
                $term = '%'.$this->q.'%';
                $query->where(fn (Builder $sub) => $sub->where('code', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('name_th', 'like', $term)
                    ->orWhere('location', 'like', $term)
                    ->orWhereHas('owner', fn (Builder $owner) => $owner->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)->orWhere('first_name_th', 'like', $term)));
            })
            ->when($this->offer === 'sale', fn (Builder $query) => $query->where('is_sale', true))
            ->when($this->offer === 'rent', fn (Builder $query) => $query->where('is_rent', true))
            ->when(in_array($this->type, PropertyType::values(), true), fn (Builder $query) => $query->where('type', $this->type))
            ->when(in_array($this->status, PropertyStatus::values(), true), fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->province !== 'all', fn (Builder $query) => $query->where('province_id', (int) $this->province))
            ->orderBy(self::SORTS[$this->sort] ?? 'updated_at', $this->dir === 'asc' ? 'asc' : 'desc')
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
        Gate::authorize('viewAny', Property::class);

        return view('livewire.properties.table', [
            'properties' => $this->getProperties(),
            'provinces' => Province::query()->orderBy('name')->get(),
        ]);
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
