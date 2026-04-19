<?php

namespace App\Models;

class AuditLog extends AuditTrail
{
    /**
     * Return the audited table name alias.
     */
    public function getTableNameAttribute(): string
    {
        return $this->auditable_type;
    }

    public function getOldDataAttribute(): ?array
    {
        return $this->old_values;
    }

    public function getNewDataAttribute(): ?array
    {
        return $this->new_values;
    }
}
