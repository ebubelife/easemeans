<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Members extends Model
{
    use HasFactory;
    protected $table = "easemeans_members";

    protected $fillable = [
        'bvn',
        'bvn_verification_attempts',
        // other fields
    ];
}
