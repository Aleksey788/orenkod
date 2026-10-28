<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Проект приказа</title>
    <style>
        @page { margin: 42px; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 11px; color: #243242; line-height: 1.6; }
        h1 { font-size: 18px; text-align: center; }
        h2 { font-size: 14px; text-align: center; }
        .header { text-align: center; }
        .draft { font-weight: bold; color: #9a3c16; text-align: center; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 10px; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th, td { border: 1px solid #b8c2cc; padding: 7px; vertical-align: top; overflow-wrap: break-word; }
        th { background-color: #e9eef3; text-align: left; }
    </style>
</head>
<body>
<div class="header">Федерация спортивного программирования Оренбургской области</div>
<div class="draft">ПРОЕКТ. ДОКУМЕНТ НЕ УТВЕРЖДЁН.</div>
<h1>ПРИКАЗ</h1>
<h2>О формировании сборной Оренбургской области</h2>
<p>Сезон: {{ $team->ratingSeason->name }}.<br>
Дисциплина: {{ $team->discipline->name }}.<br>
Возрастная группа: {{ $team->ageGroup->name }}.<br>
Состав: {{ $team->name ?? 'Сборная региона' }}, версия {{ $team->version }}.</p>
<p>Основание подготовки проекта: сохранённые решения федерации о включении спортсменов
с учётом рейтинга выбранного сезона и дисциплины.</p>
<p>Основания включения, сохранённые федерацией:
    {{ $team->members->pluck('notes')->filter()->unique()->implode('; ') ?: 'не указаны' }}.
</p>
<p>Предлагается утвердить следующий состав:</p>
<table>
    <thead><tr><th style="width: 7%">№</th><th style="width: 45%">ФИО спортсмена</th><th style="width: 42%">Организация</th></tr></thead>
    <tbody>
    @forelse ($team->members as $member)
        <tr><td>{{ $loop->iteration }}</td><td>{{ $member->rating_snapshot['full_name'] ?? 'Снимок данных не заполнен' }}</td>
            <td>{{ $member->rating_snapshot['organization'] ?? '-' }}</td></tr>
    @empty
        <tr><td colspan="3">Участники не включены</td></tr>
    @endforelse
    </tbody>
</table>
<p>Дата подготовки проекта: {{ $generatedAt->format('d.m.Y') }}.<br>
Номер и дата официального приказа: не присвоены.<br>
Реквизиты утверждения: не заполнены.</p>
<p>Проект требует проверки федерацией и сверки с утверждённой формой. Публикация состава
не означает подписания или утверждения этого приказа.</p>
</body>
</html>
