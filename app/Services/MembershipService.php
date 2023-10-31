<?php

namespace App\Services;

use App\Models\Membership;
use Illuminate\Database\Eloquent\Builder;

class MembershipService
{
    /**
     * Find a membership by its ID.
     *
     * @param mixed $id
     * @param array{
     *     throwable?: bool,
     *     with_product?: bool,
     *     with_campaign?: bool,
     * } $options
     * @return Membership|null
     */
    public function find(mixed $id, array $options = []): ?Membership
    {
        $membership = Membership::when($options['throwable'] ?? true, function (Builder $query) use ($id) {
            return $query->findOrFail($id);
        }, function (Builder $query) use ($id) {
            return $query->find($id);
        });

        if ($membership) {
            // Eager load the associated product if requested.
            if ($options['with_product'] ?? false) {
                $membership->load('product');
            }

            // Eager load the associated campaign if requested.
            if ($options['with_campaign'] ?? false) {
                $membership->load('campaign');
            }
        }

        return $membership;
    }

}
