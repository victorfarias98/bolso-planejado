# Bolso Planejado — MVP centrado em previsibilidade de caixa

**Versão:** 1.0  
**Tipo:** escopo de MVP + histórias de aceite orientadoras  
**Documento irmão:** [PRODUTO-DIVIDA-ZERO.md](./PRODUTO-DIVIDA-ZERO.md) (visão completa)  
**API implementada:** [API-REST.md](./API-REST.md)  
**Backend pronto (resumo):** [BACKEND-STATUS.md](./BACKEND-STATUS.md)

---

## Sumário

1. [Visão do MVP](#1-visão-do-mvp)
2. [Hipótese de valor](#2-hipótese-de-valor)
3. [O que entra no MVP](#3-o-que-entra-no-mvp)
4. [O que fica fora do MVP](#4-o-que-fica-fora-do-mvp)
5. [Feature principal: previsibilidade (detalhe)](#5-feature-principal-previsibilidade-detalhe)
6. [Dependências: sem isso, a previsibilidade não existe](#6-dependências-sem-isso-a-previsibilidade-não-existe)
7. [Épicos e histórias de usuário](#7-épicos-e-histórias-de-usuário)
8. [Critérios de “MVP pronto”](#8-critérios-de-mvp-pronto)
9. [O que ainda pode faltar (lacunas honestas)](#9-o-que-ainda-pode-faltar-lacunas-honestas)
10. [Ordem sugerida de implementação](#10-ordem-sugerida-de-implementação)

---

## 1. Visão do MVP

Entregar, em uma primeira versão utilizável, a resposta:

> **“Nos próximos dias, quanto dinheiro vou ter na minha conta (ou no total), já contando o que sei que entra e sai?”**

com **cadastro mínimo** de **contas** e **lançamentos** (passados e futuros), **recorrência mensal simples** e uma **tela de projeção** clara, com **marco de risco** quando o saldo projetado ficar negativo.

O MVP **não** precisa cobrir todo o ecossistema de dívidas, acordos e poupança descrito no documento de produto completo — mas precisa ser **honesto** com o posicionamento “sair do vermelho”: por isso recomenda-se pelo menos **rótulo/categoria** para lançamentos ligados a dívida (mesmo sem módulo de acordo ainda).

---

## 2. Hipótese de valor

Se o usuário **registrar** saldo inicial (ou saldo atual), **contas fixas** e **compromissos futuros** (contas, parcelas como lançamentos, salário), ele **antecipa** o dia em que o dinheiro acaba e **ajusta** comportamento ou datas — reduzindo ansiedade e inadimplência evitável.

**Métrica de sucesso inicial (qualitativa + produto):**

- Usuário consegue explicar em uma frase o **menor saldo** nos próximos 30 dias sem olhar o banco.
- Evento de produto: retorno à **tela de previsão** pelo menos **1x por semana** (proxy de valor).

---

## 3. O que entra no MVP

| Área | Escopo MVP |
|------|------------|
| **Conta de usuário** | Cadastro, login, logout, recuperação de senha (mínimo viável). |
| **Contas / carteiras** | Pelo menos uma conta; suportar várias (nome, saldo inicial opcional se usar só movimentos). |
| **Transações** | Entrada/saída, valor, data, conta, descrição, **categoria** (lista fixa pequena), status **realizado** vs **agendado** (futuro). |
| **Recorrência** | **Mensal**, com data de início, dia do mês (ou âncora), término opcional (N vezes ou data fim). |
| **Previsibilidade** | Série **diária** de saldo projetado; horizonte **30 dias** (e opcionalmente **60**); seleção **por conta** ou **consolidado**; **lista por dia** + **gráfico simples**; **marco de risco** (primeiro dia ≤ 0); **detalhe do dia** (lista do que entrou no cálculo). |
| **Dashboard** | Resumo: saldo hoje (real), **menor saldo projetado** no período, **data do primeiro risco**, link para a tela de previsão. |
| **Transparência** | Texto curto fixo: projeção depende dos dados cadastrados; não substitui extrato. |
| **Categorias** | Incluir pelo menos uma categoria do tipo **“Dívidas / parcelas”** para alinhar ao tema do produto, ainda sem módulo formal de acordo. |

---

## 4. O que fica fora do MVP

Itens intencionalmente **postergados** (podem vir na **v1.1** em diante):

| Item | Motivo |
|------|--------|
| Módulo completo de **dívidas** e **acordos** com cronograma | Aumenta muito modelo e UX; no MVP, **lançamentos agendados + recorrência** cobrem 80% da projeção. |
| **Poupança / caixinhas** com bloqueio de liquidez | Regra extra no motor; adicionar após projeção estável. |
| **Transferências** D+1, TED, múltiplas contas com regras bancárias | Começar com **mesmo dia** ou ignorar transferência interna se só uma conta. |
| **What-if**, **buffer** deslizante, **cenários** conservador/otimista | Diferencial forte, mas segundo passo. |
| **Exportação CSV/PDF**, **PWA**, **push/e-mail** de marco de risco | Ótimos, não bloqueiam validar a projeção. |
| **Open Finance** | Complexidade legal/técnica; opcional em fase posterior. |
| **Orçamento por categoria** completo | Útil, mas secundário à curva de saldo. |
| **Faixa min/max** para valores incertos | MVP: um valor por lançamento; nota opcional no campo descrição. |

---

## 5. Feature principal: previsibilidade (detalhe)

### 5.1 Comportamento esperado (regras do motor — MVP)

1. **Ponto de partida:** para cada conta, saldo no **início do “hoje”** = saldo inicial informado **mais** todas as transações **realizadas** com data ≤ hoje (conforme fuso do usuário).
2. **Dias futuros:** para cada dia \(d\) no horizonte, aplicar todos os lançamentos com **data de liquidação = \(d\)** na ordem MVP fixa: **entradas antes de saídas** (documentar na UI).
3. **Recorrência mensal:** expandir ocorrências futuras até cobrir o horizonte; não ultrapassar data fim ou N máximo de repetições.
4. **Agendados:** transações futuras com status “agendado” entram na projeção; ao marcar como “realizado”, deixam de ser futuras e passam a compor o passado.
5. **Consolidado:** soma dos saldos projetados **fim do dia** de todas as contas (definição simples; sem compensação cruzada avançada).
6. **Marco de risco:** primeiro dia, dentro do horizonte, em que o saldo **fim do dia** da conta selecionada (ou consolidado, conforme toggle) for **&lt; 0**.

### 5.2 Interface mínima

- **Seletor:** conta | todas.
- **Seletor:** horizonte 30 | 60 dias.
- **Vista lista:** colunas data, saldo fim do dia, quantidade de movimentos.
- **Vista gráfico:** linha saldo × dia; marca visual no ponto mínimo e no primeiro dia negativo.
- **Drill-down:** ao escolher um dia, lista nome/valor/tipo (entrada/saída) e link para editar o lançamento.
- **Estado vazio:** se não houver conta ou não houver nenhum lançamento futuro e saldo não definido, explicar o que cadastrar primeiro.

### 5.3 IDs alinhados ao documento de produto (subconjunto MVP)

| ID | Incluído no MVP? |
|----|------------------|
| PREV-01 Série diária | Sim |
| PREV-02 Conta / consolidado | Sim |
| PREV-03 Parcelas de acordo | Não (substituído por lançamentos agendados) |
| PREV-04 Recorrências | Sim (apenas **mensal**) |
| PREV-05 D+0 / D+1 transferências | Não (MVP: mesmo dia se existir transferência) |
| PREV-06 Poupança | Não |
| PREV-07 Marco de risco | Sim |
| PREV-08 What-if | Não |
| PREV-09 Buffer | Não |
| PREV-10 Exportar CSV | Opcional stretch |
| PREV-11 “De onde veio?” | Parcial: **detalhe do dia** + disclaimer global |

---

## 6. Dependências: sem isso, a previsibilidade não existe

A feature de previsibilidade **não é isolada**. Ela exige:

1. **Conta financeira** no app (carteira/conta corrente) com forma de obter **saldo base** (saldo inicial + movimentos OU saldo informado e ajustado).
2. **Transações com data** e vínculo à conta.
3. **Capacidade de registrar o futuro** (agendado + recorrência); sem isso, a curva é só o passado.

**Conclusão:** o MVP é um **pacote** “**contas + lançamentos + recorrência mensal + projeção + dashboard**”. Tirar qualquer peça quebra a narrativa do diferencial.

---

## 7. Épicos e histórias de usuário

### Épico A — Autenticação

| ID | História | Aceite (resumido) |
|----|----------|-------------------|
| A-1 | Como visitante, quero criar conta com e-mail e senha para guardar meus dados. | Validação de e-mail; senha com regra mínima; erro claro. |
| A-2 | Como usuário, quero entrar e sair com segurança. | Sessão estável; logout limpa sessão. |

### Épico B — Contas

| ID | História | Aceite (resumido) |
|----|----------|-------------------|
| B-1 | Como usuário, quero cadastrar ao menos uma conta com nome e saldo inicial. | CRUD mínimo; impedir exclusão se for a única conta com lançamentos (ou migrar lançamentos — definir regra). |
| B-2 | Como usuário, quero ver o saldo **atual** calculado a partir dos lançamentos. | Fórmula documentada na UI; coerência com lista de transações. |

### Épico C — Transações e recorrência

| ID | História | Aceite (resumido) |
|----|----------|-------------------|
| C-1 | Como usuário, quero lançar entrada e saída com data e conta. | Valores positivos; tipo define sinal; data não nula. |
| C-2 | Como usuário, quero marcar um lançamento como **agendado** para o futuro. | Aparece na projeção; não confundir com realizado. |
| C-3 | Como usuário, quero repetir um lançamento **todo mês** (ex.: aluguel, salário). | Gera ocorrências no horizonte da projeção; edição “só este” vs “todos os próximos” pode ser MVP simplificado (documentar limitação). |
| C-4 | Como usuário, quero classificar com categoria incluindo **dívidas/parcelas**. | Filtro opcional na lista; aparece no detalhe do dia. |

### Épico D — Previsibilidade (hero)

| ID | História | Aceite (resumido) |
|----|----------|-------------------|
| D-1 | Como usuário, quero ver minha **projeção de saldo por dia** nos próximos 30 dias. | Lista e gráfico coerentes entre si; mesmo resultado para mesma base de dados. |
| D-2 | Como usuário, quero alternar entre **uma conta** e **todas**. | Saldo consolidado = soma dos saldos fim de dia por conta. |
| D-3 | Como usuário, quero saber o **primeiro dia** em que fico **negativo**. | Destaque explícito; se não houver, mensagem positiva clara. |
| D-4 | Como usuário, quero abrir um dia e ver **o que compõe** a mudança de saldo. | Lista de lançamentos daquele dia + saldo anterior do dia. |

### Épico E — Dashboard

| ID | História | Aceite (resumido) |
|----|----------|-------------------|
| E-1 | Como usuário, quero ver no início um resumo do **risco** e um atalho para a projeção. | Três informações: saldo hoje; menor saldo no período; primeiro dia de risco (se houver). |

---

## 8. Critérios de “MVP pronto”

- [ ] Usuário novo consegue, em **menos de 10 minutos**, cadastrar conta, salário (recorrente) e 2–3 contas agendadas e ver a **curva** mudar.
- [ ] Projeção **recomputa** após criar/editar/excluir lançamento ou recorrência (ou invalidação explícita com feedback).
- [ ] **Disclaimer** visível na tela de previsão.
- [ ] Não há divergência não explicada entre **saldo hoje** na conta e **primeiro ponto** da série (documentar ordem do dia “hoje” se inclui agendados de hoje).
- [ ] Funciona em **mobile** (layout responsivo), ainda que PWA não seja requisito.

---

## 9. O que ainda pode faltar (lacunas honestas)

Mesmo com o MVP acima, estes pontos costumam aparecer em uso real — vale tratar cedo na **v1.1** se houver feedback:

| Lacuna | Por que importa |
|--------|------------------|
| **Onboarding guiado** (“cadastre salário e 3 contas”) | Sem isso, usuário vê projeção “vazia” e desiste. |
| **Ajuste de caixa** | Quando o saldo real do banco diverge, precisa de **lançamento de ajuste** em um clique. |
| **Recorrência semanal/quinzenal** | Comum para quem recebe por semana ou bico. |
| **Múltiplas moedas** | Fora do MVP BRL-only; expatriados ou viagem pedem depois. |
| **Lembretes** (e-mail/push) para marco de risco | Aumenta muito a percepção de valor da projeção. |
| **Módulo de dívidas/acordos** | Para o nome **Bolso Planejado**, o MVP sobrevive com categorias, mas o **diferencial temático** fica mais forte com parcelas nativas. |
| **What-if** | É o “wow” depois que a curva base já funciona. |

Nada disso **invalida** o MVP descrito; são **evoluções naturais** assim que a projeção estiver estável.

---

## 10. Ordem sugerida de implementação

1. Modelagem e CRUD de **usuário** e **contas**.  
2. **Transações** (realizado) e cálculo de **saldo atual**.  
3. Status **agendado** + datas futuras na projeção (sem recorrência ainda).  
4. **Recorrência mensal** + expansão no motor.  
5. **Tela previsão** (lista + gráfico + detalhe do dia + marco de risco).  
6. **Dashboard** com resumo e link.  
7. Polimento: empty states, disclaimer, testes de borda (fim de mês, 31, fevereiro).

---

## Referência cruzada

Especificação ampla do módulo de previsibilidade: [PRODUTO-DIVIDA-ZERO.md — seção 12](./PRODUTO-DIVIDA-ZERO.md#12-módulo-planejamento-e-previsibilidade-financeira-saldo-dia-a-dia).
