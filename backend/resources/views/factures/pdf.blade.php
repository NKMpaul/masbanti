<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #1890ff; }
        .titre-facture { text-align: right; }
        .titre-facture h1 { font-size: 28px; color: #333; margin: 0; }
        .titre-facture p { margin: 5px 0; color: #666; }
        .infos { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .infos-bloc { width: 45%; }
        .infos-bloc h3 { border-bottom: 2px solid #1890ff; padding-bottom: 5px; color: #1890ff; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #1890ff; color: white; padding: 10px; text-align: left; }
        td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        tr:nth-child(even) { background: #f9f9f9; }
        .totaux { float: right; width: 300px; }
        .totaux table td { border: none; padding: 5px 10px; }
        .totaux .total-ttc { font-size: 16px; font-weight: bold; color: #1890ff; border-top: 2px solid #1890ff; }
        .footer { margin-top: 50px; text-align: center; color: #999; font-size: 11px; border-top: 1px solid #eee; padding-top: 10px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 11px; }
        .badge-green { background: #f6ffed; color: #52c41a; border: 1px solid #b7eb8f; }
        .badge-orange { background: #fff7e6; color: #fa8c16; border: 1px solid #ffd591; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="logo">Masbanti</div>
            <p>Franchise de Pressing</p>
            <p>{{ $facture->agence->adresse }}, {{ $facture->agence->ville }}</p>
            <p>{{ $facture->agence->telephone }}</p>
            <p>{{ $facture->agence->email }}</p>
        </div>
        <div class="titre-facture">
            <h1>FACTURE</h1>
            <p><strong>N° :</strong> {{ $facture->numero_facture }}</p>
            <p><strong>Date :</strong> {{ $facture->date_emission->format('d/m/Y') }}</p>
            <p>
                <span class="badge {{ $facture->statut === 'PAYEE' ? 'badge-green' : 'badge-orange' }}">
                    {{ $facture->statut }}
                </span>
            </p>
        </div>
    </div>

    <div class="infos">
        <div class="infos-bloc">
            <h3>Agence</h3>
            <p><strong>{{ $facture->agence->nom }}</strong></p>
            <p>{{ $facture->agence->adresse }}</p>
            <p>{{ $facture->agence->ville }}</p>
        </div>
        <div class="infos-bloc">
            <h3>Client</h3>
            <p><strong>{{ $facture->client->nom }} {{ $facture->client->prenom }}</strong></p>
            <p>{{ $facture->client->user->email }}</p>
            <p>{{ $facture->client->telephone }}</p>
            <p>{{ $facture->client->adresse }}</p>
        </div>
    </div>

    <h3>Commande : {{ $facture->commande->numero_commande }}</h3>

    <table>
        <thead>
            <tr>
                <th>Article</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facture->commande->articles as $article)
            <tr>
                <td>{{ $article->typeArticle->nom ?? 'Article' }}</td>
                <td>{{ $article->quantite }}</td>
                <td>{{ number_format($article->prix_unitaire, 2) }} DH</td>
                <td>{{ number_format($article->quantite * $article->prix_unitaire, 2) }} DH</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totaux">
        <table>
            <tr>
                <td>Montant HT</td>
                <td><strong>{{ number_format($facture->montant_ht, 2) }} DH</strong></td>
            </tr>
            <tr>
                <td>TVA ({{ $facture->tva }}%)</td>
                <td><strong>{{ number_format($facture->montant_ht * $facture->tva / 100, 2) }} DH</strong></td>
            </tr>
            @if($facture->remise_fidelite > 0)
            <tr>
                <td>Remise fidélité</td>
                <td><strong>-{{ number_format($facture->remise_fidelite, 2) }} DH</strong></td>
            </tr>
            @endif
            <tr class="total-ttc">
                <td>TOTAL TTC</td>
                <td><strong>{{ number_format($facture->montant_ttc, 2) }} DH</strong></td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    @if($facture->paiements->count() > 0)
    <h3>Paiements</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Mode</th>
                <th>Référence</th>
                <th>Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facture->paiements as $paiement)
            <tr>
                <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                <td>{{ $paiement->mode_paiement }}</td>
                <td>{{ $paiement->reference ?? '—' }}</td>
                <td>{{ number_format($paiement->montant, 2) }} DH</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <p>Masbanti — Franchise de Pressing | contact@masbanti.ma | +212 522 000 000</p>
        <p>Merci de votre confiance !</p>
    </div>
</body>
</html>