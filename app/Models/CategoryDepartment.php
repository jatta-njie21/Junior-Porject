<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryDepartment extends Model
{
    use SoftDeletes;
	
	protected $table = 'categorydepartments';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public function categories() {
	    return $this->hasMany('\App\Models\Category');
    }
}
