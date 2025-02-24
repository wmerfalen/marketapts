<?php

namespace App\Legacy;

use Illuminate\Database\Eloquent\Model;
use App\Legacy\State;
use App\Util\Util;
use App\Traits\LoadableByArray;
use App\Property\Entity;

class Property extends Model
{
    use LoadableByArray;
    public $timestamps = false;
    //
    protected $fillable = [
        'code',            //code               | varchar(50)      | NO   |     | NULL    |                |
        'name',            //               | varchar(50)      | NO   |     | NULL    |                |
        'address',        //address            | varchar(128)     | NO   |     | NULL    |                |
        'city',            //city               | varchar(50)      | NO   | MUL | NULL    |                |
        'state_id',        //state_id           | int(10) unsigned | NO   | MUL | NULL    |                |
        'zip',            //zip                | varchar(20)      | NO   |     | NULL    |                |
        'phone',        //phone              | varchar(20)      | NO   |     | NULL    |                |
        'fax',            //fax                | varchar(20)      | NO   |     | NULL    |                |
        'email',        //email              | varchar(128)     | NO   |     | NULL    |                |
        'image',        //image              | varchar(50)      | NO   |     | NULL    |                |
        'url',            //url                | varchar(150)     | NO   | MUL | NULL    |                |
        'price_range',    //price_range        | varchar(50)      | NO   |     | NULL    |                |
        'unit_type',    //unit_type          | varchar(50)      | NO   |     | NULL    |                |
        'special',        //special            | varchar(100)     | NO   |     | NULL    |                |
        'mercial',        //mercial            | varchar(150)     | NO   |     | NULL    |                |
        'description',    //description        | text             | NO   |     | NULL    |                |
        'hours',        //hours              | text             | NO   |     | NULL    |                |
        'pet_policy',    //pet_policy         | text             | NO   |     | NULL    |                |
        'directions',    //directions         | text             | NO   |     | NULL    |                |
        'featured',        //featured           | tinyint(1)       | NO   |     | 0       |                |
        'status_id',    //status_id          | int(10) unsigned | NO   | MUL | NULL    |                |
        'corporate_group_id'    //corporate_group_id | int(10) unsigned | NO   | MUL | 1       |                |
    ];

    protected $table = 'property';

    public function getState()
    {
        $foo = $this;
        return Util::redisFetchOrUpdate('property_state', function () use ($foo) {
            return State::select('name')->where('id', $foo->state_id)->get()->pluck('name')->toArray()[0];
        });
    }
    /**
    * @return returns a phone number based on entity given
    * @param $entity | \App\Property\Entity
    */
    public static function getPhoneByEntity(Entity $entity)
    {
        return Util::redisFetchOrUpdate(
            'legacy-phone',
            function () use ($entity) {
                $property = Property
                ::find($entity->fk_legacy_property_id);
                return ($property->phone);
            }
        );
    }
}
