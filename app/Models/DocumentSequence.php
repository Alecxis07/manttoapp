<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class DocumentSequence extends Model
{
    use Auditable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'document_type',
        'prefix',
        'year',
        'last_number',
        'padding',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'last_number' => 'integer',
            'padding' => 'integer',
        ];
    }
}
