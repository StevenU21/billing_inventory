<?php

namespace App\Traits;

/**
 * Trait HasFormattedTimestamps
 *
 * Proporciona atributos formateados para created_at y updated_at.
 *
 * Atributos disponibles:
 * - formatted_created_at: Fecha y hora (d/m/Y H:i)
 * - formatted_created_at_date: Solo fecha (d/m/Y)
 * - formatted_created_at_human: Formato relativo (hace 2 días)
 * - formatted_updated_at: Fecha y hora (d/m/Y H:i)
 * - formatted_updated_at_date: Solo fecha (d/m/Y)
 * - formatted_updated_at_human: Formato relativo (hace 2 días)
 */
trait HasFormattedTimestamps
{
    /**
     * Fecha y hora de creación formateada (d/m/Y H:i).
     */
    public function getFormattedCreatedAtAttribute(): ?string
    {
        return $this->created_at ? $this->created_at->format('d/m/Y H:i') : null;
    }

    /**
     * Solo fecha de creación (d/m/Y).
     */
    public function getFormattedCreatedAtDateAttribute(): ?string
    {
        return $this->created_at ? $this->created_at->format('d/m/Y') : null;
    }

    /**
     * Fecha de creación en formato relativo (hace X tiempo).
     */
    public function getFormattedCreatedAtHumanAttribute(): ?string
    {
        return $this->created_at ? $this->created_at->diffForHumans() : null;
    }

    /**
     * Fecha y hora de actualización formateada (d/m/Y H:i).
     */
    public function getFormattedUpdatedAtAttribute(): ?string
    {
        return $this->updated_at ? $this->updated_at->format('d/m/Y H:i') : null;
    }

    /**
     * Solo fecha de actualización (d/m/Y).
     */
    public function getFormattedUpdatedAtDateAttribute(): ?string
    {
        return $this->updated_at ? $this->updated_at->format('d/m/Y') : null;
    }

    /**
     * Fecha de actualización en formato relativo (hace X tiempo).
     */
    public function getFormattedUpdatedAtHumanAttribute(): ?string
    {
        return $this->updated_at ? $this->updated_at->diffForHumans() : null;
    }
}