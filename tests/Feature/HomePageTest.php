<?php

it('muestra la página técnica inicial', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Base técnica lista')
        ->assertSee('America/Lima');
});
