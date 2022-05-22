<?php

namespace Tests\Controllers;

use DotDi\Attributes\Inject;
use Tests\Application\GenericService;
use Tests\Application\ITaxService;

class TestController
{
    function __construct(
        #[Inject([
            'type' => 'UserModel',
            'dbMap' => 'UserModelDbMapping'
        ])]
        public GenericService $usersRepository,
        #[Inject([
            'type' => 'TopicModel',
            'dbMap' => 'TopicModelDbMapping'
        ])]
        public GenericService $topicsRepository,
        public ITaxService $taxService
    ) {
    }
}
