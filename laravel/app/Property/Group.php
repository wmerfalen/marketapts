<?php

namespace App\Property;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    //
    protected $fillable = [
        'str_identifier',
        'group_name',
        'active_status'
    ];

    protected $table = 'property_group';

    public static function generateStrIdentifier($name)
    {
        $cleaned = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
        $cleaned = preg_replace('/\-{2,}/', '-', $cleaned);
        return substr($cleaned, 0, 16);
    }
}
//mysql -u amcapartments -h 192.168.1.140 -pUm5AvWNV amcapartments_com > amcapartments_com.sql
