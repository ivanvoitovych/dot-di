<?php

namespace Tests\Application;

use DotDi\Attributes\Inject;

class TestService
{
    /**
     * 
     * @param GenericService<UserEntity, UserEntityDbMap> $usersRepository 
     * @return void 
     */
    public function __construct(
        #[Inject([
            'type' => 'UserEntity',
            'dbMap' => 'UserEntityDbMap'
        ])]
        public GenericService $usersRepository
    ) {
    }
}
