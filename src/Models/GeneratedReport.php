<?php
// src/Models/GeneratedReport.php
namespace DevForest\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedReport extends Model
{
    protected $fillable = ['name', 'slug', 'query', 'data_set'];
    protected $casts = [
        'data_set' => 'array'
    ];
}
