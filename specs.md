# Especificões

## 1. Requisitos Funcionais (RF)

### Gestão de Projetos

RF01: O docente deve poder cadastrar projetos no Lacina.
RF02: Cada projeto deve conter as seguintes informações: Nome, data de início, data de término, nome do cliente, links úteis (Slack, Discord, Board/Kanban), tipo do projeto, entre outros.
RF03: O sistema deve listar os usuários sem vínculo com projetos.
Gestão de Colaboradores (Discentes, Externos, Docentes)
RF04: Discentes podem realizar seu pré-cadastro na plataforma, informando Nome, Email, Período de Entrada na Graduação, Período de Conclusão da Graduação, Links (LinkedIn, GitHub, Figma), Foto, Currículo, Área de Atuação e Tecnologias.
RF05: Colaboradores podem solicitar inscrição em projetos informando os dados necessários.
RF06: O primeiro cadastro deve exigir CPF, Nome, Email, Senha e Email do docente responsável pela avaliação do cadastro.
RF07: Coordenadores de projeto (docentes) podem validar, editar ou rejeitar os cadastros.
RF08: O sistema deve permitir a edição do perfil do colaborador com informações bancárias e pessoais (Conta bancária, Agência, Código do Banco, RG, UF do RG, Telefone).
RF09: Colaboradores podem solicitar adesão a projetos, e os coordenadores de projeto (docentes) podem aceitar ou editar antes de aceitar.
RF10: Discentes podem solicitar a troca de projeto, informando um motivo.
RF11: Coordenadores do projeto atual e do novo projeto (docentes) devem aprovar a troca para que ela seja efetivada.
RF12: Docentes podem remover discentes dos projetos.
RF13: O sistema deve permitir que discentes gerem um relatório de histórico de participação nos projetos do laboratório.
RF14: O relatório deve conter: nome do discente, projetos em que participou, período de participação em cada projeto e a carga horária total.
RF15: O relatório deve ser gerado em formato PDF para facilitar a submissão na faculdade.

### Gestão de Folgas

RF16: O sistema deve permitir o registro de folgas coletivas e pessoais.

### Gestão de Espaço

RF17: O docente deve poder cadastrar salas e baias no sistema.

### Gestão de Equipamentos

RF18: O sistema deve permitir o vínculo de equipamentos com colaboradores.
RF19: O sistema deve permitir o vínculo de equipamentos com baias.

### Gestão de Horários

RF20: Discentes podem cadastrar seus horários de aula e trabalho.
RF21: Os Docentes podem visualizar os horários dos discentes por projeto ou individualmente.
RF22: Docentes podem enviar alertas para discentes sobre problemas na distribuição de horários.

## 2. Casos de Uso (Use Cases)

### Ator: Docente

#### Gerenciar Projetos

Criar projeto no Lacina
Editar/remover projeto
Gerenciar colaboradores no projeto

#### Gerenciar Colaboradores

Validar cadastros de discentes e externos
Aceitar/rejeitar pedidos de adesão a projetos
Aceitar/rejeitar pedidos de troca de projeto
Remover discentes de projetos

#### Gerenciar Espaços e Equipamentos

Cadastrar salas e baias
Vincular equipamentos a colaboradores ou baias

#### Visualizar e Gerenciar Horários

Consultar horários dos discentes
Enviar alertas sobre conflitos de horário

#### Gerar Relatório de Participação

Gerar relatório de histórico de participação de um discente
Exportar relatório em formato PDF

### Ator: Discente

#### Realizar Pré-Cadastro

Informar dados básicos e enviar solicitação

#### Completar Perfil

Preencher informações bancárias e pessoais

#### Solicitar Adesão a Projetos

Escolher projeto e enviar solicitação ao coordenador docente

#### Solicitar Troca de Projeto

Enviar pedido de troca informando o motivo
Aguardar aprovação dos coordenadores docentes envolvidos

