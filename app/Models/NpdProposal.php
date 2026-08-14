<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NpdProposal extends Model
{
    use HasFactory;

    protected $table = 'npd_proposals';

    protected $fillable = [
        'code',
        'prf_id',
        'product_name',
        'product_concept',
        'target_cogs',
        'target_selling_price',
        'development_start',
        'development_end',
        'pic',
        'project_team',
        'project_status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_cogs'         => 'decimal:2',
            'target_selling_price' => 'decimal:2',
            'development_start'   => 'date',
            'development_end'     => 'date',
        ];
    }

    public function prf(): BelongsTo
    {
        return $this->belongsTo(Prf::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(NpdProposalDocument::class);
    }

    public function getFormattedCogsAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->target_cogs, 0, ',', '.');
    }

    public function getFormattedSellingPriceAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->target_selling_price, 0, ',', '.');
    }
}