<?php

namespace App\Concerns;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCredentials
{
    use HasRelationships;
    
    /**
     * Get the authentication credentials for the model.
     * The model must have a `user_id` column as a foreign key to the `users` table.
     *
     * @return BelongsTo
     */
    public function credentials(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
