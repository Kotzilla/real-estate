<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Log Activity
 *
 * Records a back-office action in the activity log (spatie/laravel-activitylog), as in the elib project: who did it
 * (the signed-in user), what on (the model), an action code ("property-update"), and the request's IP, browser and
 * device. When the model was just updated, its changed columns are kept as old / new values (never
 * passwords), plus any old / new values the caller passes (a list it links to). Call it after the change:
 * `$this->log($property, 'property-update')`.
 *
 * @package App\Traits
 */
trait LogActivity
{
    /**
     * Log the action on the model, with extra details that are added to (or replace) the request ones
     *
     * @param  Model|null  $model
     * @param  string  $code
     * @param  array<string, mixed>  $attrs
     * @param  array<string, array{0: mixed, 1: mixed}>  $extraChanges  more old / new values, e.g. a list it links to: ['gifts' => [old, new]]
     * @return void
     */
    protected function log(?Model $model, string $code, array $attrs = [], array $extraChanges = []): void
    {
        $agent = (string) request()->userAgent();
        $logger = activity()
            ->causedBy(auth()->user())
            ->withProperties(array_replace([
                'ip' => request()->ip(),
                'browser' => $agent,
                'agent' => $this->device($agent),
            ], $attrs));

        if ($model) {
            $logger->performedOn($model);

            // an update: the columns that changed, old and new, plus the caller's own (lists the model links to)
            $changes = Arr::except($model->getChanges(), ['updated_at', 'password', 'remember_token']);
            $old = Arr::only($model->getPrevious(), array_keys($changes));
            foreach ($extraChanges as $field => [$before, $after]) {
                $old[$field] = $before;
                $changes[$field] = $after;
            }
            if ($changes) {
                $logger->withChanges(['attributes' => $changes, 'old' => $old]);
            }
        }

        $logger->log($code);
    }

    /**
     * The kind of device from the user agent: desktop, mobile or tablet
     *
     * @param  string  $agent
     * @return string
     */
    protected function device(string $agent): string
    {
        // ponytail: a user-agent sniff, enough for a log; iPads that ask for the desktop site count as desktop
        return match (true) {
            (bool) preg_match('/iPad|Tablet|Android(?!.*Mobi)/i', $agent) => 'tablet',
            (bool) preg_match('/Mobi|iPhone|iPod/i', $agent) => 'mobile',
            default => 'desktop',
        };
    }
}
