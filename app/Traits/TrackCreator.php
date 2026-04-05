<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

trait TrackCreator
{
    /**
     * Boot the trait to automatically assign the authenticated user's ID
     * to the created_by attribute when creating a model.
     */
    protected static function bootTrackCreator()
    {
        static::creating(function ($model) {
            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::id();
            }
        });
    }

    /**
     * Relationship: Get the user who created the record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
