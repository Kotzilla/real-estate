<?php

namespace App\Http\Controllers;

use App\Enums\ContractStatus;
use App\Enums\ContractType;
use App\Enums\PropertyStatus;
use App\Models\Contract;
use App\Models\Property;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Dashboard Controller
 *
 * The back office's front page: what is on the market, what is let, the leases ending soon and this year's
 * commission. Each block shows only to users who can see that module.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Controllers
 */
class DashboardController extends Controller
{
    // a lease ending within this many days shows in "Ending soon", so the agent can offer a renewal
    public const ENDING_DAYS = 60;

    /**
     * Show the dashboard
     *
     * @return View
     */
    public function __invoke(): View
    {
        $canProperties = Gate::allows('viewAny', Property::class);
        $canContracts = Gate::allows('viewAny', Contract::class);
        $available = Property::query()->where('status', PropertyStatus::Available->value);
        $active = Contract::query()->where('status', ContractStatus::Active->value);

        return view('dashboard', [
            'canProperties' => $canProperties,
            'canContracts' => $canContracts,
            'stats' => [
                'for_sale' => $canProperties ? (clone $available)->where('is_sale', true)->count() : null,
                'for_rent' => $canProperties ? (clone $available)->where('is_rent', true)->count() : null,
                'rented' => $canContracts ? (clone $active)->where('type', ContractType::Rent->value)->count() : null,
                'commission' => $canContracts ? (float) Contract::query()
                    ->whereIn('status', [ContractStatus::Active->value, ContractStatus::Completed->value])
                    ->whereYear('signed_on', now()->year)->sum('commission') : null,
            ],
            'ending' => $canContracts ? (clone $active)->where('type', ContractType::Rent->value)
                ->whereBetween('end_on', [today(), today()->addDays(self::ENDING_DAYS)])
                ->with(['property', 'customer'])->orderBy('end_on')->limit(10)->get() : collect(),
            'drafts' => $canContracts ? Contract::query()->where('status', ContractStatus::Draft->value)
                ->with(['property', 'customer'])->latest()->limit(10)->get() : collect(),
        ]);
    }
}
