<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Plano de Corte - Projeto #{{ $project->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #4F46E5;
            margin: 0;
            font-size: 20pt;
        }
        .summary-box {
            background-color: #F3F4F6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .summary-item {
            display: inline-block;
            width: 48%;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #4F46E5;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 10pt;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #E5E7EB;
        }
        tr:nth-child(even) {
            background-color: #F9FAFB;
        }
        .section-title {
            background-color: #4F46E5;
            color: white;
            padding: 8px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 12pt;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #E5E7EB;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Plano de Corte</h1>
        <p>Projeto: {{ $project->name }} (#{{ str_pad($project->id, 8, '0', STR_PAD_LEFT) }})</p>
        <p style="font-size: 8pt;">Gerado em: {{ $generatedAt }}</p>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <strong>Material:</strong> {{ $project->material->name }}
        </div>
        <div class="summary-item">
            <strong>Espessura:</strong> {{ $project->material->thickness }} mm
        </div>
        <div class="summary-item">
            <strong>Chapas Necessárias:</strong> {{ $cuttingDetails['sheets_needed'] }}
        </div>
        <div class="summary-item">
            <strong>Área Total:</strong> {{ number_format($cuttingDetails['total_area'], 2, ',', '.') }} m²
        </div>
        <div class="summary-item">
            <strong>Área por Chapa:</strong> {{ number_format($cuttingDetails['sheet_area'], 2, ',', '.') }} m²
        </div>
        <div class="summary-item">
            <strong>Desperdício:</strong> {{ $cuttingDetails['waste_percentage'] }}%
        </div>
    </div>

    <div class="section-title">Lista de Peças para Corte</div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 30%;">Nome da Peça</th>
                <th style="width: 15%;">Largura</th>
                <th style="width: 15%;">Altura</th>
                <th style="width: 10%;">Qtd</th>
                <th style="width: 25%;">Fita de Borda</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuttingDetails['pieces'] as $index => $piece)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $piece['name'] }}</strong></td>
                <td>{{ number_format($piece['width'], 0, ',', '.') }} mm</td>
                <td>{{ number_format($piece['height'], 0, ',', '.') }} mm</td>
                <td style="text-align: center;">{{ $piece['quantity'] }}</td>
                <td>{{ $piece['edge_banding'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title" style="margin-top: 30px;">Observações Importantes</div>
    <ul style="font-size: 9pt; line-height: 1.6;">
        <li>Verificar sentido das fibras do material antes do corte</li>
        <li>Adicionar 2mm de folga para aplicação da fita de borda</li>
        <li>Utilizar serra circular com disco apropriado para {{ $project->material->type }}</li>
        <li>Conferir dimensões antes de iniciar os cortes</li>
        <li>Identificar cada peça com etiquetas após o corte</li>
        <li>Guardar sobras de material para possíveis reparos futuros</li>
    </ul>

    <div class="footer">
        <p>FitFab - Plano de Corte | Projeto #{{ $project->id }} | Página 1</p>
    </div>
</body>
</html>
