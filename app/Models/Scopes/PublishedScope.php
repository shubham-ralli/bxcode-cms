<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PublishedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // If running in console (artisan) or admin panel, show everything
        // Assumption: Admin routes start with 'admin' or checked via Auth role
        if (app()->runningInConsole() || request()->is('admin/*') || request()->routeIs('admin.*')) {
            return;
        }

        // Check if user is logged in and has permission (e.g. admin)
        // For now, if logged in, show all (simplification based on user request "only login user show page")
        if (auth()->check()) {
            return;
        }

        // Guest logic:
        // Show only if published_at is NULL (interpreted as immediate) OR published_at <= NOW
        $builder->where(function ($query) {
            $query->whereNull('published_at')
                ->orWhere('published_at', '<=', now());
        });
    }
}
