<?php

declare(strict_types=1);

namespace Alfasic\Middlewares;

/**
 * Contrato Oficial para Middlewares do Sistema Alfasic
 * Executado em cadeia antes da action do Controller.
 */
interface MiddlewareInterface
{
    /**
     * Processa a requisição atual.
     * Retorne true para prosseguir para o próximo middleware / controller,
     * ou execute um redirecionamento / encerramento e retorne false.
     *
     * @param array $params Parâmetros opcionais passados na definição da rota (ex: 'permission:users.manage')
     */
    public function handle(array $params = []): bool;
}
