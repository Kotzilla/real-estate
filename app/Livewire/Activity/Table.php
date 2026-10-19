<?php

namespace App\Livewire\Activity;

use App\Traits\ResetsFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

/**
 * Activity Table Livewire Component
 *
 * The activity log (App\Traits\LogActivity), newest first: who did what to which record, the old → new values of an
 * update, and the IP and device it came from. Search by user name / username or IP, filter by action.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Livewire
 */
class Table extends Component
{
    use ResetsFilters, WithPagination;

    // search and filters cleared by the Reset button (ResetsFilters)
    protected const FILTERS = ['q', 'action'];

    public string $q = '';

    public string $action = 'all';

    /**
     * URL query string parameters
     *
     * @var array<string, array<string, string>>
     */
    protected $queryString = [
        'q' => ['except' => ''],
        'action' => ['except' => 'all'],
    ];

    /**
     * Back to the first page when the search or filter changes
     *
     * @return void
     */
    public function updated(): void
    {
        $this->resetPage();
    }

    /**
     * The log entries matching the search and action, newest first
     *
     * @return LengthAwarePaginator<int, Activity>
     */
    protected function getActivities(): LengthAwarePaginator
    {
        return Activity::query()
            ->with(['causer', 'subject'])
            ->when($this->action !== 'all', fn (Builder $query) => $query->where('description', $this->action))
            ->when($this->q !== '', function (Builder $query) {
                $term = '%'.trim($this->q).'%';
                $query->where(function (Builder $sub) use ($term) {
                    $sub->where('properties->ip', 'like', $term)
                        ->orWhereHasMorph('causer', '*', fn (Builder $user) => $user->where('name', 'like', $term)->orWhere('username', 'like', $term));
                });
            })
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
        Gate::authorize('viewAny', Activity::class);

        return view('livewire.activity.table', [
            'activities' => $this->getActivities(),
            'actions' => Activity::query()->distinct()->orderBy('description')->pluck('description'),
            'labels' => __('activity.actions'),
        ]);
    }

    /**
     * Use the theme's pagination
     *
     * @return string
     */
    public function paginationView(): string
    {
        return 'vendor.pagination.kt-livewire';
    }
}
