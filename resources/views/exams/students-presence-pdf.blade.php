<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: "Arial", "Tahoma", sans-serif;
            font-size: 12px;
            color: #111827;
        }

        h1 {
            color: #9A0002;
            margin-bottom: 5px;
        }

        .info {
            margin-bottom: 20px;
            color: #444;
        }

        .ar {
            direction: rtl;
            unicode-bidi: plaintext;
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #9A0002;
            color: white;
            padding: 10px;
            border: 1px solid #9A0002;
        }

        td {
            padding: 12px;
            border: 1px solid #ddd;
        }

        .signature {
            height: 35px;
        }
    </style>
</head>
<body>

<h1>Liste de presence</h1>

<div class="info">
    <strong>Examen :</strong>
    <span class="ar" dir="rtl">{{ $exam->title }}</span><br>

    <strong>Cours :</strong>
    <span class="ar" dir="rtl">{{ $exam->course_name }}</span><br>

    <strong>Enseignant :</strong>
    {{ $exam->teacher_name ?? auth()->user()->full_name }}
</div>

<table>
    <thead>
        <tr>
            <th style="width: 8%">N</th>
            <th style="width: 20%">Code</th>
            <th style="width: 25%">Prenom</th>
            <th style="width: 25%">Nom</th>
            <th style="width: 22%">Signature</th>
        </tr>
    </thead>
    <tbody>
        @forelse($students as $student)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $student->student_code }}</td>
                <td class="ar" dir="rtl">{{ $student->first_name }}</td>
                <td class="ar" dir="rtl">{{ $student->last_name }}</td>
                <td class="signature"></td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center;">Aucun etudiant trouve.</td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>