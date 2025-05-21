# Sistema de Recursos Humanos para o Laboratório de Computação Inteligente Aplicada (LACINA) da Universidade Federal de Campina Grande

## 1. Requisitos Funcionais (RF)

### Gestão de Projetos (Feito)

- **RF01:** O docente deve poder cadastrar projetos no Lacina.
- **RF02:** Cada projeto deve conter as seguintes informações: Nome, data de início, data de término, nome do cliente, links úteis (Slack, Discord, Board/Kanban), tipo do projeto, entre outros.
- **RF03:** O sistema deve listar os usuários sem vínculo com projetos.

### Gestão de Colaboradores (Discentes, Externos, Docentes)

- **RF04:** Discentes podem realizar seu pré-cadastro na plataforma, informando Nome, Email, Período de Entrada na Graduação, Período de Conclusão da Graduação, Links (LinkedIn, GitHub, Figma), Foto, Currículo, Área de Atuação e Tecnologias. (Feito)
- **RF05:** Colaboradores podem solicitar inscrição em projetos informando os dados necessários. (Feito)
- **RF06:** O primeiro cadastro deve exigir CPF, Nome, Email, Senha e Email do docente responsável pela avaliação do cadastro. (Feito)
- **RF07:** Coordenadores de projeto (docentes) podem validar, editar ou rejeitar os cadastros. (Feito)
- **RF08:** O sistema deve permitir a edição do perfil do colaborador com informações bancárias e pessoais (Conta bancária, Agência, Código do Banco, RG, UF do RG, Telefone). (Feito)
- **RF09:** Colaboradores podem solicitar adesão a projetos, e os coordenadores de projeto (docentes) podem aceitar ou editar antes de aceitar. (Feito)
- **RF10:** Discentes podem solicitar a troca de projeto, informando um motivo. (feito)
- **RF11:** Coordenadores do projeto atual e do novo projeto (docentes) devem aprovar a troca para que ela seja efetivada. (feito)
- **RF12:** Docentes podem remover discentes dos projetos.
- **RF13:** O sistema deve permitir que discentes gerem um relatório de histórico de participação nos projetos do laboratório.
- **RF14:** O relatório deve conter: nome do discente, projetos em que participou, período de participação em cada projeto e a carga horária total.
- **RF15:** O relatório deve ser gerado em formato PDF para facilitar a submissão na faculdade.

### Gestão de Folgas

- **RF16:** O sistema deve permitir o registro de folgas coletivas e pessoais.
- **RF17:** Os docentes devem poder acessar o registro de folgas de um colaborador.

### Gestão de Espaço

- **RF18:** O docente deve poder cadastrar salas e baías no sistema.

### Gestão de Equipamentos

- **RF19:** O sistema deve permitir o vínculo de equipamentos com colaboradores.
- **RF20:** O sistema deve permitir o vínculo de equipamentos com baias.

### Gestão de Horários

- **RF21:** Os discentes podem cadastrar seus horários de aula e trabalho.
- **RF22:** Os docentes podem visualizar os horários dos discentes por projeto ou individualmente.
- **RF23:** Docentes podem enviar alertas para discentes sobre problemas na distribuição de horários.

---

## 2. Casos de Uso (Use Cases)

### Ator: Docente

#### Gerenciar Projetos

- Criar projeto no Lacina
- Editar/remover projeto
- Gerenciar colaboradores no projeto

#### Gerenciar Colaboradores

- Validar cadastros de discentes e externos
- Aceitar/rejeitar pedidos de adesão a projetos
- Aceitar/rejeitar pedidos de troca de projeto
- Remover discentes de projetos

#### Gerenciar Espaços e Equipamentos

- Cadastrar salas e baías
- Vincular equipamentos a colaboradores ou baias

#### Visualizar e Gerenciar Horários

- Consultar horários dos discentes
- Enviar alertas sobre conflitos de horário

#### Gerar Relatório de Participação

- Gerar relatório de histórico de participação de um discente
- Exportar relatório em formato PDF

### Ator: Discente

#### Realizar Pré-Cadastro

- Informar dados básicos e enviar solicitação

#### Completar Perfil

- Preencher informações bancárias e pessoais

#### Solicitar Adesão a Projetos

- Escolher projeto e enviar solicitação ao coordenador docente

#### Solicitar Troca de Projeto

- Enviar pedido de troca informando o motivo
- Aguardar aprovação dos coordenadores docentes envolvidos

#### Cadastrar Horários

- Informar horários de aula e trabalho

#### Gerar Relatório de Histórico

- Solicitar relatório de participação nos projetos
- Baixar relatório em formato PDF

---

## 3. User Stories

### **Docentes**

#### **1. Cadastro de Projetos**

**Como** um docente,  
**Eu quero** cadastrar projetos no laboratório,  
**Para que** os discentes possam se inscrever e participar das atividades.

**Critérios de Aceitação:**

- O docente pode cadastrar um novo projeto com Nome, Cliente, Links úteis, etc.
- O sistema deve listar os projetos cadastrados.
- Apenas docentes podem criar projetos.

#### **2. Aprovação de Discentes em Projetos**

**Como** um docente,  
**Eu quero** aprovar ou rejeitar solicitações de discentes em projetos,  
**Para que** eu tenha controle sobre os participantes do meu projeto.

**Critérios de Aceitação:**

- O docente pode visualizar todas as solicitações pendentes.
- O docente pode aprovar ou rejeitar a solicitação.
- O discente recebe uma notificação sobre a decisão.

#### **3. Gestão de Espaços e Equipamentos**

**Como** um docente,  
**Eu quero** cadastrar salas, baias e equipamentos,  
**Para que** os colaboradores saibam onde trabalhar e quais recursos estão disponíveis.

**Critérios de Aceitação:**

- O docente pode cadastrar salas e baías no sistema.
- O docente pode vincular equipamentos a colaboradores ou baias.
- O sistema deve exibir uma lista dos equipamentos cadastrados.

#### **4. Gestão de Folga**

**Como** um docente coordenador de projeto,  
**Quero** acessar o histórico de folgas dos discentes vinculados aos meus projetos,  
**Para** acompanhar suas ausências e planejar as atividades do projeto.

**Critérios de Aceitação:**

- O sistema deve exibir uma lista de discentes vinculados ao projeto.
- O docente deve conseguir visualizar todas as folgas de um discente selecionado.
- O sistema deve impedir o acesso a folgas de discentes que não estão vinculados ao projeto.

### **Discentes**

#### **1. Cadastro de Discente**

**Como** um discente,  
**Eu quero** me cadastrar na plataforma com meus dados pessoais,  
**Para que** eu possa participar dos projetos do laboratório.

**Critérios de Aceitação:**

- Deve haver um formulário de cadastro com Nome, Email, Período de Entrada, etc.
- O sistema deve validar os dados inseridos.
- O cadastro deve ser aprovado por um docente antes do acesso completo.

#### **2. Edição de Perfil de Discente**

**Como** um discente,  
**Eu quero** editar meu perfil com informações adicionais, como links de redes profissionais,  
**Para que** os docentes possam ver minhas competências.

**Critérios de Aceitação:**

- O discente pode adicionar e editar suas informações.
- O sistema deve validar os links fornecidos (LinkedIn, GitHub, etc.).
- O docente pode visualizar o perfil completo do discente.
