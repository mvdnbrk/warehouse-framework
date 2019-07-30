<?php

namespace Just\Warehouse\Models\Concerns;

trait ManagesPickList
{
    /**
     * Determine if a pick list is available.
     *
     * @return bool
     */
    public function hasPickList()
    {
        return $this->status == 'open';
    }

    /**
     * Retrieve a picklist.
     *
     * @return \Illuminate\Support\Collection
     */
    public function pickList()
    {
        if (! $this->hasPickList()) {
            return collect();
        }

        return $this->lines()
            ->select('id')
            ->with([
                'inventory' => function ($query) {
                    $query->select([
                        'inventories.id',
                        'inventories.gtin',
                        'inventories.location_id',
                    ]);
                },
                'inventory.location' => function ($query) {
                    $query->select([
                        'id',
                        'name',
                    ]);
                },
            ])
            ->get()
            ->map(function ($line) {
                return $line->inventory->makeHidden([
                    'id',
                    'location_id',
                ]);
            })
            ->groupBy([
                'gtin',
                'location.id',
            ])
            ->flatten(1)
            ->map(function ($item) {
                return collect($item->first())
                    ->forget('location')
                    ->put('location', $item->first()->location->name)
                    ->put('quantity', $item->count());
            })
            ->sortBy('location')
            ->values();
    }
}
