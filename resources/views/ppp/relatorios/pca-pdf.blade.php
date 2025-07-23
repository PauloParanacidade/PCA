<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PCA - Planejamento de Contratações Anual</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 18px;
        }
        .subtitle {
            color: #7f8c8d;
            margin: 5px 0 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
        }
        .total {
            margin-top: 20px;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PARANACIDADE</h1>
        <div class="subtitle">Planejamento de Contratações Anual - PCA {{ date('Y') }}</div>
        <div class="subtitle">Gerado em: {{ date('d/m/Y H:i:s') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Item/Serviço</th>
                <th>Descrição</th>
                <th>Valor Estimado</th>
                <th>Origem</th>
                <th>Data Ideal</th>
                <th>Responsável</th>
                <th>Setor</th>
            </tr>
        </thead>
        <tbody>
            @php $totalGeral = 0; @endphp
            @foreach($ppps as $ppp)
                @php $totalGeral += $ppp->valor_estimado; @endphp
                <tr>
                    <td>{{ $ppp->id }}</td>
                    <td>{{ $ppp->nome_item }}</td>
                    <td>{{ Str::limit($ppp->descricao_item, 50) }}</td>
                    <td>R$ {{ number_format($ppp->valor_estimado, 2, ',', '.') }}</td>
                    <td>{{ $ppp->origem_recurso }}</td>
                    <td>{{ $ppp->data_ideal ? $ppp->data_ideal->format('d/m/Y') : '' }}</td>
                    <td>{{ $ppp->user->name ?? '' }}</td>
                    <td>{{ $ppp->user->department ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <p>Total de Itens: {{ $ppps->count() }}</p>
        <p>Valor Total Estimado: R$ {{ number_format($totalGeral, 2, ',', '.') }}</p>
    </div>

    <div class="footer">
        <p>Documento gerado automaticamente pelo Sistema PCA - {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>