<?php

namespace Test\routes;

describe('users routes', function () {
    it('should get a user', function () {
        $response = $this->get('users/groupes/7');
        $this->assertEquals(200, $response->getStatusCode());
    });
});