#### Cadastrar Horários

Informar horários de aula e trabalho

#### Gerar Relatório de Histórico

Solicitar relatório de participação nos projetos
Baixar relatório em formato PDF

## 3. User Stories

### Discentes

#### 1. Cadastro de Discente

Como um discente,
 Eu quero me cadastrar na plataforma com meus dados pessoais,
 Para que eu possa participar dos projetos do laboratório.
Critérios de Aceitação:
Deve haver um formulário de cadastro com Nome, Email, Período de Entrada, etc.
O sistema deve validar os dados inseridos.
O cadastro deve ser aprovado por um docente antes do acesso completo.

#### 2. Edição de Perfil de Discente

Como um discente,
 Eu quero editar meu perfil com informações adicionais, como links de redes profissionais,
 Para que os docentes possam ver minhas competências.
Critérios de Aceitação:
O discente pode adicionar e editar suas informações.
O sistema deve validar os links fornecidos (LinkedIn, GitHub, etc.).
O docente pode visualizar o perfil completo do discente.

#### 3. Solicitação de Adesão a Projetos

Como um discente,
 Eu quero solicitar minha participação em um projeto do laboratório,
 Para que eu possa contribuir e adquirir experiência acadêmica.
Critérios de Aceitação:
O discente pode visualizar uma lista de projetos disponíveis.
Deve haver um botão para solicitar adesão ao projeto.
O docente coordenador do projeto deve aprovar a solicitação antes do ingresso.

#### 4. Solicitação de Troca de Projeto

Como um discente,
 Eu quero solicitar a troca de projeto,
 Para que eu possa migrar para outro projeto que seja mais adequado ao meu perfil ou interesse.
Critérios de Aceitação:
O discente pode visualizar os projetos disponíveis para troca.
Deve haver um formulário com justificativa para a solicitação.
O docente coordenador do projeto deve aprovar a mudança.

#### 5. Cadastro de Horários

Como um discente,
 Eu quero cadastrar meus horários de aula e trabalho,
 Para que os docentes saibam da minha disponibilidade.
Critérios de Aceitação:
O discente pode inserir seus horários no sistema.
O sistema deve exibir um calendário organizado.
O docente pode visualizar os horários dos discentes.

#### 6. Geração de Relatórios de Participação

Como um discente,
 Eu quero gerar um relatório com meu histórico de participação nos projetos do laboratório,
 Para que eu possa comprovar minha carga horária de atividades complementares.
Critérios de Aceitação:
O discente pode solicitar um relatório pelo sistema.
O relatório deve conter detalhes sobre os projetos e períodos de participação.
O relatório pode ser baixado em formato PDF.

### Docentes

#### 1. Cadastro de Projetos

Como um docente,
 Eu quero cadastrar projetos no laboratório,
 Para que os discentes possam se inscrever e participar das atividades.
Critérios de Aceitação:
O docente pode cadastrar um novo projeto com Nome, Cliente, Links úteis, etc.
O sistema deve listar os projetos cadastrados.
Apenas docentes podem criar projetos.

#### 2. Aprovação de Discentes em Projetos

Como um docente,
 Eu quero aprovar ou rejeitar solicitações de discentes em projetos,
 Para que eu tenha controle sobre os participantes do meu projeto.
Critérios de Aceitação:
O docente pode visualizar todas as solicitações pendentes.
O docente pode aprovar ou rejeitar a solicitação.
O discente recebe uma notificação sobre a decisão.

#### 3. Gestão de Espaços e Equipamentos

Como um docente,
 Eu quero cadastrar salas, baias e equipamentos,
 Para que os colaboradores saibam onde trabalhar e quais recursos estão disponíveis.
Critérios de Aceitação:
O docente pode cadastrar salas e baías no sistema.
O docente pode vincular equipamentos a colaboradores ou baias.
O sistema deve exibir uma lista dos equipamentos cadastrados.
