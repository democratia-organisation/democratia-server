<?php

namespace Test\routes;

describe('users routes', function () {
    it('should get a user', function () {
        $response = $this->post('users/refresh');
        $content = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue(! empty($content['data']));
    });
});
