<?php

namespace Test\routes;

describe('users routes', function () {
    it('get a user', function () {
        $response = $this->getJson('users/7');
        $body = json_decode($response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());
    });

});

describe('thematiques routes', function () {});
