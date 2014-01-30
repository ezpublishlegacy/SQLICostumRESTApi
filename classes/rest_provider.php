<?php

 class sqliCostumRestApiProvider implements ezpRestProviderInterface
 {
    /**
     * Returns registered versioned routes for provider
     *
     * @return array Associative array. Key is the route name (beware of name collision!). Value is the versioned route.
     */
     public function getRoutes()
     {
            return array( 'ezpNode'=> new ezpRestVersionedRoute( new ezpMvcRailsRoute( '/content/node/:nodeId', 'sqliCostumRestContentController', 'viewContent' ), 1 ),
                          'ezpList' => new ezpRestVersionedRoute( new ezpMvcRegexpRoute( '@^/content/node/(?P<nodeId>\d+)/list(?:/offset/(?P<offset>\d+))?(?:/limit/(?P<limit>\d+))?(?:/class/(?P<class>\w+))?(?:/sort/(?P<sortKey>\w+)(?:/(?P<sortType>asc|desc))?)?$@', 'sqliCostumRestContentController', 'list' ), 1 )
            
            );
     }
  
    /**
     * Returns associated with provider view controller
     *
     * @return ezpRestViewController
     */
     public function getViewController()
     {
             return new sqliCostumRestApiViewController();
     }
 }
 
 ?>