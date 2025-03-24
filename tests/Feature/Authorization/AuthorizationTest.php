<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{

    public function test_authenticated_user_can_access_resource_only_admin_users_success()
    {
        $user = User::where('level', 'admin')->first();
        $this->actingAs($user);

        $response = $this->get('/access/only/admin');
        $response->assertStatus(200);
    }
    public function test_authenticated_user_can_access_resource_only_admin_users_failure()
    {
        $user = User::where('level', 'operator')->first();
        $this->actingAs($user);

        $response = $this->get('/access/only/admin');
        $response->assertStatus(403);

        $user = User::where('level', 'manager')->first();
        $this->actingAs($user);

        $response = $this->get('/access/only/admin');
        $response->assertStatus(403);
    }
    public function test_authenticated_user_can_access_resource_only_manager_users_success()
    {
        $user = User::where('level', 'manager')->first();
        $this->actingAs($user);

        $response = $this->get('/access/only/manager');
        $response->assertStatus(200);
    }
    public function test_authenticated_user_can_access_resource_only_manager_users_failure()
    {
        $user = User::where('level', 'operator')->first();
        $this->actingAs($user);

        $response = $this->get('/access/only/manager');
        $response->assertStatus(403);

        $user = User::where('level', 'admin')->first();
        $this->actingAs($user);

        $response = $this->get('/access/only/manager');
        $response->assertStatus(403);
    }
    public function test_authenticated_user_can_access_resource_only_operator_users_success()
    {
        $user = User::where('level', 'operator')->first();
        $this->actingAs($user);

        $response = $this->get('/access/only/operator');
        $response->assertStatus(200);
    }
    public function test_authenticated_user_can_access_resource_only_operator_users_failure()
    {
        $user = User::where('level', 'admin')->first();
        $this->actingAs($user);

        $response = $this->get('/access/only/operator');
        $response->assertStatus(403);

        $user = User::where('level', 'manager')->first();
        $this->actingAs($user);

        $response = $this->get('/access/only/operator');
        $response->assertStatus(403);
    }

}
