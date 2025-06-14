<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <title>Programmation PDF</title>
    <style>
        @page {
            margin: 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            width: 60px;
        }

        h2 {
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .line {
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            text-transform: uppercase;
        }

        th {
            background-color: #eee;
        }
    </style>
</head>

<body>
    {{-- En-tête avec logo et titre --}}
    <div class="header">
        <img src="{{ public_path('logo.png') }}" alt="Logo" class="logo" />
        <h2>
            PROGRAMMATION DE {{ strtoupper($programmation->type) }}
            DU {{ \Carbon\Carbon::parse($programmation->date_prog)->format('d/m/Y')
        }}
        </h2>
    </div>
    <div class="line"></div>

    {{-- Tableau des étudiants --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nom & Prénom(s)</th>
                <th>Identifiants</th>
                <th>Téléphone</th>
                <th>Auto-école</th>
                <th>Catégorie</th>
            </tr>
        </thead>
        <tbody>
            @foreach($etudiants as $index => $e)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $e->nom ?? '-' }}</strong> {{ $e->prenom ?? '-' }}</td>
                    <td>{{ $e->type_piece }} - {{ $e->num_piece }}</td>
                    <td>{{ $e->num_telephone ?? '-' }}</td>
                    <td>{{ $e->nom_autoEc ?? '-' }}</td>
                    <td>{{ $e->categorie ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>