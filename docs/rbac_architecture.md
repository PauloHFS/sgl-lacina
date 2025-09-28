# Arquitetura do Sistema RBAC

Este documento descreve a arquitetura técnica do sistema de Role-Based Access Control (RBAC) refatorado.

## Componentes Principais

1.  **`Role` Enum (`app/Enums/Role.php`)**
    *   **Propósito:** Define as roles do sistema de forma centralizada e imutável.
    *   **Valores:** `COORDENADOR_MASTER`, `COORDENADOR`, `COLABORADOR`.

2.  **`User` Model (`app/Models/User.php`)**
    *   **Propósito:** Representa o usuário e é o ponto central para a determinação da role.
    *   **Campos Relevantes:**
        *   `is_coordenador_master (boolean)`: Flag que identifica um `COORDENADOR_MASTER`.
    *   **Lógica:** Utiliza o `HasRole` trait para expor a role do usuário.

3.  **`HasRole` Trait (`app/Models/Concerns/HasRole.php`)**
    *   **Propósito:** Encapsula a lógica de determinação da role de um usuário.
    *   **Métodos Principais:**
        *   `getRoleAttribute()`: Calcula e retorna a `Role` do usuário com base na seguinte hierarquia:
            1.  Se `is_coordenador_master` for `true`, retorna `Role::COORDENADOR_MASTER`.
            2.  Senão, se o usuário for coordenador em qualquer projeto ativo, retorna `Role::COORDENADOR`.
            3.  Caso contrário, retorna `Role::COLABORADOR`.
        *   `isCoordenadorMaster()`, `isCoordenador()`, `isColaborador()`: Métodos auxiliares para verificações booleanas diretas.

4.  **`AuthServiceProvider` (`app/Providers/AuthServiceProvider.php`)**
    *   **Propósito:** Registrar as Policies e o gate de acesso global.
    *   **Lógica:**
        *   `Gate::before()`: Intercepta todas as verificações de autorização. Se o usuário for `COORDENADOR_MASTER`, concede acesso imediato (`return true;`), curto-circuitando qualquer outra verificação de `Policy`.

5.  **`CheckRole` Middleware (`app/Http/Middleware/CheckRole.php`)**
    *   **Propósito:** Proteger rotas inteiras com base em uma ou mais roles.
    *   **Uso:** `Route::get(...)->middleware('role:coordenador_master,coordenador');`
    *   **Lógica:** Rejeita a requisição com um `403 Forbidden` se o usuário autenticado não possuir uma das roles especificadas.

6.  **`Policies` (`app/Policies/*.php`)**
    *   **Propósito:** Controlar o acesso a ações específicas em modelos (recursos).
    *   **Lógica:** Após a refatoração, as policies serão simplificadas. Elas confiarão no `Gate::before` para o `COORDENADOR_MASTER` e usarão os métodos do `HasRole` trait (`isCoordenador`, `isColaborador`) para as demais verificações, frequentemente em conjunto com a lógica de negócio (ex: "o usuário é dono do recurso?").

## Fluxo de uma Requisição

1.  A requisição chega a uma rota protegida.
2.  O `CheckRole` Middleware (se aplicado na rota) verifica a role do usuário. Se a verificação falhar, a requisição é bloqueada.
3.  O controller chama um método de autorização (ex: `$this->authorize('update', $post);`).
4.  O `Gate` é acionado.
5.  O `Gate::before()` no `AuthServiceProvider` é executado. Se o usuário for `COORDENADOR_MASTER`, o acesso é concedido.
6.  Se o `Gate::before()` não conceder acesso, a `Policy` correspondente ao modelo é executada.
7.  A `Policy` usa os métodos do `HasRole` trait e a lógica de negócio para determinar se o usuário tem permissão, retornando `true` ou `false`.
