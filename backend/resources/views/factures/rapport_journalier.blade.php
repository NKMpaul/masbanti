<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport Journalier Masbanti</title>
    <style>
        body { font-family: sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2e7d32; padding-bottom: 10px; }
        .box { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .highlight { font-size: 24px; color: #2e7d32; font-weight: bold; }
        .kpi-title { font-weight: bold; color: #555; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Bilan Journalier Masbanti</h1>
        <p>Date : <strong>{{ $date }}</strong></p>
    </div>

    <div class="box">
        <div class="kpi-title">💰 Chiffre d'Affaires du Jour</div>
        <p class="highlight">{{ number_format($ca, 2) }} DH</p>
    </div>

    <div class="box">
        <div class="kpi-title">📦 Volume des Commandes</div>
        <p>Nombre total de commandes aujourd'hui : <strong>{{ $nbCommandes }}</strong></p>
    </div>

    <div class="box">
        <div class="kpi-title">🚚 Efficacité de Livraison</div>
        <p>Taux de complétion des livraisons : <strong>{{ number_format($tauxLivraison, 1) }}%</strong></p>
    </div>
</body>
</html>