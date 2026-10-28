<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Протокол соревнования</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #202b38; }
        h1 { font-size: 19px; margin: 8px 0; }
        h2 { font-size: 13px; margin: 8px 0 14px; }
        .header { text-align: center; }
        .draft { color: #9a3c16; font-weight: bold; }
        .details { line-height: 1.6; margin: 12px 0; }
        table { border-collapse: collapse; width: 100%; table-layout: fixed; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th, td { border: 1px solid #b8c2cc; padding: 7px 5px; overflow-wrap: break-word; vertical-align: top; }
        th { background-color: #e9eef3; text-align: left; }
        .number { text-align: center; }
        .footer { margin-top: 14px; line-height: 1.6; }
    </style>
</head>
<body>
@php
    $statusLabels = ['finished' => 'Завершил', 'dns' => 'Не стартовал', 'dnf' => 'Не завершил', 'dsq' => 'Дисквалифицирован'];
    $levelLabels = ['regional' => 'Региональный', 'interregional' => 'Межрегиональный', 'national' => 'Всероссийский'];
    $formatLabels = ['offline' => 'Очный', 'online' => 'Дистанционный', 'hybrid' => 'Смешанный'];
@endphp
<div class="header">
    <div>Федерация спортивного программирования Оренбургской области</div>
    <h1>Итоговый протокол</h1>
    <h2>{{ $competition->title }}</h2>
    @if (!$competition->results_published_at)
        <div class="draft">ПРОЕКТ. До публикации документ не является официальным протоколом.</div>
    @endif
</div>
<div class="details">
    <strong>Дисциплина:</strong> {{ $competition->discipline->name }}<br>
    <strong>Тип:</strong> {{ $competition->competitionType->name }}.
    <strong>Уровень:</strong> {{ $levelLabels[$competition->level] ?? $competition->level }}.
    <strong>Возрастная группа:</strong> {{ $competition->ageGroup->name }}<br>
    <strong>Формат:</strong> {{ $formatLabels[$competition->format] ?? $competition->format }}.
    <strong>Место проведения:</strong> {{ $competition->location ?? 'Не указано' }}<br>
    <strong>Даты:</strong> {{ $competition->starts_at->format('d.m.Y H:i') }} - {{ $competition->ends_at->format('d.m.Y H:i') }}
    ({{ config('app.timezone') }})
</div>
<table>
    <thead>
    <tr>
        <th style="width: 4%">№</th>
        <th style="width: 6%">Место</th>
        <th style="width: 25%">ФИО спортсмена</th>
        <th style="width: 26%">Организация</th>
        <th style="width: 12%">Результат / баллы</th>
        <th style="width: 19%">Статус</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($rows as $row)
        <tr>
            <td class="number">{{ $row['position'] }}</td>
            <td class="number">{{ $row['place'] ?? '-' }}</td>
            <td>{{ $row['full_name'] }}</td>
            <td>{{ $row['organization'] ?? '-' }}</td>
            <td>{{ $row['score'] ?? '-' }}</td>
            <td>{{ $statusLabels[$row['result_status']] ?? 'Результат не заполнен' }}</td>
        </tr>
    @empty
        <tr><td colspan="6">Нет допущенных участников</td></tr>
    @endforelse
    </tbody>
</table>
<div class="footer">
    <strong>Допущено:</strong> {{ $rows->count() }}.
    <strong>Завершили:</strong> {{ $counts->get('finished', 0) }}.
    <strong>Не стартовали:</strong> {{ $counts->get('dns', 0) }}.
    <strong>Не завершили:</strong> {{ $counts->get('dnf', 0) }}.
    <strong>Дисквалифицированы:</strong> {{ $counts->get('dsq', 0) }}.
    @if ($counts->get('missing', 0))
        <strong>Не заполнено:</strong> {{ $counts->get('missing') }}.
    @endif
    <br>Дата формирования: {{ $generatedAt->format('d.m.Y H:i') }} ({{ config('app.timezone') }}).<br>
    @forelse ($competition->judgeAssignments as $assignment)
        {{ $assignment->position }}:
        {{ $assignment->judge->user->last_name }} {{ $assignment->judge->user->first_name }} {{ $assignment->judge->user->middle_name }}<br>
    @empty
        Судейская коллегия: реквизиты не заполнены.
    @endforelse
</div>
</body>
</html>
