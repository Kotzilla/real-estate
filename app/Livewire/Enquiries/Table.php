<?php

namespace App\Livewire\Enquiries;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Models\Property;
use App\Traits\ResetsFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Enquiries Table Livewire Component
 *
 * Viewing requests left on the public listing pages, newest first. Search by name, phone or listing, filter by status;
 * the name opens the enquiry's page (status and follow-up notes).
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Livewire
 */
class Table extends Component
{
    use ResetsFilters, WithPagination;

    // search and filters cleared by the Reset button (ResetsFilters)
    protected const FILTERS = ['q', 'status'];

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: 'all')]
    public string $status = 'all';

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
     * The enquiries matching the search and filter
     *
     * @return LengthAwarePaginator<int, Enquiry>
     */
    protected function getEnquiries(): LengthAwarePaginator
    {
        return Enquiry::query()
            ->with('enquirable')
            ->withCount('comments')
            ->when($this->q !== '', function (Builder $query) {
                $term = '%'.$this->q.'%';
                $query->where(fn (Builder $sub) => $sub->where('name', 'like', $term)->orWhere('phone', 'like', $term)
                    ->orWhereHasMorph('enquirable', [Property::class], fn (Builder $p) => $p->where('code', 'like', $term)->orWhere('name', 'like', $term)));
            })
            ->when(in_array($this->status, EnquiryStatus::values(), true), fn (Builder $query) => $query->where('status', $this->status))
            ->latest()
            ->latest('id')
            ->paginate(25);
    }

    /**
     * Render the Livewire component
     *
     * @return View
     */
    public function render(): View
    {
        Gate::authorize('viewAny', Enquiry::class);

        return view('livewire.enquiries.table', ['enquiries' => $this->getEnquiries()]);
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
