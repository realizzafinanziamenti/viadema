<?php

namespace App\Livewire\Admin\Trash;

use App\Enums\CustomerStatus;
use App\Enums\PracticeStatus;
use App\Models\Customer;
use App\Models\Practice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Throwable;

class TrashIndex extends Component
{
    public function mount(): void
    {
        Gate::authorize('access trash');
    }

    #[Computed]
    public function trashItems(): array
    {
        return $this->trashQuery()
            ->get()
            ->map(
                fn (object $row): array => $this->presentTrashItem($row)
            )
            ->values()
            ->all();
    }

    private function trashQuery(): QueryBuilder
    {
        $union = $this->customerTrashQuery()
            ->unionAll(
                $this->practiceTrashQuery()
            );

        return DB::query()
            ->fromSub(
                $union,
                'trash_items'
            )
            ->orderByDesc('deleted_at')
            ->orderByDesc('id');
    }

    private function customerTrashQuery(): QueryBuilder
    {
        $query = DB::table('customers as item')
            ->leftJoin(
                'users as owner',
                'owner.id',
                '=',
                'item.user_id'
            )
            ->leftJoin(
                'users as deleter',
                'deleter.id',
                '=',
                'item.deleted_by'
            )
            ->whereNotNull('item.deleted_at')
            ->whereIn(
                'item.customer_status',
                [
                    CustomerStatus::CUSTOMER->value,
                    CustomerStatus::LEAD->value,
                ]
            );

        $this->applyTrashVisibility(
            $query,
            'item.deleted_by'
        );

        return $query
            ->select([
                'item.id',
                'item.deleted_at',
                'item.first_name',
                'item.last_name',
                'item.tax_id',
                'item.phone',
                'owner.first_name as owner_first_name',
                'owner.last_name as owner_last_name',
                'deleter.first_name as deleter_first_name',
                'deleter.last_name as deleter_last_name',
                'item.customer_status as entity_type',
            ])
            ->selectRaw(
                'NULL AS practice_status'
            );
    }

    private function practiceTrashQuery(): QueryBuilder
    {
        $query = DB::table('practices as item')
            ->leftJoin(
                'customers as customer',
                'customer.id',
                '=',
                'item.customer_id'
            )
            ->leftJoin(
                'users as owner',
                'owner.id',
                '=',
                'item.user_id'
            )
            ->leftJoin(
                'users as deleter',
                'deleter.id',
                '=',
                'item.deleted_by'
            )
            ->whereNotNull('item.deleted_at');

        $this->applyTrashVisibility(
            $query,
            'item.deleted_by'
        );

        return $query
            ->select([
                'item.id',
                'item.deleted_at',
                'customer.first_name',
                'customer.last_name',
                'customer.tax_id',
                'customer.phone',
                'owner.first_name as owner_first_name',
                'owner.last_name as owner_last_name',
                'deleter.first_name as deleter_first_name',
                'deleter.last_name as deleter_last_name',
            ])
            ->selectRaw(
                '? AS entity_type',
                ['practice']
            )
            ->addSelect(
                'item.practice_status as practice_status'
            );
    }

    private function applyTrashVisibility(
        QueryBuilder $query,
        string $deletedByColumn
    ): void {
        if (
            auth()
                ->user()
                ->can('view all trash')
        ) {
            return;
        }

        $query->where(
            $deletedByColumn,
            auth()->id()
        );
    }

    public function restore(
        string $type,
        int $id
    ): void {
        $entity = $this->findTrashedEntity(
            $type,
            $id
        );

        Gate::authorize(
            'restore',
            $entity
        );

        try {
            DB::transaction(
                function () use ($entity): void {
                    $entity->restore();

                    $entity
                        ->forceFill([
                            'deleted_by' => null,
                        ])
                        ->saveQuietly();
                }
            );

            Toaster::success(
                'Elemento ripristinato con successo'
            );

            $this->dispatch(
                'trash-item-removed',
                type: $type,
                id: $id
            );

            $this->skipRender();
        } catch (Throwable $e) {
            $this->logTrashError(
                'restore',
                $entity,
                $e
            );

            Toaster::error(
                'Errore durante il ripristino. Riprova più tardi.'
            );

            $this->skipRender();
        }
    }

    public function forceDelete(
        string $type,
        int $id
    ): void {
        $entity = $this->findTrashedEntity(
            $type,
            $id
        );

        Gate::authorize(
            'forceDelete',
            $entity
        );

        try {
            DB::transaction(
                fn () => $entity->forceDelete()
            );

            Toaster::success(
                'Elemento eliminato definitivamente'
            );

            $this->dispatch(
                'trash-item-removed',
                type: $type,
                id: $id
            );

            $this->skipRender();
        } catch (Throwable $e) {
            $this->logTrashError(
                'force-delete',
                $entity,
                $e
            );

            Toaster::error(
                'Impossibile eliminare definitivamente l\'elemento.'
            );

            $this->skipRender();
        }
    }

    private function findTrashedEntity(
        string $type,
        int $id
    ): Customer|Practice {
        return match ($type) {
            'customer' => Customer::onlyTrashed()
                ->customers()
                ->findOrFail($id),

            'lead' => Customer::onlyTrashed()
                ->leads()
                ->findOrFail($id),

            'practice' => Practice::onlyTrashed()
                ->findOrFail($id),

            default => abort(404),
        };
    }

    private function presentTrashItem(
        object $row
    ): array {
        $name = $this->formatFullName(
            $row->first_name,
            $row->last_name
        );

        $owner = $this->formatFullName(
            $row->owner_first_name,
            $row->owner_last_name
        );

        $deletedBy = $this->formatFullName(
            $row->deleter_first_name,
            $row->deleter_last_name
        );

        $deletedAt = Carbon::parse(
            $row->deleted_at
        );

        return [
            'id' => (int) $row->id,

            'entity_type' => $row->entity_type,

            'type_label' => match ($row->entity_type) {
                'practice' => 'Pratica',
                'lead' => 'Lead',
                default => 'Anagrafica',
            },

            'title' => $row->entity_type === 'practice'
                ? 'P' . str_pad(
                    (string) $row->id,
                    5,
                    '0',
                    STR_PAD_LEFT
                )
                : $name,

            'detail' => match ($row->entity_type) {
                'practice' => $name,
                'lead' => $row->phone ?: 'N/D',
                default => $row->tax_id
                    ?: ($row->phone ?: 'N/D'),
            },

            'source' => $row->entity_type === 'practice'
                ? (
                    $row->practice_status
                    === PracticeStatus::DISBURSED->value
                        ? 'Archivio Pratiche'
                        : 'Gestione Pratiche'
                )
                : null,

            'owner_name' => $owner,

            'deleted_by_name' => $deletedBy,

            'deleted_at' => $deletedAt
                ->format('d/m/Y H:i'),

            'purge_at' => $deletedAt
                ->copy()
                ->addDays(30)
                ->format('d/m/Y'),
        ];
    }

    private function formatFullName(
        ?string $firstName,
        ?string $lastName
    ): string {
        $name = trim(
            implode(
                ' ',
                array_filter([
                    $firstName,
                    $lastName,
                ])
            )
        );

        return $name !== ''
            ? $name
            : 'N/D';
    }

    private function logTrashError(
        string $action,
        Model $entity,
        Throwable $exception
    ): void {
        Log::error(
            "Errore cestino durante {$action}",
            [
                'model' => $entity::class,
                'model_id' => $entity->getKey(),
                'user_id' => auth()->id(),
                'exception' => $exception,
            ]
        );
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view(
            'livewire.admin.trash.trash-index'
        );
    }
}