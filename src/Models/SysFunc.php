<?php

namespace Smart\Models;

use Illuminate\Database\Eloquent\Model;

class SysFunc extends Model {
	public $table = 'sys_func';

	public $primaryKey = 'id';

	public $timestamps = false;

	use \Smart\Traits\Service\Scope;

	public function scopeIsMenu($query, $param) {
		if ($param) {
			return $query->where('is_menu', $param);
		}

	}

	public function scopeModule($query, $param = '') {
		if ($param) {
			return $query->whereIn('module', (array)$param);
		}
	}

	public function sysRolePermissions() {
		return $this->belongsToMany(\Smart\Models\SysRolePermission::class, 'sys_func_privilege', 'id', 'func_id');
	}

	public function privilege() {
		return $this->hasMany(\Smart\Models\SysFuncPrivilege::class, 'func_id');
	}

	public function children(){
        return $this->hasMany( SysFunc::class , 'pid');
    }

    public function node(){
    	return $this->hasMany( SysPermissionNode::class, 'func_id');
    }

    public function permissionNode(){
    	return $this->belongsToMany(SysPermissionNode::class,'sys_func_privilege', 'func_id', 'node_id');
    }



}
