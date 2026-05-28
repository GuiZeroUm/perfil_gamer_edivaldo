<?php

namespace Tests\Feature;

use App\Models\Profile;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JwtProfileApiTest extends TestCase
{
    use RefreshDatabase;

    private const USER_ID = '90de0b23-fea5-46e2-8ed6-288daf79f39c';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'jwt.issuer' => 'https://auth.test',
            'jwt.audience' => 'internal-apis',
            'jwt.public_key_pem' => file_get_contents(__DIR__.'/../fixtures/jwt-public.pem'),
        ]);

        putenv('JWT_TEST_PRIVATE_KEY_PATH='.__DIR__.'/../fixtures/jwt-private.pem');
    }

    public function test_rejeita_requisicao_sem_token(): void
    {
        $this->getJson('/api/profiles/me')
            ->assertUnauthorized()
            ->assertJson(['message' => 'Missing Authorization header']);
    }

    public function test_retorna_perfil_do_usuario_autenticado(): void
    {
        Profile::create([
            'user_id' => self::USER_ID,
            'nickname' => 'gamer_teste',
            'bio' => 'Bio de teste',
        ]);

        $this->getJson('/api/profiles/me', [
            'Authorization' => 'Bearer '.$this->gerarToken(),
        ])
            ->assertOk()
            ->assertJsonPath('nickname', 'gamer_teste')
            ->assertJsonPath('user_id', self::USER_ID);
    }

    public function test_cria_perfil_com_user_id_do_token(): void
    {
        $this->postJson('/api/profiles', [
            'nickname' => 'novo_gamer',
            'bio' => 'Minha bio',
            'country' => 'Brasil',
            'platforms' => ['Steam'],
            'games' => ['Valorant'],
        ], [
            'Authorization' => 'Bearer '.$this->gerarToken(),
        ])
            ->assertCreated()
            ->assertJsonPath('user_id', self::USER_ID)
            ->assertJsonPath('nickname', 'novo_gamer');

        $this->assertDatabaseHas('profiles', [
            'user_id' => self::USER_ID,
            'nickname' => 'novo_gamer',
        ]);
    }

    private function gerarToken(): string
    {
        $privateKey = file_get_contents(__DIR__.'/../fixtures/jwt-private.pem');
        $now = time();

        return JWT::encode([
            'iss' => 'https://auth.test',
            'aud' => 'internal-apis',
            'sub' => self::USER_ID,
            'iat' => $now,
            'exp' => $now + 900,
        ], $privateKey, 'RS256');
    }
}
