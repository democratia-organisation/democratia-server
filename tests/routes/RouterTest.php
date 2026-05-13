<?php

namespace Test\routes;

describe('users routes', function () {
    it('get a user', function () {
        $response = $this->get('users/7');
        $this->assertEquals(200, $response->getStatusCode());
    });

});

describe('thematiques routes', function () {});
