<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório mensal</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 20px; margin: 0 0 4px 0; }
        h2 { font-size: 14px; margin: 18px 0 8px 0; }
        .muted { color: #6b7280; }
        .cards { margin: 12px 0; }
        .card { display: inline-block; width: 31%; margin-right: 2%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px; vertical-align: top; }
        .card:last-child { margin-right: 0; }
        .value { font-size: 14px; font-weight: bold; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 6px; }
        th { background: #f3f4f6; text-align: left; }
        .right { text-align: right; }
        .small { font-size: 11px; }
    </style>
</head>
<body>
    <h1>Relatório mensal</h1>
    <div class="muted">Usuário: {{ $user->name }} | Período: {{ mb_strtoupper($monthLabel, 'UTF-8') }}</div>

    <div class="cards">
        <div class="card">
            <div class="muted small">Entradas</div>
            <div class="value">R$ {{ number_format($incomeTotal, 2, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="muted small">Saídas</div>
            <div class="value">R$ {{ number_format($expenseTotal, 2, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="muted small">Saldo do mês</div>
            <div class="value">R$ {{ number_format($balance, 2, ',', '.') }}</div>
        </div>
    </div>

    <h2>Despesas por categoria</h2>
    <table>
        <thead>
            <tr>
                <th>Categoria</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($byCategory as $c)
                <tr>
                    <td>{{ $c['name'] }}</td>
                    <td class="right">R$ {{ number_format($c['total'], 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="muted">Sem despesas no período.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Lançamentos</h2>
    <div class="muted small" style="margin-bottom: 6px;">
        Total no mês: {{ $transactionsCount }}
        @if($includeDetails && $isTruncated)
            | Exibindo os {{ $detailsLimit }} primeiros para manter o relatório rápido.
        @endif
    </div>

    @if(!$includeDetails)
        <div class="muted">Detalhamento de lançamentos omitido (somente resumo e categorias).</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Conta</th>
                    <th>Categoria</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th class="right">Valor</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                    <tr>
                        <td>{{ $t->occurred_on?->format('d/m/Y') }}</td>
                        <td>{{ $t->description ?: '—' }}</td>
                        <td>{{ $t->financialAccount?->name ?: '—' }}</td>
                        <td>{{ $t->category?->name ?: '—' }}</td>
                        <td>{{ $t->type->value === 'income' ? 'Entrada' : 'Saída' }}</td>
                        <td>{{ $t->status->value === 'completed' ? 'Realizado' : 'Agendado' }}</td>
                        <td class="right">R$ {{ number_format((float) $t->amount, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="muted">Sem lançamentos no período.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</body>
</html>
