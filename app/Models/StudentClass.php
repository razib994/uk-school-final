<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    protected $fillable =['class_id','student_id'];

    public function classes(){
        return $this->belongsTo(Classe::class,'class_id','id');
    }

    public function student_subjects(){
        return $this->hasMany(StudentSubject::class,'student_id','student_id');
    }

}
