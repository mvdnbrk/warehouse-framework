<?php

namespace Just\Warehouse\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Just\Warehouse\Events\OrderLineReplaced;
use Just\Warehouse\Models\States\Order\Hold;
use LogicException;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * @property int $id
 * @property int $order_id
 * @property string $gtin
 * @property \Just\Warehouse\Models\Order $order
 * @property \Just\Warehouse\Models\Inventory $inventory
 */
class OrderLine extends AbstractModel
{
    use Concerns\Reservable,
        HasRelationships;

    protected $casts = [
        'order_id' => 'integer',
    ];

    public $timestamps = false;

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withTrashed();
    }

    public function location(): HasOneDeep
    {
        return $this->hasOneDeepFromRelations(
                $this->inventory(),
                (new Inventory)->location()
            )
            ->withTrashed('inventories.deleted_at');
    }

    public function inventory(): HasOneThrough
    {
        return $this->hasOneThrough(
                Inventory::class,
                Reservation::class,
                'order_line_id',
                'id',
                'id',
                'inventory_id'
            )
            ->withTrashed();
    }

    /**
     * Replace this order line.
     *
     * @return \Just\Warehouse\Models\OrderLine
     *
     * @throws \LogicException
     */
    public function replace()
    {
        if (! $this->isFulfilled()) {
            throw new LogicException('This order line can not be replaced.');
        }

        if ($this->order->status->isOpen()) {
            $this->order->status->transitionTo(Hold::class);
        }

        return tap($this->order->addLine($this->gtin), function ($line) {
            $this->inventory->update([
                'deleted_at' => $this->freshTimeStamp(),
            ]);

            $this->delete();

            if ($this->order->status->isHold()) {
                $this->order->process();
            }

            OrderLineReplaced::dispatch($this->order, $this->inventory, $line);
        });
    }
}
