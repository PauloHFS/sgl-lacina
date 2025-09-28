# Matriz de Permissões (RBAC Refatorado)

Esta matriz define as permissões para cada `Role` após a refatoração do sistema de autorização.

**Roles:**
*   **COORDENADOR_MASTER:** Acesso irrestrito. Equivalente ao "super administrador".
*   **COORDENADOR:** Acesso de gerenciamento restrito aos projetos que coordena.
*   **COLABORADOR:** Acesso básico para gerenciar seus próprios dados e interagir com seus projetos.

| Recurso | Ação | COORDENADOR_MASTER | COORDENADOR | COLABORADOR | Notas |
| :--- | :--- | :---: | :---: | :---: | :--- |
| **User** | `viewAny` | ✅ | ❌ | ❌ | Apenas Master pode listar todos os usuários. |
| | `view` | ✅ | ✅ | ✅ | Coordenador pode ver usuários de seus projetos. Colaborador pode ver a si mesmo. |
| | `update` | ✅ | ❌ | ✅ | Apenas Master pode editar outros. Colaborador pode editar a si mesmo. |
| | `delete` | ✅ | ❌ | ❌ | Apenas Master pode deletar usuários. |
| | `updateRole`| ✅ | ❌ | ❌ | Ação futura: Apenas Master poderá alterar a role de outros. |
| **Projeto** | `viewAny` | ✅ | ✅ | ✅ | Todos podem listar projetos (com filtro por vínculo). |
| | `view` | ✅ | ✅ | ✅ | Acesso permitido se o usuário for membro do projeto. |
| | `create` | ✅ | ❌ | ❌ | Apenas Master pode criar novos projetos. |
| | `update` | ✅ | ✅ | ❌ | Master edita qualquer projeto, Coordenador edita seus projetos. |
| | `delete` | ✅ | ❌ | ❌ | Apenas Master pode deletar projetos. |
| | `viewAusencias`| ✅ | ✅ | ❌ | Vê ausências do projeto se for Coordenador do mesmo. |
| **Ausencia** | `viewAny` | ✅ | ✅ | ✅ | Todos podem listar suas ausências. Coordenadores veem de seus projetos. |
| | `view` | ✅ | ✅ | ✅ | Vê se for dono, ou Coordenador do projeto. |
| | `create` | ✅ | ✅ | ✅ | Todos podem criar pedidos de ausência. |
| | `update` | ✅ | ✅ | ✅ | Dono edita se pendente/recusada. Coordenador do projeto pode editar. |
| | `delete` | ✅ | ❌ | ✅ | Apenas o dono pode deletar (se não aprovada). |
| | `updateStatus`| ✅ | ✅ | ❌ | Apenas Coordenador do projeto pode aprovar/recusar. |
| **DailyReport**| `viewAny` | ✅ | ✅ | ✅ | Todos podem listar seus reports. Coordenadores veem de seus projetos. |
| | `view` | ✅ | ✅ | ✅ | Vê se for dono, ou Coordenador do projeto. |
| | `create` | ✅ | ✅ | ✅ | Todos podem criar reports. |
| | `update` | ✅ | ❌ | ✅ | Apenas o dono pode editar. |
| | `delete` | ✅ | ❌ | ✅ | Apenas o dono pode deletar. |
| **Sala** | `viewAny` | ✅ | ✅ | ✅ | Todos podem listar salas. |
| | `view` | ✅ | ✅ | ✅ | Todos podem ver detalhes da sala. |
| | `create` | ✅ | ❌ | ❌ | Apenas Master pode criar salas. |
| | `update` | ✅ | ❌ | ❌ | Apenas Master pode editar salas. |
| | `delete` | ✅ | ❌ | ❌ | Apenas Master pode deletar salas. |
