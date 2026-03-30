<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'description'])]
class PostTag extends Model
{
    /** @use HasFactory<\Database\Factories\PostTagFactory> */
    use HasFactory;
}
