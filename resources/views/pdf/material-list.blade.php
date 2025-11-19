<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lista de Materiais - Projeto #{{ $project->id }}</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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
        .category-header {
            background-color: #E0E7FF;
            font-weight: bold;
            padding: 8px;
            margin-top: 15px;
        }
        .total-row {
            background-color: #4F46E5;
            color: white;
            font-weight: bold;
            font-size: 12pt;
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
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Lista de Materiais</h1>
        <p>Projeto: {{ $project->name }} (#{{ str_pad($project->id, 8, '0', STR_PAD_LEFT) }})</p>
        <p style="font-size: 8pt;">Gerado em: {{ $generatedAt }}</p>
    </div>

    @php
        $currentCategory = '';
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Item</th>
                <th style="width: 10%;" class="text-center">Qtd</th>
                <th style="width: 10%;" class="text-center">Unid.</th>
                <th style="width: 15%;" class="text-right">Valor Unit.</th>
                <th style="width: 15%;" class="text-right">Valor Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($materialList['items'] as $index => $item)
                @if($currentCategory !== $item['category'])
                    @php $currentCategory = $item['category']; @endphp
                    <tr>
                        <td colspan="6" class="category-header">{{ $item['category'] }}</td>
                    </tr>
                @endif
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item['name'] }}</strong><br>
                        <small style="color: #666;">{{ $item['description'] }}</small>
                    </td>
                    <td class="text-center">{{ $item['quantity'] }}</td>
                    <td class="text-center">{{ $item['unit'] }}</td>
                    <td class="text-right">R$ {{ number_format($item['unit_price'], 2, ',', '.') }}</td>
                    <td class="text-right"><strong>R$ {{ number_format($item['total_price'], 2, ',', '.') }}</strong></td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" class="text-right">VALOR TOTAL DO PROJETO:</td>
                <td class="text-right">R$ {{ number_format($materialList['total_cost'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 30px; padding: 15px; background-color: #FEF3C7; border-left: 4px solid #F59E0B;">
        <p style="margin: 0; font-size: 9pt;"><strong>Observações:</strong></p>
        <ul style="font-size: 9pt; margin: 10px 0; line-height: 1.6;">
            <li>Os valores apresentados são estimativas e podem variar conforme fornecedor</li>
            <li>Recomenda-se adquirir 10% a mais de materiais para possíveis correções</li>
            <li>Verificar disponibilidade dos itens antes de iniciar o projeto</li>
            <li>Os preços não incluem mão de obra de instalação</li>
        </ul>
    </div>

    <div style="margin-top: 20px; padding: 15px; background-color: #DBEAFE; border-left: 4px solid #3B82F6;">
        <p style="margin: 0; font-size: 9pt;"><strong>Resumo por Categoria:</strong></p>
        @php
            $categoryTotals = [];
            foreach($materialList['items'] as $item) {
                if(!isset($categoryTotals[$item['category']])) {
                    $categoryTotals[$item['category']] = 0;
                }
                $categoryTotals[$item['category']] += $item['total_price'];
            }
        @endphp
        <table style="margin-top: 10px; width: 50%;">
            @foreach($categoryTotals as $category => $total)
            <tr>
                <td style="border: none; padding: 4px;"><strong>{{ $category }}:</strong></td>
                <td style="border: none; padding: 4px; text-align: right;">R$ {{ number_format($total, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="footer">
        <p>FitFab - Lista de Materiais | Projeto #{{ $project->id }} | Página 1</p>
    </div>
</body>
</html>
