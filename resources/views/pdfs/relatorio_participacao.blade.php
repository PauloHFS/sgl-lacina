<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Participação no LACINA</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
            color: #2d3748;
        }
        h2 {
            font-size: 16px;
            margin-top: 30px;
            margin-bottom: 10px;
            color: #2d3748;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f7fafc;
            font-weight: bold;
            color: #4a5568;
        }
        .user-info p {
            margin-bottom: 5px;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #718096;
            margin-top: 30px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Relatório de Participação no LACINA</h1>

        <div class="user-info">
            <h2>Dados do Colaborador</h2>
            <p><strong>Nome:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Data de Emissão:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>

        <h2>Histórico de Projetos</h2>
        @if ($historico->isEmpty())
            <p>Nenhum projeto encontrado no histórico.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Projeto</th>
                        <th>Cliente</th>
                        <th>Tipo de Vínculo</th>
                        <th>Função</th>
                        <th>Carga Horária</th>
                        <th>Data Início</th>
                        <th>Data Fim</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($historico as $item)
                        <tr>
                            <td>{{ $item->projeto->nome }}</td>
                            <td>{{ $item->projeto->cliente }}</td>
                            <td>{{ $item->tipo_vinculo }}</td>
                            <td>{{ $item->funcao }}</td>
                            <td>{{ $item->carga_horaria }}h</td>
                            <td>{{ $item->data_inicio->format('d/m/Y') }}</td>
                            <td>{{ $item->data_fim ? $item->data_fim->format('d/m/Y') : 'Atual' }}</td>
                            <td>{{ $item->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="footer">
            <p>Laboratório de Computação Inteligente Aplicada (LACINA) - Universidade Federal de Campina Grande</p>
        </div>
    </div>
</body>
</html>
