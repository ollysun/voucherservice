<?php namespace Voucher\Repositories;

use Voucher\Providers\Fractal as Fractal;
use League\Fractal\Manager;
use Illuminate\Support\Facades\Input;
use App;

abstract class AbstractRepository
{
    public static function setPaginationLinks($paginator, $params)
    {
        $paginator->setPageName('offset')->appends(array_except($params, 'offset'));
        return $paginator;
    }

    public static function setFractal()
    {
        $manager = new Manager();
        $get = Input::get('include');
        if ($get) {
            $manager = $manager->parseIncludes($get);
        }
        $fractal = new Fractal($manager);
        return $fractal;
    }

    protected static function transform($model, $transformer)
    {
        if ($model instanceof \Illuminate\Database\Eloquent\Collection) {
            $method = 'respondWithCollection';
        } elseif ($model instanceof \Illuminate\Pagination\AbstractPaginator) {
            $method = 'respondWithPaginatedCollection';
        } elseif ($model instanceof \Illuminate\Database\Eloquent\Model) {
            $method = 'respondWithItem';
        } else {
            throw new \Exception('Something went wrong');
        }
        $rootScope = self::setFractal()->$method($model, $transformer);
        return $rootScope->toArray();
    }
}
