# Bolso Planejado — Documentação de Produto e Features

**Versão do documento:** 1.1  
**Última atualização:** 25 de março de 2025  
**Tipo:** visão de produto, escopo funcional e especificação orientadora (não substitui PRDs técnicos por módulo).  
**MVP (foco previsibilidade):** [MVP-PREVISIBILIDADE.md](./MVP-PREVISIBILIDADE.md)  
**API REST (v1):** [API-REST.md](./API-REST.md)  
**Status do backend:** [BACKEND-STATUS.md](./BACKEND-STATUS.md)

---

## Sumário

1. [Resumo executivo](#1-resumo-executivo)
2. [Visão, missão e proposta de valor](#2-visão-missão-e-proposta-de-valor)
3. [Público-alvo e personas](#3-público-alvo-e-personas)
4. [Princípios de produto e posicionamento](#4-princípios-de-produto-e-posicionamento)
5. [Escopo funcional — visão geral](#5-escopo-funcional--visão-geral)
6. [Módulo: identidade, conta e preferências](#6-módulo-identidade-conta-e-preferências)
7. [Módulo: cadastro e gestão de dívidas](#7-módulo-cadastro-e-gestão-de-dívidas)
8. [Módulo: acordos, renegociações e parcelamentos](#8-módulo-acordos-renegociações-e-parcelamentos)
9. [Módulo: entradas, saídas e orçamento](#9-módulo-entradas-saídas-e-orçamento)
10. [Módulo: poupança, metas e reservas](#10-módulo-poupança-metas-e-reservas)
11. [Módulo: contas, carteiras e conciliação](#11-módulo-contas-carteiras-e-conciliação)
12. [Módulo: planejamento e previsibilidade financeira (saldo dia a dia)](#12-módulo-planejamento-e-previsibilidade-financeira-saldo-dia-a-dia)
13. [Módulo: painéis, relatórios e insights](#13-módulo-painéis-relatórios-e-insights)
14. [Módulo: lembretes e notificações](#14-módulo-lembretes-e-notificações)
15. [Módulo: educação financeira leve](#15-módulo-educação-financeira-leve)
16. [Experiência do usuário, acessibilidade e conteúdo](#16-experiência-do-usuário-acessibilidade-e-conteúdo)
17. [Segurança, privacidade e conformidade (LGPD)](#17-segurança-privacidade-e-conformidade-lgpd)
18. [Modelo de dados conceitual (alto nível)](#18-modelo-de-dados-conceitual-alto-nível)
19. [Integrações e evolução técnica](#19-integrações-e-evolução-técnica)
20. [Métricas de produto e saúde do negócio](#20-métricas-de-produto-e-saúde-do-negócio)
21. [Roadmap sugerido por fases](#21-roadmap-sugerido-por-fases)
22. [Riscos, limitações e avisos ao usuário](#22-riscos-limitações-e-avisos-ao-usuário)
23. [Glossário](#23-glossário)

---

## 1. Resumo executivo

**Bolso Planejado** é um aplicativo web focado em **organização financeira pessoal** com ênfase em **sair do vermelho**: o usuário registra **dívidas**, **acordos de pagamento**, **entradas e saídas de dinheiro**, **poupanças e metas**, e acompanha **painéis** que traduzem números em decisões do dia a dia. Um pilar diferenciador é a **previsibilidade de caixa**: **saldo projetado dia a dia** (ou por conta), considerando o que já está agendado — salário, contas, parcelas e movimentos de poupança — para antecipar **riscos de ficar sem dinheiro** antes que aconteçam.

O produto não substitui **assessoria financeira**, **consultoria jurídica** nem **negociação real** com credores; ele **documenta**, **planeja** e **acompanha** o que o próprio usuário informa e executa na vida real.

Este documento descreve **o que o produto pode ser**, **módulos detalhados**, **regras de negócio orientadoras**, **implicações de privacidade** e um **roadmap** para evolução incremental.

---

## 2. Visão, missão e proposta de valor

### 2.1 Visão

Ser a referência em **clareza e ação** para quem precisa **organizar dívidas** e **reconstruir hábitos financeiros**, com uma experiência **simples**, **honesta** e **respeitosa** com a realidade de quem está endividado.

### 2.2 Missão

Reduzir a **ansiedade** e a **desorganização** ao centralizar informações financeiras críticas (dívidas, acordos, fluxo de caixa e metas) em um só lugar, com linguagem acessível e ferramentas que apoiam **decisões conscientes**.

### 2.3 Proposta de valor

| Pilar | O que entrega |
|--------|-----------------|
| **Clareza** | Visão unificada do que se deve, do que foi combinado e do que entra/sai. |
| **Controle** | Registro estruturado de dívidas, acordos e transações, com histórico. |
| **Progresso** | Indicadores de evolução (dívidas quitadas, metas, comprometimento da renda). |
| **Previsibilidade** | Saldo projetado por dia e por conta, marcos de risco e simulações “e se…”, a partir dos compromissos cadastrados. |
| **Hábito** | Lembretes, recorrências e rotinas leves de revisão (sem culpa excessiva). |

### 2.4 Diferenciação (conceitual)

- Foco explícito em **endividamento** e **acordos**, não apenas em “controle de gastos genérico”.
- **Visão futura de caixa** (saldo dia a dia / por conta), rara em apps só retrospectivos — com transparência de que é **projeção baseada em cadastros**, não adivinhação.
- Separação clara entre **dívida**, **parcela de acordo** e **despesa comum**, evitando misturar conceitos.
- Transparência sobre **limitações** (dados são informados pelo usuário; o app não “puxa” dívidas de órgãos de proteção ao crédito por padrão).

---

## 3. Público-alvo e personas

### 3.1 Público primário

- Adultos com **renda formal ou informal** que enfrentam **cartões**, **empréstimos**, **carnês**, **cheques** ou **dívidas com pessoas físicas**.
- Pessoas em **renegociação ativa** ou **pós-acordo** que precisam **cumprir cronogramas**.

### 3.2 Personas (ilustrativas)

**Persona A — “Ana, reorganizando o mês”**  
Renda estável mas apertada; várias dívidas pequenas; quer ver **total devido**, **próximos vencimentos** e **quanto sobra** após o essencial.

**Persona B — “Bruno, negociando com banco”**  
Fez acordo com entrada + parcelas; precisa registrar **condições**, **datas** e **comprovantes** e saber se está **adiantado ou atrasado** no combinado.

**Persona C — “Carla, informal + metas”**  
Entradas variáveis; quer **separar dinheiro** para emergência mesmo pagando dívidas; precisa de **metas** e **visão de caixa** por semana/mês.

### 3.3 Anti-personas (quando o produto não é o ideal sozinho)

- Situações de **violência financeira** ou **coerção** que exigem apoio humano especializado.
- Casos de **superendividamento** com necessidade de **defensoria**, **mediação judicial** ou **programas legais** específicos — o app apoia o registro, mas não substitui o processo.

---

## 4. Princípios de produto e posicionamento

1. **Verdade gentil:** mostrar números reais sem sensacionalismo; evitar julgamento moral na interface.
2. **Um conceito, um lugar:** dívida, acordo e transação têm definições consistentes na UX.
3. **Progresso > perfeição:** pequenos registros frequentes valem mais que planilhas complexas abandonadas.
4. **Privacidade por padrão:** minimizar coleta; criptografia em trânsito; clareza sobre o que é armazenado.
5. **Transparência jurídica:** avisos de que o app **não** realiza negociação automática nem garante acordos.

---

## 5. Escopo funcional — visão geral

### 5.1 Dentro do escopo (alto nível)

| Área | Inclui |
|------|--------|
| Dívidas | Cadastro, status, priorização, anexos opcionais. |
| Acordos | Registro de condições, parcelas, histórico de versões. |
| Fluxo de caixa | Entradas/saídas, categorias, recorrência, vínculo com dívidas/acordos. |
| Poupança / metas | Caixinhas, aportes, metas com prazo. |
| Relatórios | Mensal, comprometimento da renda, linha do tempo de vencimentos. |
| Previsibilidade | Saldo projetado diário, marcos de risco, buffer, simulação what-if (ver [seção 12](#12-módulo-planejamento-e-previsibilidade-financeira-saldo-dia-a-dia)). |
| Conta do usuário | Preferências, exportação, exclusão de dados (LGPD). |

### 5.2 Fora do escopo (inicial ou opcional futuro)

- Open finance obrigatório ou “sincronização automática” com todos os bancos (pode existir como **fase avançada** e opcional).
- Recomendação de investimentos de alto risco ou promessas de rentabilidade.
- Cobrança automática de terceiros ou envio de e-mails em nome do usuário para credores.

### 5.3 Premissas

- **Moeda padrão:** Real (BRL); extensível a multi-moeda em versões futuras.
- **Fonte da verdade:** dados inseridos pelo usuário, salvo integrações futuras explicitamente marcadas.

---

## 6. Módulo: identidade, conta e preferências

### 6.1 Objetivo

Garantir que cada conjunto de dados financeiros pertença a **um indivíduo** (ou a um **espaço familiar** em versões futuras), com controles de **acesso** e **privacidade**.

### 6.2 Features detalhadas

| ID | Feature | Descrição | Critérios de aceite (orientadores) |
|----|---------|-----------|-------------------------------------|
| ACC-01 | Cadastro de usuário | E-mail + senha ou provedores sociais (se adotados). | Validação de e-mail; política de senha configurável. |
| ACC-02 | Login e sessão | Sessão segura; opção “lembrar dispositivo” com riscos explicados. | Timeout; logout em todos os dispositivos (desejável). |
| ACC-03 | Recuperação de senha | Fluxo por e-mail com token de uso único e expiração curta. | Token invalidado após uso. |
| ACC-04 | Perfil básico | Nome, opcional: telefone, cidade (se necessário para suporte). | Campos mínimos; nada obrigatório além do necessário ao serviço. |
| ACC-05 | Preferências regionais | Formato de data, primeiro dia da semana, separador decimal. | Persistência por usuário. |
| ACC-06 | Modo escuro / tema | Tema claro/escuro ou seguir sistema. | Preferência salva; contraste adequado (WCAG). |
| ACC-07 | Moeda e unidade | BRL padrão; preparação para multi-moeda como roadmap. | Exibição consistente em toda a UI. |
| ACC-08 | Objetivo declarado | Texto curto ou seleção: “quitar dívidas”, “organizar fluxo”, etc. | Usado para personalizar mensagens **não invasivas** no dashboard. |
| ACC-09 | Exportação de dados | JSON/CSV com dívidas, transações, acordos (conforme implementação). | Arquivo gerado sob demanda; link com expiração se em nuvem. |
| ACC-10 | Exclusão de conta | Fluxo com confirmação; prazo de carência opcional para reversão. | Dados apagados ou anonimizados conforme política. |

### 6.3 Evoluções possíveis

- **Espaço familiar / compartilhado:** permissões (somente leitura vs. edição) para cônjuge ou dependente autorizado.
- **Dois fatores (2FA):** TOTP ou SMS para contas sensíveis.

---

## 7. Módulo: cadastro e gestão de dívidas

### 7.1 Objetivo

Representar **cada obrigação** de forma que o usuário saiba **quanto deve**, **a quem**, **em que condições** e **qual o status** ao longo do tempo.

### 7.2 Entidade “Dívida” — campos conceituais

| Campo | Obrigatório | Observações |
|-------|-------------|-------------|
| Nome do credor / descrição | Sim | Ex.: “Cartão Nubank”, “Loja X”, “Tio João”. |
| Tipo de dívida | Sim | Cartão, empréstimo pessoal, cheque especial, carnê, familiar, outro. |
| Valor original / contratado | Recomendado | Histórico se houver atualização contratual. |
| Saldo atual | Sim | Valor que o usuário considera devido **hoje**. |
| Moeda | Sim | BRL por padrão. |
| Taxa de juros / CET | Opcional | Campo informativo; pode ser desconhecido. |
| Dia de vencimento | Opcional | Para dívidas com fatura ou pagamento mensal. |
| Prioridade manual | Opcional | Usuário define ordem de ataque (estratégia pessoal). |
| Status | Sim | Ativa, em negociação, em acordo, quitada, suspensa, cancelada. |
| Notas | Opcional | Texto livre para contexto (não substitui documento legal). |

### 7.3 Features detalhadas

| ID | Feature | Descrição |
|----|---------|-----------|
| DIV-01 | CRUD de dívidas | Criar, editar, arquivar (soft delete) dívidas. |
| DIV-02 | Lista filtrável | Por status, tipo, credor, faixa de valor, vencimento. |
| DIV-03 | Detalhe da dívida | Tela com resumo, linha do tempo de eventos, vínculos com acordos. |
| DIV-04 | Priorização assistida | Sugestão de ordem por **menor saldo** (bola de neve) ou **maior taxa** (avalanche), **sem impor** — usuário confirma. |
| DIV-05 | Simulação simples | “Se eu pagar R$ X por mês, em quanto tempo zera?” — aproximada, com aviso de limitações. |
| DIV-06 | Quitação | Registrar data e valor final; congelar saldo em zero; manter histórico. |
| DIV-07 | Anexos | Upload de PDF/imagens (limite de tamanho); armazenamento seguro. |
| DIV-08 | Tags | Ex.: `#trabalho`, `#antigo`, `#cartão` para busca rápida. |

### 7.4 Regras de negócio (orientadoras)

- Ao vincular uma dívida a um **acordo ativo**, o saldo exibido pode refletir o **saldo do acordo** ou a **dívida original**, desde que a UI deixe claro **qual visão** está ativa.
- Dívida **quitada** não entra em totais de “a pagar”, mas entra em relatórios de **progresso**.

---

## 8. Módulo: acordos, renegociações e parcelamentos

### 8.1 Objetivo

Registrar **o combinado** com o credor: valores, parcelas, carências e histórico de **renegociações**, permitindo saber se a realidade está alinhada ao acordo.

### 8.2 Entidade “Acordo” — campos conceituais

| Campo | Descrição |
|-------|-----------|
| Dívida vinculada | Referência à dívida (ou múltiplas em casos complexos — versão avançada). |
| Data do acordo | Quando foi fechado ou assinado (informado pelo usuário). |
| Tipo | Desconto à vista, parcelamento, nova taxa, carência, etc. |
| Valor total acordado | Soma que o usuário deve pagar no novo arranjo. |
| Entrada | Valor inicial se houver. |
| Número de parcelas | Inteiro; pode haver parcela residual. |
| Valor da parcela | Fixo ou variável (campo adicional se variável). |
| Primeiro vencimento | Data de referência para gerar cronograma. |
| Status | Proposto, ativo, concluído, rompido, substituído. |
| Observações | Texto livre; anexos opcionais. |

### 8.3 Features detalhadas

| ID | Feature | Descrição |
|----|---------|-----------|
| ACO-01 | Criação de acordo | Assistente guiado: entrada → parcelas → revisão. |
| ACO-02 | Geração de cronograma | Lista de parcelas com datas esperadas e valores. |
| ACO-03 | Baixa de parcela | Marcar como paga; registrar data e valor efetivo; divergências. |
| ACO-04 | Histórico de versões | Acordo B **substitui** Acordo A; manter A como histórico somente leitura. |
| ACO-05 | Indicadores | % concluído; parcelas em atraso; total pago vs. previsto. |
| ACO-06 | Alertas de divergência | Se valor pago ≠ combinado, pedir confirmação ou nota explicativa. |
| ACO-07 | Vínculo com transação | Ao pagar parcela, opcionalmente gerar lançamento no fluxo de caixa. |

### 8.4 Casos extremos (documentação para UX)

- **Reparcelamento:** novo acordo deve poder “herdar” saldo remanescente do anterior.
- **Carência:** período sem pagamento antes da primeira parcela efetiva.
- **Multa/atraso:** registro manual de encargos adicionais por parcela atrasada.

---

## 9. Módulo: entradas, saídas e orçamento

### 9.1 Objetivo

Registrar **movimentações financeiras** para responder: **quanto entrou**, **quanto saiu**, **para onde foi o dinheiro** e **quanto sobrou** em um período.

### 9.2 Entidade “Transação” — campos conceituais

| Campo | Descrição |
|-------|-----------|
| Tipo | Entrada ou saída. |
| Valor | Positivo; sinal controlado pelo tipo. |
| Data | Data de competência ou pagamento (definir padrão e permitir ambos em versão avançada). |
| Categoria | Hierárquica opcional: Pai > Filho (ex.: Moradia > Aluguel). |
| Conta / carteira | De onde saiu ou para onde entrou. |
| Descrição | Texto curto. |
| Status | Realizada, agendada, cancelada. |
| Recorrência | Diária, semanal, mensal, anual, personalizada. |
| Vínculos | Opcional: dívida, parcela de acordo, meta de poupança. |

### 9.3 Plano de categorias (sugestão)

- **Moradia**, **Alimentação**, **Transporte**, **Saúde**, **Educação**, **Dívidas**, **Tarifas bancárias**, **Assinaturas**, **Lazer**, **Família**, **Imprevistos**, **Rendimentos** (para entradas), **Outros**.

### 9.4 Features detalhadas

| ID | Feature | Descrição |
|----|---------|-----------|
| FLX-01 | Lançamento rápido | Formulário mínimo + atalhos (últimas categorias). |
| FLX-02 | Edição e exclusão | Com auditoria simples (última modificação). |
| FLX-03 | Recorrência | Geração de ocorrências futuras; edição em série vs. apenas uma. |
| FLX-04 | Orçamento por categoria | Meta de gasto mensal por categoria; barra de progresso. |
| FLX-05 | Comparativo mês a mês | Variação % e valor absoluto. |
| FLX-06 | Regras de classificação | Se descrição contém “IFOOD”, sugerir Alimentação (opcional, com consentimento). |
| FLX-07 | Importação CSV | Mapeamento de colunas; preview antes de gravar. |

### 9.5 Regras de negócio

- Transação vinculada a **parcela de acordo** deve atualizar o status da parcela quando marcada como paga (configurável).
- **Entradas** não devem ser tratadas como “sobra automática”; o app pode sugerir alocação (dívida vs. poupança) sem executar nada automaticamente.

---

## 10. Módulo: poupança, metas e reservas

### 10.1 Objetivo

Permitir **reserva de emergência**, **metas** (viagem, curso) e **disciplina de aporte** mesmo em contexto de dívidas — respeitando que a prioridade é **sustentabilidade** do plano.

### 10.2 Entidade “Meta / Caixinha”

| Campo | Descrição |
|-------|-----------|
| Nome | Ex.: “Reserva emergência”, “Manutenção do carro”. |
| Valor alvo | Opcional (metas abertas permitidas). |
| Prazo alvo | Opcional. |
| Saldo atual | Soma dos aportes menos retiradas. |
| Prioridade | Ordem de alocação sugerida. |
| Regra opcional | Percentual da renda ou valor fixo mensal (lembrete). |

### 10.3 Features detalhadas

| ID | Feature | Descrição |
|----|---------|-----------|
| POU-01 | CRUD de metas | Criar, pausar, concluir metas. |
| POU-02 | Aportes e resgates | Movimentações com data e valor; motivo opcional. |
| POU-03 | Projeção | “Se aportar R$ X até data Y, atinge Z% da meta.” |
| POU-04 | Congelamento | Pausar metas não essenciais em um mês difícil (sem apagar histórico). |
| POU-05 | Vínculo com transação | Um aporte pode gerar lançamento de saída da conta corrente. |

### 10.4 Conteúdo educativo (mensagens de produto)

- Diferenciar **reserva de emergência** de **investimento**.
- Explicar que, em alguns cenários, **negociar juros altos** pode superar o retorno de poupança — sem dar conselho individualizado; apenas framework mental.

---

## 11. Módulo: contas, carteiras e conciliação

### 11.1 Objetivo

Opcionalmente segmentar o patrimônio líquido em **contas** (banco, dinheiro, carteira digital) para conciliar **saldo** com **transações**.

### 11.2 Features detalhadas

| ID | Feature | Descrição |
|----|---------|-----------|
| CNT-01 | Cadastro de contas | Nome, tipo, instituição (texto), saldo inicial. |
| CNT-02 | Saldo derivado | Saldo = inicial + entradas − saídas (por conta). |
| CNT-03 | Transferência entre contas | Duas transações vinculadas ou uma transação tipo transferência. |
| CNT-04 | Conciliação manual | Marcar transações como conferidas com extrato real. |
| CNT-05 | Fechamento de mês | Snapshot opcional de saldos por fim de período. |

---

## 12. Módulo: planejamento e previsibilidade financeira (saldo dia a dia)

### 12.1 Objetivo e diferencial de produto

Responder à pergunta: **“Quanto terei de dinheiro disponível em cada dia (ou em cada conta), já descontando o que sei que vou pagar e receber?”** — com base no que o usuário cadastrou (contas, salários, contas a pagar, parcelas de acordos, poupanças, transferências).

Este módulo é um **diferencial forte** frente a apps que só mostram **histórico** ou **orçamento mensal agregado**: o **Bolso Planejado** passa a oferecer **visão prospectiva** (futuro próximo), útil para evitar **cheque especial**, **atraso em parcelas** e **surpresas** no fim do mês.

**O que não é:** previsão mágica. **O que é:** **projeção determinística** a partir de compromissos e padrões informados, com **avisos de incerteza** onde faltar dado.

### 12.2 Conceitos centrais

| Conceito | Definição |
|----------|-----------|
| **Saldo de liquidez (conta)** | Valor disponível para uso imediato em uma conta/carteira (não confundir com saldo em caixinhas de poupança, se estiverem “bloqueados” na regra do usuário). |
| **Saldo projetado (D)** | Saldo ao fim do dia **D**, após aplicar na ordem correta todas as movimentações cuja **data de liquidação** cai em **D** ou antes, considerando saldo inicial e carry-over. |
| **Compromisso futuro** | Qualquer saída ou entrada com data futura: transação agendada, parcela de acordo pendente, recorrência expandida, salário, aporte/resgate de meta. |
| **Marco de risco** | Primeiro dia no horizonte em que o **saldo projetado** cai abaixo de zero (ou abaixo de um **piso de segurança** configurável) em uma conta escolhida. |
| **Horizonte de projeção** | Janela temporal (ex.: 7, 30, 60, 90 dias) sobre a qual o motor calcula a série diária. |
| **Granularidade** | Padrão **diária**; opção **semanal** (agregada) para quem não quer micromanagement. |

### 12.3 Fontes de dados que alimentam a projeção

O motor consolida, por **conta** (ou visão **consolidada** “todas as contas”):

| Fonte | Entra na projeção como |
|-------|-------------------------|
| Saldo atual da conta | Ponto de partida (ou saldo derivado de transações, conforme regra do produto). |
| Transações **realizadas** | Efeito até a data de liquidação; atualiza saldo “hoje”. |
| Transações **agendadas** | Saída/entrada na data programada. |
| Recorrências (salário, aluguel, assinaturas) | Geração de ocorrências futuras até o fim do horizonte (com regra de término: N vezes, até data, indefinido com limite de janela). |
| Parcelas de **acordos** não pagas | Saídas nas datas de vencimento esperadas; se marcadas como pagas, saem da projeção futura. |
| **Dívidas** com vencimento (fatura) | Se modeladas como pagamento único recorrente (ex.: dia 10), entram como compromisso. |
| **Transferências** entre contas | Saída em uma conta e entrada na outra **no mesmo dia** (ou D+0/D+1 configurável para TED). |
| **Poupança / metas** | **Aportes** (saída da conta corrente), **resgates** (entrada na conta), conforme agendados ou recorrentes; opcionalmente o saldo da caixinha pode **não** entrar na liquidez do dia a dia se o usuário marcar como “bloqueado até meta”. |
| **Buffer de imprevistos** (opcional) | Percentual ou valor fixo **descontado** do saldo projetado como “margem de segurança” apenas na visualização (não altera lançamentos). |

### 12.4 Motor de cálculo — regras orientadoras

1. **Base:** para cada conta, manter uma série \(S[d]\) de saldo ao **fim** do dia \(d\).
2. **Ordenação intradia:** em um mesmo dia, definir ordem de aplicação (ex.: entradas antes de saídas, ou ordem de prioridade do usuário para “o que paga primeiro”). A ordem deve ser **documentada na UI** para evitar surpresas.
3. **Carry-over:** o saldo inicial do dia \(d\) é o saldo final de \(d-1\) (ajustado por saldo real se o usuário informar “ajuste de caixa” naquele dia).
4. **Consolidação multi-conta:** soma algébrica dos saldos projetados por dia, **ou** visão por conta (recomendado manter ambas: “minha conta principal” vs. “total”).
5. **Incerteza:** itens marcados como **estimativa** (ex.: “entre R$ 200 e R$ 400”) geram **faixa** de saldo projetado (mínimo/máximo) em versão avançada; no MVP, um único valor “esperado” + nota.
6. **Reconciliação:** se o saldo real divergir do projetado, sugerir **ajuste** ou **nova transação** para alinhar (não forçar).

### 12.5 Visualizações e experiência

| Entrega | Descrição |
|---------|-----------|
| **Calendário de saldo** | Cada dia mostra saldo projetado **fim do dia** (e opcionalmente início). Cores: positivo, próximo do zero, negativo projetado. |
| **Lista “Próximos 30 dias”** | Tabela: data, saldo após o dia, lista de compromissos daquele dia. |
| **Curva / gráfico de linha** | Eixo X = dias, eixo Y = saldo; destacar **mínimo** no período. |
| **Detalhe do dia** | Ao clicar em um dia: todos os lançamentos que impactam aquele dia, com links para editar. |
| **Conta foco** | Seletor: “Conta corrente principal”, “todas”, ou conta específica. |
| **Comparativo** | “Saldo se eu atrasar o pagamento X para D+3” — simulação **what-if** (não persiste até o usuário confirmar). |

### 12.6 Cenários (opcional, avançado)

| Cenário | Uso |
|---------|-----|
| **Base** | Tudo que está agendado e confirmado pelo usuário. |
| **Conservador** | + buffer de imprevistos; opcionalmente antecipa vencimentos em 1 dia. |
| **Otimista** | Considera apenas entradas “garantidas”; saídas opcionais desmarcadas. |

Cenários não devem confundir o usuário no MVP: podem ficar atrás de “Modo avançado”.

### 12.7 Alertas e inteligência assistiva

- **Primeiro dia com saldo negativo projetado** (por conta): alerta com antecedência configurável (ex.: 7 dias antes).
- **Colisão de compromissos:** muitas saídas no mesmo dia que derrubam o saldo abaixo do piso.
- **Sugestão:** “Adiar não é possível no app — mas você pode simular mover a data de um agendamento” (edição guiada).
- Integração com [seção 14](#14-módulo-lembretes-e-notificações): notificar quando um **marco de risco** surgir no horizonte.

### 12.8 Features detalhadas

| ID | Feature | Descrição |
|----|---------|-----------|
| PREV-01 | Série de saldo diário | Cálculo automático para horizonte configurável. |
| PREV-02 | Escolha de conta ou consolidado | Alternância sem perder contexto. |
| PREV-03 | Inclusão de parcelas de acordo | Integração com cronograma de acordos; ignora parcelas já pagas. |
| PREV-04 | Inclusão de recorrências | Expansão de regras RRULE ou equivalente interno até o limite do horizonte. |
| PREV-05 | Transferências com D+0 / D+1 | Configuração por tipo de movimento. |
| PREV-06 | Poupança: aporte/resgate | Respeita bloqueio de liquidez da caixinha, se ativo. |
| PREV-07 | Marco de risco | Data e valor do menor saldo no período; destaque visual. |
| PREV-08 | Simulação what-if | Copiar cenário, alterar uma data/valor, comparar curvas (descartar ou salvar como “rascunho”). |
| PREV-09 | Buffer de segurança | Slider ou valor fixo que reduz o saldo “exibido” como disponível. |
| PREV-10 | Exportar projeção | CSV com colunas dia, saldo, lista de IDs de lançamentos. |
| PREV-11 | Explicação “De onde veio?” | Painel que lista todas as fontes ativas na projeção (transparência). |

### 12.9 Regras de negócio e limitações

- A projeção **depende** da qualidade dos cadastros; o app deve exibir **disclaimer** permanente curto.
- **Não** incluir juros de cheque especial ou rotativo automaticamente salvo se o usuário cadastrar **taxa estimada** e **regra** (versão futura).
- Salário **informal variável:** permitir entrada recorrente “estimada” com intervalo ou categoria “incerto”.
- Finais de semana/feriados: para liquidação bancária, opção de **empurrar** pagamento para próximo dia útil (configurável).

### 12.10 Integração com outros módulos

- **Transações** ([seção 9](#9-módulo-entradas-saídas-e-orçamento)): status agendado vs. realizado alimenta a série.
- **Acordos** ([seção 8](#8-módulo-acordos-renegociações-e-parcelamentos)): parcelas pendentes geram saídas futuras.
- **Poupança** ([seção 10](#10-módulo-poupança-metas-e-reservas)): aportes e metas alteram liquidez conforme regras.
- **Contas** ([seção 11](#11-módulo-contas-carteiras-e-conciliação)): saldo inicial e transferências.
- **Painéis** ([seção 13](#13-módulo-painéis-relatórios-e-insights)): widget “Saldo em 7 dias” no dashboard principal.

---

## 13. Módulo: painéis, relatórios e insights

### 13.1 Objetivo

Traduzir dados em **painéis acionáveis**: o que vence, quanto compromete a renda, qual tendência de gastos.

### 13.2 Painéis sugeridos

| Painel | Conteúdo |
|--------|----------|
| **Hoje / Semana** | Próximos vencimentos (dívidas + parcelas + recorrências). |
| **Mês** | Entradas, saídas, saldo, top categorias, comparativo. |
| **Dívidas** | Total devido, por tipo, progresso de quitação. |
| **Acordos** | Parcelas do mês, atrasadas, concluídas. |
| **Metas** | Percentual atingido, próximo marco. |

### 13.3 Features detalhadas

| ID | Feature | Descrição |
|----|---------|-----------|
| REL-01 | Dashboard principal | Cartões-resumo configuráveis (drag-and-drop em versão avançada). |
| REL-02 | Linha do tempo | Eventos financeiros nos próximos 30/60/90 dias. |
| REL-03 | Taxa de comprometimento | (Pagamentos de dívidas + despesas fixas) / renda informada. |
| REL-04 | Exportação PDF | Relatório mensal para arquivo pessoal. |
| REL-05 | Insights textuais | Frases baseadas em regras: “Você gastou X% a mais em Y” — com cuidado para não culpar. |
| REL-06 | Widget de previsibilidade | Resumo no dashboard: menor saldo projetado no período, primeiro dia de risco, link para o [módulo 12](#12-módulo-planejamento-e-previsibilidade-financeira-saldo-dia-a-dia). |

---

## 14. Módulo: lembretes e notificações

### 14.1 Objetivo

Reduzir **esquecimento** de vencimentos e revisões sem transformar o app em fonte de **notificação excessiva**.

### 14.2 Canais

- E-mail transacional.
- Notificações web (PWA), se habilitadas.
- SMS apenas se houver base de custo e consentimento explícito.

### 14.3 Tipos de lembrete

- Vencimento de **fatura** ou **parcela de acordo**.
- **Marco de risco** na projeção de saldo (ex.: “em 5 dias o saldo projetado fica negativo na conta X” — conforme [seção 12](#12-módulo-planejamento-e-previsibilidade-financeira-saldo-dia-a-dia)).
- Fim de **período de orçamento** (“fechar o mês”).
- Meta de **aporte** não realizada (opcional).

### 14.4 Preferências

Frequência máxima por dia; horário silencioso; opt-in por tipo de alerta.

---

## 15. Módulo: educação financeira leve

### 15.1 Objetivo

Oferecer **conteúdo curto** e **neutro** (sem promessas de ganho) integrado ao contexto do usuário.

### 15.2 Formatos

- Cards “Saiba mais” no contexto de dívidas e acordos.
- Glossário (ver seção 23).
- Links para fontes oficiais (Banco Central, Procon, consumidor.gov).

### 15.3 Temas possíveis

- Diferença entre **juros**, **multa** e **CET**.
- **Bola de neve** vs. **avalanche** (conceitos, não recomendação personalizada).
- **Superendividamento** e canais de ajuda (informação geral).

---

## 16. Experiência do usuário, acessibilidade e conteúdo

### 16.1 Diretrizes de UX

- Fluxos curtos para **registro diário**; complexidade escondida em “avançado”.
- Estados vazios com **exemplos** e **templates** (ex.: lista de categorias sugeridas).
- Linguagem **inclusiva** e **não punitiva** em mensagens de erro e insights.

### 16.2 Acessibilidade

- Contraste AA no mínimo para texto principal.
- Navegação por teclado; foco visível; labels em formulários.
- Textos alternativos em ícones relevantes.

### 16.3 Performance percebida

- Carregamento progressivo de listas longas.
- Feedback imediato ao salvar (toasts discretos).

---

## 17. Segurança, privacidade e conformidade (LGPD)

### 17.1 Princípios

- **Minimização:** coletar só o necessário ao funcionamento.
- **Finalidade:** dados usados apenas para prestação do serviço e melhorias (com base legal adequada).
- **Transparência:** política de privacidade clara; registro de consentimentos.

### 17.2 Medidas técnicas (meta)

- HTTPS em todo o site.
- Hash de senha com algoritmo moderno (ex.: bcrypt/argon2 — conforme stack).
- Proteção CSRF em formulários web.
- Rate limiting em login e APIs.
- Backups cifrados e política de retenção documentada.

### 17.3 Direitos do titular

- Acesso, correção, portabilidade, eliminação, revogação de consentimento — com fluxos na conta do usuário e canal de suporte.

---

## 18. Modelo de dados conceitual (alto nível)

Entidades principais e relações (simplificado):

```
Usuário 1 ── * Dívida
Dívida 1 ── * Acordo
Acordo 1 ── * ParcelaAcordo
Usuário 1 ── * Conta
Conta 1 ── * Transação
Transação * ── 1 Categoria
Usuário 1 ── * MetaPoupanca
MetaPoupanca 1 ── * MovimentacaoMeta
ParcelaAcordo 0..1 ── 1 Transação (vínculo opcional)
Anexo * ── 1 Dívida | 1 Acordo (polimorfismo ou tabelas separadas)

Projeção (derivada ou materializada):
Conta ── * SaldoProjetadoDia (opcional: tabela cache: conta_id, data, saldo_fim_dia, versao_regra)
— ou cálculo on-the-fly a partir de Transação, ParcelaAcordo, Recorrencia, Transferencia, AporteMeta
```

**Observação:** este modelo é **orientador**; a implementação pode normalizar categorias, tags e auditoria em tabelas dedicadas.

---

## 19. Integrações e evolução técnica

| Integração | Descrição | Fase sugerida |
|------------|-----------|----------------|
| PWA | Instalação no celular, push com moderação. | Médio prazo |
| Open Finance | Importação de transações com consentimento explícito. | Longo prazo |
| Calendário (ICS) | Exportar vencimentos para Google/Outlook. | Médio prazo |
| Webhooks / API | Para power users ou automação própria. | Longo prazo |

---

## 20. Métricas de produto e saúde do negócio

### 20.1 Métricas de engajamento

- **WAU/MAU** (usuários ativos semanais/mensais).
- **Registros por usuário** (transações + dívidas atualizadas) — proxy de hábito.
- **Retenção** D1, D7, D30 após cadastro.

### 20.2 Métricas de valor

- Número de **dívidas quitadas** registradas por trimestre (coletivo agregado).
- Redução do **tempo médio** até primeiro acordo registrado (onboarding eficaz).
- Uso da **previsibilidade:** % de usuários que abrem o **calendário de saldo** semanalmente; redução de **marcos de risco** não vistos (simulação: usuário edita datas antes do problema — medir via pesquisa ou evento “simulação salva”).

### 20.3 Métricas de confiança

- **Churn** e motivos declarados em cancelamento.
- Tickets de suporte por **perda de dados** ou **confusão de conceitos** — indicam UX a ajustar.

---

## 21. Roadmap sugerido por fases

### Fase 0 — Fundação

- Autenticação, perfil, tema, política de privacidade.
- CRUD de dívidas e dashboard mínimo.

### Fase 1 — MVP financeiro

- Transações com categorias; resumo mensal; lista de dívidas com totais.

### Fase 2 — Acordos

- Acordos com parcelas; marcação de pagas; linha do tempo de vencimentos.

### Fase 3 — Disciplina e metas

- Metas de poupança; orçamento por categoria; lembretes por e-mail.

### Fase 3.1 — Previsibilidade de caixa (diferencial)

- **Saldo projetado dia a dia** por conta e consolidado; inclusão de recorrências, parcelas de acordo e transferências.
- **Marco de risco** e alertas opcionais; **buffer de segurança**; **simulação what-if** básica.
- Widget no dashboard (**REL-06**) e exportação CSV da série (**PREV-10**).

### Fase 4 — Polimento e escala

- Importação CSV; PDF; PWA; melhorias de acessibilidade e performance; aprofundamento de **cenários** (conservador/otimista) e faixas de incerteza na projeção.

### Fase 5 — Integrações avançadas

- Open Finance opcional; API; recursos familiares/compartilhados.

---

## 22. Riscos, limitações e avisos ao usuário

1. **Dados informados pelo usuário** podem estar incompletos ou desatualizados — totais são **orientativos**.
2. **Saldo projetado** é uma **projeção** baseada em lançamentos e regras; não inclui gastos não registrados, taxas bancárias não cadastradas nem atrasos de credores. Pode divergir da realidade.
3. **Simulações** simplificam juros e não substituem extratos oficiais.
4. O app **não negocia** com bancos nem altera contratos.
5. Em situações de **superendividamento** ou **ações judiciais**, o usuário deve buscar **ajuda profissional** e órgãos competentes.

---

## 23. Glossário

| Termo | Definição |
|-------|-----------|
| **Dívida** | Obrigação financeira reconhecida pelo usuário em relação a um credor. |
| **Acordo** | Arranjo de pagamento que altera ou organiza condições anteriores (informado pelo usuário). |
| **Parcela** | Parte do acordo com data e valor esperados. |
| **Transação** | Entrada ou saída de dinheiro no fluxo de caixa. |
| **Categoria** | Classificação da transação para análise de gastos. |
| **Meta** | Objetivo de acumulação (caixinha) com ou sem prazo. |
| **Conciliação** | Conferência entre registros do app e extrato real. |
| **CET** | Custo Efetivo Total — medida que engloba juros, taxas e encargos (conceito educacional). |
| **Bola de neve** | Estratégia de pagar primeiro menores saldos para ganhar momentum (conceito geral). |
| **Avalanche** | Estratégia de priorizar maiores juros (conceito geral). |
| **Saldo projetado** | Saldo esperado ao fim de um dia futuro, calculado a partir de saldos e compromissos cadastrados (não é saldo bancário real até que o dia passe e seja conciliado). |
| **Marco de risco** | Dia em que a projeção indica saldo abaixo de zero (ou abaixo de um piso definido pelo usuário). |
| **Liquidez** | Dinheiro disponível para uso imediato conforme regras do usuário (pode excluir valores “bloqueados” em metas). |
| **What-if** | Simulação que altera datas ou valores sem gravar, para comparar cenários. |

---

## Encerramento

Este documento consolida uma **visão amplia** do **Bolso Planejado**: do propósito às **features detalhadas**, passando por **previsibilidade de caixa** ([seção 12](#12-módulo-planejamento-e-previsibilidade-financeira-saldo-dia-a-dia)), **dados**, **privacidade** e **roadmap**. Próximos passos típicos de time de produto incluem: priorizar o **MVP** com critérios de esforço e valor, derivar **histórias de usuário** por módulo e alinhar **requisitos não funcionais** (segurança, performance, LGPD) com a stack escolhida (por exemplo Laravel + frontend adotado neste repositório).

Para sugestões de evolução deste documento, mantenha um changelog no topo do arquivo ou em `docs/CHANGELOG-DOC.md` (opcional).
