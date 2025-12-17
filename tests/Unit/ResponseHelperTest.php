<?php

namespace Tests\Unit;

use App\Utils\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ResponseHelperTest extends TestCase
{
    /** @test */
    public function it_returns_success_response_with_data()
    {
        $data = ['id' => 1, 'name' => 'Test'];
        $response = Response::success($data, 'Success message');

        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(200, $content['meta']['status']);
        $this->assertEquals('Success message', $content['meta']['message']);
        $this->assertEquals($data, $content['data']);
    }

    /** @test */
    public function it_returns_success_response_without_data()
    {
        $response = Response::success(null, 'Success');

        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertNull($content['data']);
    }

    /** @test */
    public function it_returns_success_response_with_custom_status_code()
    {
        $response = Response::success(['created' => true], 'Resource created', 201);

        $this->assertEquals(201, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(201, $content['meta']['status']);
    }

    /** @test */
    public function it_returns_success_response_with_default_message()
    {
        $response = Response::success(['data' => 'test']);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Success', $content['meta']['message']);
    }

    /** @test */
    public function it_handles_validation_exception_error()
    {
        $validator = validator(['email' => 'invalid-email'], [
            'email' => 'required|email',
        ]);

        try {
            $validator->validate();
        } catch (ValidationException $e) {
            $response = Response::error($e, 'Validation failed');

            $this->assertEquals(422, $response->getStatusCode());

            $content = json_decode($response->getContent(), true);
            $this->assertEquals('Validation failed', $content['message']);
            $this->assertArrayHasKey('email', $content['errors']);
        }
    }

    /** @test */
    public function it_handles_model_not_found_exception()
    {
        $exception = new ModelNotFoundException();

        $response = Response::error($exception);

        $this->assertEquals(404, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(404, $content['meta']['status']);
        $this->assertEquals('Data not found', $content['meta']['message']);
        $this->assertNull($content['data']);
    }

    /** @test */
    public function it_handles_authentication_exception()
    {
        $exception = new AuthenticationException();

        $response = Response::error($exception);

        $this->assertEquals(401, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(401, $content['meta']['status']);
    }

    /** @test */
    public function it_handles_generic_exception()
    {
        $exception = new \Exception('Something went wrong');

        $response = Response::error($exception, 'Custom error message', 500);

        $this->assertEquals(500, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(500, $content['meta']['status']);
        $this->assertEquals('Custom error message', $content['meta']['message']);
    }

    /** @test */
    public function it_returns_error_response_without_exception()
    {
        $response = Response::error(null, 'Custom error', 400);

        $this->assertEquals(400, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(400, $content['meta']['status']);
        $this->assertEquals('Custom error', $content['meta']['message']);
    }

    /** @test */
    public function it_has_consistent_response_structure_for_success()
    {
        $response = Response::success(['test' => 'data']);

        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('meta', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('status', $content['meta']);
        $this->assertArrayHasKey('message', $content['meta']);
    }

    /** @test */
    public function it_has_consistent_response_structure_for_error()
    {
        $response = Response::error(null, 'Error message');

        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('meta', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('status', $content['meta']);
        $this->assertArrayHasKey('message', $content['meta']);
    }

    /** @test */
    public function it_returns_token_response_correctly()
    {
        $token = 'test-jwt-token';
        $user = ['id' => 1, 'name' => 'Test User'];
        $expiresIn = 3600;

        $response = Response::token($token, $user, $expiresIn);

        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Login berhasil', $content['meta']['message']);
        $this->assertEquals($token, $content['data']['token']);
        $this->assertEquals($user, $content['data']['user']);
        $this->assertEquals($expiresIn, $content['data']['expires_in']);
    }

    /** @test */
    public function it_returns_not_found_response()
    {
        $response = Response::notFound('Resource not found');

        $this->assertEquals(404, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(404, $content['meta']['status']);
        $this->assertEquals('Resource not found', $content['meta']['message']);
        $this->assertNull($content['data']);
    }

    /** @test */
    public function it_returns_unauthorized_response()
    {
        $response = Response::unauthorized('Unauthorized access');

        $this->assertEquals(401, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(401, $content['meta']['status']);
        $this->assertEquals('Unauthorized access', $content['meta']['message']);
        $this->assertNull($content['data']);
    }

    /** @test */
    public function it_handles_empty_validation_errors()
    {
        $validator = validator(['name' => ''], [
            'name' => 'required',
        ]);

        try {
            $validator->validate();
        } catch (ValidationException $e) {
            $response = Response::error($e);

            $content = json_decode($response->getContent(), true);
            $this->assertIsArray($content['errors']);
            $this->assertNotEmpty($content['errors']);
        }
    }

    /** @test */
    public function it_handles_multiple_validation_errors()
    {
        $validator = validator([
            'name' => '',
            'email' => 'invalid',
            'age' => -5,
        ], [
            'name' => 'required',
            'email' => 'required|email',
            'age' => 'required|integer|min:0',
        ]);

        try {
            $validator->validate();
        } catch (ValidationException $e) {
            $response = Response::error($e);

            $content = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('name', $content['errors']);
            $this->assertArrayHasKey('email', $content['errors']);
            $this->assertArrayHasKey('age', $content['errors']);
        }
    }

    /** @test */
    public function it_returns_json_response_type()
    {
        $response = Response::success(['test' => 'data']);

        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_handles_null_data_in_success_response()
    {
        $response = Response::success(null);

        $content = json_decode($response->getContent(), true);
        $this->assertNull($content['data']);
    }

    /** @test */
    public function it_handles_empty_array_in_success_response()
    {
        $response = Response::success([]);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals([], $content['data']);
    }

    /** @test */
    public function it_handles_nested_data_structures()
    {
        $data = [
            'user' => [
                'id' => 1,
                'profile' => [
                    'name' => 'Test',
                    'settings' => ['theme' => 'dark'],
                ],
            ],
        ];

        $response = Response::success($data);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($data, $content['data']);
    }

    /** @test */
    public function it_preserves_data_types_in_response()
    {
        $data = [
            'integer' => 123,
            'float' => 45.67,
            'string' => 'test',
            'boolean' => true,
            'null' => null,
            'array' => [1, 2, 3],
        ];

        $response = Response::success($data);

        $content = json_decode($response->getContent(), true);
        $this->assertIsInt($content['data']['integer']);
        $this->assertIsFloat($content['data']['float']);
        $this->assertIsString($content['data']['string']);
        $this->assertIsBool($content['data']['boolean']);
        $this->assertNull($content['data']['null']);
        $this->assertIsArray($content['data']['array']);
    }
}
