<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->numero_facture }}</title>
    <style>
        /* Configuration globale pour DomPDF */
        @page { margin: 30px; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
        
        /* Utilitaires de tableaux pour la mise en page (remplace le Flexbox) */
        .w-100 { width: 100%; }
        .align-top { vertical-align: top; }
        
        .logo { font-size: 24px; font-weight: bold; color: #1890ff; }
        .titre-facture { text-align: right; }
        .titre-facture h1 { font-size: 26px; color: #333; margin: 0; }
        .titre-facture p { margin: 3px 0; color: #666; }
        
        .infos-bloc { width: 48%; }
        .infos-bloc h3 { border-bottom: 2px solid #1890ff; padding-bottom: 5px; color: #1890ff; margin-top: 0; }
        
        /* Tableaux de données standard */
        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-data th { background: #1890ff; color: white; padding: 10px; text-align: left; font-size: 11px; }
        .table-data td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        
        .totaux { float: right; width: 280px; margin-top: 10px; }
        .totaux table { width: 100%; border-collapse: collapse; }
        .totaux td { padding: 5px 10px; border: none; }
        .total-ttc { font-size: 15px; font-weight: bold; color: #1890ff; border-top: 2px solid #1890ff !important; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; color: #999; font-size: 10px; border-top: 1px solid #eee; padding-top: 10px; }
        
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-green { background-color: #f6ffed; color: #52c41a; border: 1px solid #b7eb8f; }
        .badge-orange { background-color: #fff7e6; color: #fa8c16; border: 1px solid #ffd591; }
    </style>
</head>
<body>

    <table class="w-100" style="margin-bottom: 30px;">
        <tr>
            <td class="align-top" style="border: none; padding: 0;">
                <div class="logo">Masbanti</div>
                <p style="margin: 3px 0;">Franchise de Pressing</p>
                <p style="margin: 3px 0;">{{ $facture->agence->adresse ?? 'Adresse' }}, {{ $facture->agence->ville ?? 'Casablanca' }}</p>
                <p style="margin: 3px 0;">{{ $facture->agence->telephone ?? '—' }}</p>
                <p style="margin: 3px 0;">{{ $facture->agence->email ?? '—' }}</p>
            </td>
            <td class="align-top titre-facture" style="border: none; padding: 0;">
                <h1>FACTURE</h1>
                <p><strong>N° :</strong> {{ $facture->numero_facture }}</p>
                <p><strong>Date :</strong> {{ is_string($facture->date_emission) ? \Carbon\Carbon::parse($facture->date_emission)->format('d/m/Y') : $facture->date_emission->format('d/m/Y') }}</p>
                <p style="margin-top: 8px;">
                    <span class="badge {{ $facture->statut === 'PAYEE' ? 'badge-green' : 'badge-orange' }}">
                        {{ $facture->statut }}
                    </span>
                </p>
            </td>
        </tr>
    </table>

    <table class="w-100" style="margin-bottom: 30px;">
        <tr>
            <td class="align-top infos-bloc" style="border: none; padding: 0 15px 0 0;">
                <h3>Agence</h3>
                <p><strong>{{ $facture->agence->nom ?? 'Agence' }}</strong></p>
                <p>{{ $facture->agence->adresse ?? 'Adresse' }}</p>
                <p>{{ $facture->agence->ville ?? 'Ville' }}</p>
            </td>
            <td class="align-top infos-bloc" style="border: none; padding: 0 0 0 15px;">
                <h3>Client</h3>
                <p><strong>{{ $facture->client->nom }} {{ $facture->client->prenom }}</strong></p>
                <p>{{ $facture->client->user->email ?? $facture->client->email }}</p>
                <p>{{ $facture->client->telephone }}</p>
                <p>{{ $facture->client->adresse }}</p>
            </td>
        </tr>
    </table>

    <h3 style="color: #333; margin-bottom: 10px;">Commande : {{ $facture->commande->numero_commande }}</h3>

    <table class="table-data">
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
                <td style="text-align: right;"><strong>{{ number_format($facture->montant_ht, 2) }} DH</strong></td>
            </tr>
            <tr>
                <td>TVA ({{ $facture->tva }}%)</td>
                <td style="text-align: right;"><strong>{{ number_format(($facture->montant_ht * $facture->tva) / 100, 2) }} DH</strong></td>
            </tr>
            @if(isset($facture->remise_fidelite) && $facture->remise_fidelite > 0)
            <tr>
                <td>Remise fidélité</td>
                <td style="text-align: right;"><strong>-{{ number_format($facture->remise_fidelite, 2) }} DH</strong></td>
            </tr>
            @endif
            <tr class="total-ttc">
                <td>TOTAL TTC</td>
                <td style="text-align: right;"><strong>{{ number_format($facture->montant_ttc, 2) }} DH</strong></td>
            </tr>
        </table>
    </div>

    <div style="clear: both; height: 1px;"></div>

    @if($facture->paiements->count() > 0)
    <h3 style="margin-top: 30px; margin-bottom: 10px;">Paiements enregistrés</h3>
    <table class="table-data">
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
                <td>{{ is_string($paiement->date_paiement) ? \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') : $paiement->date_paiement->format('d/m/Y') }}</td>
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