<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Especialidad extends Model 
{
 protected $fillable = ['nombre', 'imagen'];
 protected $table = 'especialidades';

 public function medicos(): HasMany
 {
     return $this->hasMany(Medico::class);
 }
}
