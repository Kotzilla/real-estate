<?php

namespace App\Livewire\Layout;

use App\Models\Contract;
use App\Models\Enquiry;
use App\Models\Person;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Global Search
 *
 * Modal search across properties, people, contracts and enquiries.
 * One tab per entity with a hit count, restricted to the tabs this user has permissions to see.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Livewire
 */
class Search extends Component
{
    public const MIN_KEYWORD = 2;

    private const LIMIT = 10;

    public string $keyword = '';

    public string $tab = 'properties';

    /**
     * Switch the active search result tab
     *
     * @param  string  $name
     * @return void
     */
    public function switchTab(string $name): void
    {
        $this->tab = $name;
    }

    /**
     * Clear the search keyword
     *
     * @return void
     */
    public function clear(): void
    {
        $this->keyword = '';
    }

    /**
     * Query entities visible to the user matching the keyword
     *
     * @param  User  $user
     * @param  string  $keyword
     * @return array<string, Collection>
     */
    private function search(User $user, string $keyword): array
    {
        $like = '%'.addcslashes($keyword, '%_\\').'%';
        $groups = [];

        if ($user->can('properties.view')) {
            $groups['properties'] = Property::query()
                ->with(['district', 'owner'])
                ->where(function ($q) use ($like) {
                    $q->where('code', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('name_th', 'like', $like)
                        ->orWhere('location', 'like', $like);
                })
                ->limit(self::LIMIT)
                ->get();
        }

        if ($user->can('people.view')) {
            $groups['people'] = Person::query()
                ->where(function ($q) use ($like) {
                    $q->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('first_name_th', 'like', $like)
                        ->orWhere('last_name_th', 'like', $like)
                        ->orWhere('nickname', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('email', 'like', $like);
                })
                ->limit(self::LIMIT)
                ->get();
        }

        if ($user->can('contracts.view')) {
            $groups['contracts'] = Contract::query()
                ->with(['property', 'customer'])
                ->where(function ($q) use ($like) {
                    $q->where('code', 'like', $like)
                        ->orWhereHas('customer', function ($c) use ($like) {
                            $c->where('first_name', 'like', $like)
                                ->orWhere('last_name', 'like', $like)
                                ->orWhere('first_name_th', 'like', $like)
                                ->orWhere('last_name_th', 'like', $like);
                        })
                        ->orWhereHas('property', function ($p) use ($like) {
                            $p->where('code', 'like', $like)
                                ->orWhere('name', 'like', $like)
                                ->orWhere('name_th', 'like', $like);
                        });
                })
                ->limit(self::LIMIT)
                ->get();
        }

        if ($user->can('enquiries.view')) {
            $groups['enquiries'] = Enquiry::query()
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('message', 'like', $like);
                })
                ->limit(self::LIMIT)
                ->get();
        }

        return $groups;
    }

    /**
     * Render the search modal with hits per allowed tab
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        $user = auth()->user();
        $keyword = trim($this->keyword);
        $ready = mb_strlen($keyword) >= self::MIN_KEYWORD;
        $groups = ($ready && $user) ? $this->search($user, $keyword) : [];
        $counts = array_map(fn (Collection $items) => $items->count(), $groups);

        $allowed = array_key_exists($this->tab, $counts);
        $active = ($allowed && ($counts[$this->tab] > 0 || empty(array_filter($counts))))
            ? $this->tab
            : (array_key_first(array_filter($counts)) ?? (array_key_first($counts) ?? 'properties'));

        return view('livewire.layout.search', [
            'ready' => $ready,
            'counts' => $counts,
            'active' => $active,
            'items' => $groups[$active] ?? collect(),
            'total' => array_sum($counts),
        ]);
    }
}
