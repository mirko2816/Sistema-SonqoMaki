<?php

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

function specialist(array $attributes = []): User
{
    return User::create(array_merge([
        'email' => 'especialista@sonqomaki.test',
        'password' => 'Una-clave-segura-2026',
        'is_active' => true,
    ], $attributes));
}

it('muestra la pantalla de inicio de sesión a visitantes', function () {
    $this->get('/iniciar-sesion')
        ->assertOk()
        ->assertSee('Inicia sesión')
        ->assertSee('Correo electrónico')
        ->assertSee('Contraseña');
});

it('permite iniciar sesión con credenciales correctas', function () {
    $user = specialist();

    $this->post('/iniciar-sesion', [
        'email' => $user->email,
        'password' => 'Una-clave-segura-2026',
    ])->assertRedirect(route('internal'));

    $this->assertAuthenticatedAs($user);
});

it('normaliza el email y lo autentica sin distinguir mayúsculas', function () {
    $user = specialist(['email' => 'Especialista@SonqoMaki.Test']);

    expect($user->fresh()->email)->toBe('especialista@sonqomaki.test');

    $this->post('/iniciar-sesion', [
        'email' => '  ESPECIALISTA@SONQOMAKI.TEST  ',
        'password' => 'Una-clave-segura-2026',
    ])->assertRedirect(route('internal'));

    $this->assertAuthenticatedAs($user);
});

it('impide emails duplicados sin distinguir mayúsculas', function () {
    specialist(['email' => 'cuenta@sonqomaki.test']);

    expect(fn () => specialist(['email' => 'CUENTA@SONQOMAKI.TEST']))
        ->toThrow(QueryException::class);
});

it('rechaza credenciales incorrectas con un mensaje genérico', function () {
    specialist();

    $this->from('/iniciar-sesion')->post('/iniciar-sesion', [
        'email' => 'especialista@sonqomaki.test',
        'password' => 'incorrecta',
    ])->assertRedirect('/iniciar-sesion')
        ->assertSessionHasErrors(['email' => 'Las credenciales ingresadas no son válidas.']);

    $this->assertGuest();
});

it('no permite iniciar sesión a una cuenta inactiva o eliminada', function () {
    $inactive = specialist(['email' => 'inactivo@sonqomaki.test', 'is_active' => false]);

    $this->post('/iniciar-sesion', [
        'email' => $inactive->email,
        'password' => 'Una-clave-segura-2026',
    ]);
    $this->assertGuest();

    RateLimiter::clear('inactivo@sonqomaki.test|127.0.0.1');
    $inactive->update(['is_active' => true]);
    $inactive->delete();

    $this->post('/iniciar-sesion', [
        'email' => $inactive->email,
        'password' => 'Una-clave-segura-2026',
    ]);
    $this->assertGuest();
});

it('regenera la sesión después del inicio de sesión', function () {
    specialist();
    $this->get('/iniciar-sesion');
    $previousSessionId = session()->getId();

    $this->post('/iniciar-sesion', [
        'email' => 'especialista@sonqomaki.test',
        'password' => 'Una-clave-segura-2026',
    ]);

    expect(session()->getId())->not->toBe($previousSessionId);
});

it('impide que un visitante acceda a una ruta protegida', function () {
    $this->get('/inicio')->assertRedirect(route('login'));
});

it('permite que un especialista autenticado acceda a la página interna provisional', function () {
    $this->actingAs(specialist())
        ->get('/inicio')
        ->assertOk()
        ->assertSee('Acceso privado verificado')
        ->assertSee('no contiene todavía el dashboard');
});

it('redirige al especialista autenticado fuera del formulario de inicio de sesión', function () {
    $this->actingAs(specialist())
        ->get('/iniciar-sesion')
        ->assertRedirect(route('internal'));
});

it('permite cerrar sesión mediante POST', function () {
    $this->actingAs(specialist())
        ->post('/cerrar-sesion')
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Sesión cerrada correctamente.');

    $this->assertGuest();
});

it('invalida la sesión y regenera el token CSRF al cerrar sesión', function () {
    $this->actingAs(specialist())->withSession(['private-marker' => 'sensible']);
    $previousSessionId = session()->getId();
    $previousToken = session()->token();

    $this->post('/cerrar-sesion')
        ->assertSessionMissing('private-marker');

    expect(session()->getId())->not->toBe($previousSessionId)
        ->and(session()->token())->not->toBe($previousToken);
    $this->get('/inicio')->assertRedirect(route('login'));
});

it('no permite cerrar sesión mediante GET', function () {
    $this->actingAs(specialist())
        ->get('/cerrar-sesion')
        ->assertMethodNotAllowed();

    $this->assertAuthenticated();
});

it('protege el cierre de sesión con CSRF', function () {
    $this->app->instance(ValidateCsrfToken::class, new class($this->app, $this->app['encrypter']) extends ValidateCsrfToken
    {
        protected function runningUnitTests(): bool
        {
            return false;
        }
    });

    $this->actingAs(specialist())
        ->post('/cerrar-sesion')
        ->assertStatus(419);

    $this->assertAuthenticated();
});

it('bloquea temporalmente los intentos excesivos de inicio de sesión', function () {
    specialist();

    foreach (range(1, 5) as $attempt) {
        $this->post('/iniciar-sesion', [
            'email' => 'especialista@sonqomaki.test',
            'password' => 'incorrecta',
        ]);
    }

    $response = $this->post('/iniciar-sesion', [
        'email' => 'especialista@sonqomaki.test',
        'password' => 'incorrecta',
    ])->assertSessionHasErrors('email');

    expect($response->getSession()->get('errors')->first('email'))
        ->toStartWith('Demasiados intentos.');

    $this->assertGuest();
});

it('no expone rutas públicas de registro ni recuperación de contraseña', function () {
    foreach (['/register', '/forgot-password', '/reset-password'] as $path) {
        $this->get($path)->assertNotFound();
        $this->post($path)->assertNotFound();
    }

    expect(app('router')->getRoutes()->getByName('register'))->toBeNull()
        ->and(app('router')->getRoutes()->getByName('password.request'))->toBeNull()
        ->and(app('router')->getRoutes()->getByName('password.reset'))->toBeNull();
});

it('crea al especialista con contraseña cifrada y evita duplicados', function () {
    $this->artisan('specialist:create', ['email' => 'Inicial@SonqoMaki.Test'])
        ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'Clave-inicial-segura-2026')
        ->expectsQuestion('Confirma la contraseña', 'Clave-inicial-segura-2026')
        ->expectsOutput('Cuenta del especialista creada correctamente.')
        ->assertSuccessful();

    $user = User::firstOrFail();

    expect($user->email)->toBe('inicial@sonqomaki.test')
        ->and($user->password)->not->toBe('Clave-inicial-segura-2026')
        ->and(Hash::check('Clave-inicial-segura-2026', $user->password))->toBeTrue();

    $this->artisan('specialist:create', ['email' => 'INICIAL@SONQOMAKI.TEST'])
        ->expectsOutput('Ya existe una cuenta con ese correo electrónico. No se realizaron cambios.')
        ->assertFailed();

    expect(User::count())->toBe(1);
});
