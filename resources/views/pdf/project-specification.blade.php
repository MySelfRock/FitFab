<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Especificação do Projeto #{{ $project->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #4F46E5;
            margin: 0;
            font-size: 24pt;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background-color: #4F46E5;
            color: white;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 14pt;
            font-weight: bold;
        }
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 8px;
            border-bottom: 1px solid #E5E7EB;
            width: 40%;
        }
        .info-value {
            display: table-cell;
            padding: 8px;
            border-bottom: 1px solid #E5E7EB;
        }
        .dimensions-box {
            background-color: #F3F4F6;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        .dimension-item {
            font-size: 12pt;
            margin: 5px 0;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #E5E7EB;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>FitFab</h1>
        <p>Especificação Técnica do Projeto</p>
        <p style="font-size: 9pt;">Gerado em: {{ $generatedAt }}</p>
    </div>

    <div class="section">
        <div class="section-title">Informações do Projeto</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Número do Projeto:</div>
                <div class="info-value">#{{ str_pad($project->id, 8, '0', STR_PAD_LEFT) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Nome:</div>
                <div class="info-value">{{ $project->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Cliente:</div>
                <div class="info-value">{{ $project->user->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Data de Criação:</div>
                <div class="info-value">{{ $project->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">{{ ucfirst($project->status) }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Template Utilizado</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nome:</div>
                <div class="info-value">{{ $project->template->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Categoria:</div>
                <div class="info-value">{{ $project->template->category }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Descrição:</div>
                <div class="info-value">{{ $project->template->description }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Dimensões do Projeto</div>
        <div class="dimensions-box">
            <div class="dimension-item">
                <strong>Largura:</strong> {{ number_format($project->width, 0, ',', '.') }} mm ({{ number_format($project->width / 10, 1, ',', '.') }} cm)
            </div>
            <div class="dimension-item">
                <strong>Altura:</strong> {{ number_format($project->height, 0, ',', '.') }} mm ({{ number_format($project->height / 10, 1, ',', '.') }} cm)
            </div>
            <div class="dimension-item">
                <strong>Profundidade:</strong> {{ number_format($project->depth, 0, ',', '.') }} mm ({{ number_format($project->depth / 10, 1, ',', '.') }} cm)
            </div>
            <div class="dimension-item" style="margin-top: 10px; font-size: 13pt;">
                <strong>Volume Total:</strong> {{ number_format(($project->width * $project->height * $project->depth) / 1000000000, 2, ',', '.') }} m³
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Material Especificado</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nome:</div>
                <div class="info-value">{{ $project->material->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tipo:</div>
                <div class="info-value">{{ $project->material->type }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Espessura:</div>
                <div class="info-value">{{ $project->material->thickness }} mm</div>
            </div>
            <div class="info-row">
                <div class="info-label">Cor/Acabamento:</div>
                <div class="info-value">{{ $project->material->color }} - {{ $project->material->finish }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Marca:</div>
                <div class="info-value">{{ $project->material->brand }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Dimensões da Chapa:</div>
                <div class="info-value">{{ $project->material->width }} × {{ $project->material->height }} mm</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>FitFab - Sistema de Geração de Projetos de Móveis | Página 1</p>
    </div>
</body>
</html>
