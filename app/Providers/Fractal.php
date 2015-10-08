<?php namespace Voucher\Providers;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Illuminate\Pagination\AbstractPaginator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class Fractal
{
    public function __construct(Manager $fractal)
    {
        $this->fractal = $fractal;
        // @todo: Check this out and see if we can make it safer, used to embed relations
        // $this->fractal->setRecursionLimit(explode(',', Input::get('embed')));
    }

    public function respondWithItem($item, $callback)
    {
        $resource = new Item($item, $callback);
        $rootScope = $this->fractal->createData($resource);

        return $rootScope;
    }

    public function respondWithCollection($collection, $callback)
    {
        $resource = new Collection($collection, $callback);
        $rootScope = $this->fractal->createData($resource);

        return $rootScope;
    }

    public function respondWithPaginatedCollection(AbstractPaginator $paginator, $callback)
    {
        $resource = new Collection($paginator->getCollection(), $callback);
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        $rootScope = $this->fractal->createData($resource);

        return $rootScope;
    }
}
