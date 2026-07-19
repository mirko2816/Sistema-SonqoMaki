<?php

it('redirige la raíz pública al inicio de sesión', function () {
    $this->get('/')->assertRedirect(route('login'));
});
