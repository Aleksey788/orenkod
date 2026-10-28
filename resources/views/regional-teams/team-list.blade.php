<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список сборной</title>
    <style>
        @page { margin: 36px; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #243242; }
        h1 { font-size: 18px; text-align: center; margin: 16px 0; }
        .header { text-align: center; }
        .details, .footer { line-height: 1.6; margin: 14px 0; }
        .draft { color: #9a3c16; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th, td { border: 1px solid #b8c2cc; padding: 7px 5px; vertical-align: top; overflow-wrap: break-word; }
        th { background-color: #e9eef3; text-align: left; }
    </style>
</head>
<body>
<div class="header">Федерация спортивного программирования Оренбургской области</div>
<h1>Список сборной Оренбургской области</h1>
@if ($team->status === 'draft')
    <div class="draft">ПРОЕКТ СОСТАВА</div>
@endif
<div class="details">
    <strong>{{ $team->name ?? 'Сборная региона' }}</strong><br>
    Сезон: {{ $team->ratingSeason->name }}. Дисциплина: {{ $team->discipline->name }}.<br>
    Возрастная группа: {{ $team->ageGroup->name }}. Версия состава: {{ $team->version }}.<br>
    Дата формирования: {{ $generatedAt->format('d.m.Y') }}.
</div>
<table>
    <thead><tr>
        <th style="width: 5%">№</th><th style="width: 31%">ФИО спортсмена</th><th style="width: 32%">Организация</th>
        <th style="width: 14%">Рейтинг, баллы</th><th style="width: 12%">Место в рейтинге</th>
    </tr></thead>
    <tbody>
    @forelse ($team->members as $member)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $member->rating_snapshot['full_name'] ?? 'Снимок данных не заполнен' }}</td>
            <td>{{ $member->rating_snapshot['organization'] ?? '-' }}</td>
            <td>{{ $member->rating_snapshot['total_points'] ?? '-' }}</td>
            <td>{{ $member->rating_snapshot['rating_rank'] ?? '-' }}</td>
        </tr>
    @empty
        <tr><td colspan="5">Участники не включены</td></tr>
    @endforelse
    </tbody>
</table>
<div class="footer">
    Всего спортсменов: {{ $team->members->count() }}.<br>
    Баллы и рейтинговые места сохранены на момент включения спортсмена в состав.
    Порядковый номер строки не является местом в сборной.<br>
    Оформление необходимо сверить с утверждённой формой федерации.
</div>
</body>
</html>
