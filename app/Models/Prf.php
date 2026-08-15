<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\NpdProposal;

class Prf extends Model
{
    use HasFactory;

    protected $table = 'prfs';

    protected $fillable = [
        'code',
        'product_concept',
        'target_market',
        'product_category',
        'target_launch',
        'product_name',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_launch' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PrfDocument::class);
    }

    public function npdProposals(): HasMany
    {
        return $this->hasMany(NpdProposal::class);
    }
}