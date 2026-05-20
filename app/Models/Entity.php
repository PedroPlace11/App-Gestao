<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entity extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id', 'number', 'type', 'nif', 'name', 'address', 'postal_code', 'city', 'country_id',
        'phone', 'mobile', 'website', 'email', 'rgpd_consent', 'observations', 'active',
    ];

    /**
     * Relationships
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'entity_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'entity_id');
    }

    public function supplierOrders()
    {
        return $this->hasMany(SupplierOrder::class, 'supplier_id');
    }

    public function invoicesAsSupplier()
    {
        return $this->hasMany(Invoice::class, 'supplier_id');
    }

    public function calendarEvents()
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }
}
