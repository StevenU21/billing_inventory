<?php

namespace App\Services;

use App\DTOs\EntityData;
use App\Models\Entity;
use App\Exceptions\BusinessLogicException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class EntityService
{
    public const RELATIONS = [
        'municipality.department',
    ];

    public function createEntity(EntityData $data): Entity
    {
        try {
            return DB::transaction(function () use ($data) {
                $entity = Entity::create([
                    'first_name' => $data->firstName,
                    'last_name' => $data->lastName,
                    'identity_card' => $data->identityCard,
                    'ruc' => $data->ruc,
                    'email' => $data->email,
                    'phone' => $data->phone,
                    'address' => $data->address,
                    'description' => $data->description,
                    'is_client' => $data->isClient,
                    'is_supplier' => $data->isSupplier,
                    'is_active' => $data->isActive,
                    'municipality_id' => $data->municipalityId,
                ]);

                $entity->load(self::RELATIONS);

                return $entity;
            });
        } catch (QueryException $e) {
            $this->handleConcurrencyError($e);
            throw $e;
        }
    }

    public function updateEntity(Entity $entity, EntityData $data): Entity
    {
        try {
            return DB::transaction(function () use ($entity, $data) {
                $lockedEntity = Entity::lockForUpdate()->find($entity->id);

                if (!$lockedEntity) {
                    throw new BusinessLogicException("La entidad que intentas editar ya no existe.");
                }

                $lockedEntity->update([
                    'first_name' => $data->firstName,
                    'last_name' => $data->lastName,
                    'identity_card' => $data->identityCard,
                    'ruc' => $data->ruc,
                    'email' => $data->email,
                    'phone' => $data->phone,
                    'address' => $data->address,
                    'description' => $data->description,
                    'is_client' => $data->isClient,
                    'is_supplier' => $data->isSupplier,
                    'is_active' => $data->isActive,
                    'municipality_id' => $data->municipalityId,
                ]);

                $lockedEntity->load(self::RELATIONS);

                return $lockedEntity;
            });
        } catch (QueryException $e) {
            $this->handleConcurrencyError($e);
            throw $e;
        }
    }

    public function toggleActive(Entity $entity): Entity
    {
        $entity->is_active = !$entity->is_active;
        $entity->save();

        return $entity;
    }

    private function handleConcurrencyError(QueryException $e): void
    {
        if ($e->getCode() === '23000') {
            throw new BusinessLogicException(
                "Ya existe un registro con estos datos (Cédula, RUC, etc).",
                'identity_card'
            );
        }
    }
}