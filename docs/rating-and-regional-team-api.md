# Рейтинг спортсменов и сборная Оренбургской области

Backend сохраняет Laravel Session, HttpOnly cookies, CSRF и архитектуру Controller → Form Request → DTO → Service → Model → API Resource. Новые маршруты находятся в `routes/rating.php`, подключённом из `routes/web.php`. Для JSON передавайте `Accept: application/json`; изменяющие запросы также требуют актуальный CSRF и cookie сессии.

## Переиспользованная схема

Проверены миграции и реальные таблицы PostgreSQL: `rating_seasons`, `rating_rules`, `athlete_restrictions`, `regional_teams`, `regional_team_members`, `sport_ranks`, `athlete_ranks` уже существовали и были применены. Переиспользованы их модели и связи, опубликованные результаты предыдущего этапа и `AuditLog`.

В `composer.json` Dompdf уже находился в production-разделе `require`, версия 3.1.6. Перенос и обновление зависимостей не потребовались; Laravel Boost сохранён.

Добавлена и применена миграция `database/migrations/2026_10_28_111207_extend_rating_and_regional_team_management.php`:

- `competitions.rating_category` - явная категория, nullable для старых соревнований, с индексом.
- `rating_seasons.status`, `rating_snapshot`, `ratings_calculated_at`, `rating_version` - жизненный цикл и снимок закрытого сезона. Старые строки с `is_active=true` переводятся в `active`, остальные остаются `draft`; старый флаг сохраняется и синхронизируется сервисом.
- `regional_teams.name`, `min_sport_rank_id`, `version`, `supersedes_team_id`, `published_at`, `published_by_user_id`, `list_path` - именование, отбор, версии и публикация. Существующий `order_path` хранит PDF проекта приказа, а не подписанный приказ.
- Уникальность состава по сезону/дисциплине/возрастной группе заменена уникальностью тех же полей и `version`. Все старые составы сохраняют версию 1.
- `regional_team_members.rating_snapshot`, `included_by_user_id`, `removed_at`, `removal_reason` - снимок основания отбора и сохранение истории исключений. Существующая уникальность спортсмена внутри состава сохранена.

Существующие таблицы, спортсмены, результаты и старые миграции не удаляются. Откат новой миграции блокируется, если уже созданы несколько версий одного состава: это защищает историю от потери.

## Классификация соревнования

Баллы определяются явной категорией, а не названием соревнования или только `level`:

| `rating_category` | Значение | Требуемый `level` | Места 1 / 2 / 3 |
| --- | --- | --- | --- |
| `national_championship` | Чемпионат России | `national` | 100 / 80 / 60 |
| `national_youth` | Первенство России | `national` | 80 / 60 / 40 |
| `interregional` | Межрегиональные соревнования | `interregional` | 60 / 45 / 30 |
| `regional` | Региональные соревнования | `regional` | 40 / 30 / 20 |

Администратор задаёт категорию отдельным действием, в том числе для ранее опубликованного соревнования:

```http
PATCH /admin/competitions/7/rating-category
```

```json
{"rating_category":"regional","reason":"Классификация согласно положению соревнования"}
```

Ответ: `{"data":{"competition_id":7,"rating_category":"regional"}}`. Несоответствие уровню даёт 422. Название соревнования не анализируется. Наличие опубликованных результатов соревнования с отсутствующей или неверной категорией блокирует рейтинг соответствующей дисциплины/сезона с 422 и перечнем ID соревнований. Нельзя менять классификацию соревнования, попавшего в уже закрытый сезон. Действие фиксируется в аудите.

## Сезоны и правила

Пример создания сезона:

```http
POST /admin/rating-seasons
```

```json
{
  "name":"Сезон 2026",
  "year":2026,
  "starts_on":"2026-01-01",
  "ends_on":"2026-12-31",
  "status":"active",
  "stability_coefficient":"1.20",
  "min_podium_results":2
}
```

Состояния: `draft` → `active` → `closed`. Активный сезон нельзя вернуть в черновик. Публичный рейтинг чернового сезона недоступен. Конец не может предшествовать началу; пересечения интервалов запрещены в едином рейтинговом календаре. Существующий уникальный `year` сохранён: один сезон на год. Создание/изменение сериализуется в PostgreSQL блокировкой таблицы сезонов в транзакции, поэтому защищён и случай двух одновременных первых вставок.

При создании сезона автоматически добавляются 12 рекомендуемых правил. `RatingRuleSeeder` добавляет отсутствующие базовые правила в незакрытые сезоны и не перезаписывает существующие значения федерации. Он не создаёт сезон автоматически. Закрытые сезоны Seeder не изменяет.

```http
PUT /admin/rating-rules/12
```

```json
{"rating_season_id":1,"category":"regional","place":3,"points":25}
```

Для дополнительного места используется POST `/admin/rating-rules` с той же структурой, например `place=4, points=10`. Область правила - сезон, категория и место; правило общее для всех дисциплин. Уникальность обеспечена БД и проверкой сервиса. Перенос правила в другой сезон запрещён. Баллы целые, неотрицательные; технический максимум - 1 000 000 на правило. Место правила от 1 до 32767. Дополнительные дисциплинарные переопределения в MVP не введены.

Активный рейтинг рассчитывается на лету, поэтому изменение правила сразу влияет на следующий запрос; отдельный пересчёт активного сезона не требуется. Закрытый сезон, его параметры и правила обычным PUT не изменяются.

Закрытие выполняется PUT сезона с полной формой и `status=closed`. Оно разрешено не раньше даты `ends_on`. В транзакции создаётся снимок рейтинга всех дисциплин с `rating_version=1`. Фиксируются баллы, расшифровка, состояние допуска на дату окончания, определения возрастных групп и порядка разрядов. Внутренние поля снимка, включая дату рождения для возрастного фильтра, не выдаются API.

Поздно опубликованные результаты не изменяют закрытый снимок автоматически. Явная повторная фиксация:

```http
POST /admin/rating-seasons/1/recalculate
```

```json
{"reason":"Учтены поздно опубликованные официальные протоколы сезона"}
```

Действие разрешено только для закрытого сезона, увеличивает `rating_version`, меняет `ratings_calculated_at` и записывается в аудит. Сохранённые составы сборной и снимки включения не меняются. Это не механизм изменения правил закрытого сезона.

## Формула и точность

Учитываются результаты с одобренной заявкой и официальной публикацией (`results_published_at` не null) у соревнований со статусом `published`. Дисциплина должна совпадать; дата начала соревнования должна попадать в интервал сезона включительно. Соревнование, начавшееся до сезона и закончившееся внутри него, не включается: граница определяется именно `starts_at`.

1. Для `finished` берутся рейтинговые баллы из правила категории и официального `place`.
2. `dns`, `dnf`, `dsq` дают 0. Место 4 и далее даёт 0, если соответствующее правило не создано. Отсутствующее правило призового места даёт 422 вместо молчаливого начисления 0. Неизвестный статус официального результата также блокирует расчёт.
3. `base_points` - сумма начислений по всем подходящим соревнованиям.
4. `podium_competitions_count` - число различных соревнований с `finished` и местом 1–3.
5. Если это число не меньше `min_podium_results` (по умолчанию 2), применяется `stability_coefficient` сезона (по умолчанию 1.20); иначе 1.00.
6. `total_points = base_points × stability_coefficient`.

Например, два региональных результата: 1 место (40) и 3 место (20). Итог `(40 + 20) × 1.20 = 72.00`.

Расчёт использует целые баллы и целые сотые коэффициента, без операций сравнения дробных float. Итог выдаётся десятичной строкой с двумя знаками. Дополнительное округление не требуется: целые базовые баллы и коэффициент с двумя знаками дают точный результат. Допустимый коэффициент 1.00–9.99, порог призовых выступлений 2–32767 - технические границы настроек, а не спортивные нормативы.

Рейтинг сортируется по убыванию точных итоговых баллов. При равенстве присваивается одинаковое место по схеме 1, 1, 3. ФИО и ID служат только стабильной сортировкой отображения, а не разрешением спортивной ничьей. После фильтрации места определяются внутри выбранной группы.

## Фильтры, возраст, разряды и ограничения

```http
GET /ratings?season_id=1&discipline_id=2&age_group_id=1&min_sport_rank_id=3&as_of=2026-10-28
```

`season_id` и `discipline_id` обязательны для query-формы и личного рейтинга. В URL `/ratings/{season}/{discipline}` они берутся из пути. Остальные фильтры необязательны.

- Возраст оценивается на `ends_on` сезона по полной дате рождения. Это выбранное правило MVP, которое нужно согласовать с федерацией; оно не объявляется официальной спортивной нормой.
- Для минимального разряда используется `sport_ranks.sort_order`: большее значение означает более высокий разряд. ID не сравниваются. Порядок задаётся федерацией через PUT `/admin/sport-ranks/{rank}/rating-order` с `{"sort_order":30}`. Нулевое значение означает ненастроенный порядок и не подходит как минимальный порог.
- Учитываются только разряды той же дисциплины с `awarded_at <= as_of` и `expires_at >= as_of` либо бессрочные. Берётся наибольшее действующее `sort_order`.
- `as_of` задаёт дату проверки ограничений и действия разрядов; по умолчанию это текущая дата для живого рейтинга и `ends_on` для закрытого снимка. Параметр не сужает сезонный набор соревнований и не восстанавливает историю публикации протоколов: источник - официальные результаты, опубликованные к моменту расчёта.
- Ограничения типов `disqualification` и `suspension` действуют с `starts_on` по `ends_on` включительно. Null конца означает бессрочное ограничение. Дата до начала или после конца не исключает спортсмена. Досрочная отмена действует начиная с календарной даты `revoked_at`; запрос на более раннюю дату учитывает прежнее ограничение.
- Для закрытого снимка нельзя запросить иной `as_of`: нужен явный пересчёт для новой фиксации данных, но официальная дата оценки остаётся концом сезона.

Административная запись ограничения:

```json
{
  "athlete_id":12,
  "type":"suspension",
  "reason":"Основание решения федерации",
  "starts_on":"2026-10-01",
  "ends_on":"2026-10-31",
  "decision_number":"Решение-12"
}
```

POST `/admin/athlete-restrictions` создаёт, PUT `/{restriction}` изменяет запись. Перенос ограничения на другого спортсмена запрещён. POST `/{restriction}/revoke` требует `{"reason":"Основание отмены"}`; время отмены и администратора определяет сервер. Отменённая запись больше не редактируется. Состояние API вычисляется как `scheduled`, `active`, `expired` или `revoked`. Причины и номера решений доступны только административному API.

## Ответ рейтинга

```json
{
  "data":[{
    "athlete_id":12,
    "full_name":"Иванов Иван Иванович",
    "organization":"Учебная организация",
    "season_id":1,
    "discipline_id":2,
    "base_points":60,
    "podium_competitions_count":2,
    "stability_coefficient":"1.20",
    "total_points":"72.00",
    "rating_rank":1,
    "as_of":"2026-10-28",
    "eligible":true
  }],
  "meta":{"season_id":1,"discipline_id":2,"rating_version":0,"mode":"live"}
}
```

Расшифровка GET `/ratings/1/2/athletes/12` возвращает один объект `data` с дополнительным массивом `results`:

```json
{
  "competition_id":7,
  "title":"Кубок ОренКод",
  "starts_at":"2026-10-20T09:00:00+00:00",
  "discipline_id":2,
  "level":"regional",
  "competition_type":"Личное первенство",
  "rating_category":"regional",
  "place":1,
  "result_status":"finished",
  "performance_score":"95.50",
  "awarded_points":40,
  "rule_id":10
}
```

Публичные ответы содержат ФИО, организацию и спортивные показатели. Дата рождения, email, телефон, согласия, внутренние поля снимка и основания ограничений не возвращаются. GET `/athlete/rating` использует только профиль текущей сессии; подстановка `athlete_id` не меняет выборку. Отсутствие собственного результата возвращает пустой `data`. Публичная расшифровка другого спортсмена содержит только те же разрешённые спортивные данные.

## Ручной состав сборной и история

```http
POST /admin/regional-teams
```

```json
{
  "name":"Сборная Оренбургской области",
  "rating_season_id":1,
  "discipline_id":2,
  "age_group_id":1,
  "min_sport_rank_id":null
}
```

Создаётся пустой черновик версии 1. Сезон должен быть активным либо закрытым. Система не добавляет лидеров автоматически. GET `/{team}/candidates` использует сезон, дисциплину и фильтры состава, возвращает строки рейтинга с `eligible` и `included`. Для закрытого сезона это фиксированный рейтинг, но `eligible` дополнительно проверяется на текущую дату: новое ограничение блокирует включение даже при наличии спортсмена в историческом рейтинге.

```http
POST /admin/regional-teams/1/members
```

```json
{"athlete_id":12,"reason":"Включение по результатам сезона и решению федерации"}
```

Сохраняются спортсмен, дата, администратор, основание и снимок рейтинга на момент включения. Только подходящий кандидат может быть включён; дубль даёт 422. Состав не изменяется при смене правил или пересчёте рейтинга. Баллы/место в API состава явно названы `rating_points_at_inclusion` и `rating_rank_at_inclusion`.

DELETE `/{team}/members/{member}` требует тело `{"reason":"Основание исключения"}` и возвращает 204. Удаление логическое: запись остаётся с `removed_at`, история решений остаётся в `audit_logs`. Повторное включение в черновик активирует ту же уникальную запись с новым снимком и отдельным событием аудита. Чужой `member` другого состава даёт 404.

До публикации PUT позволяет редактировать черновик. Область отбора нельзя менять у состава с включёнными участниками или у новой версии опубликованного состава. Название допускает изменение до публикации. Календарные границы сезона с сохранёнными составами также защищены от изменения.

POST `/{team}/publish` требует непустой состав и повторно проверяет каждого спортсмена по текущему рейтингу, фильтрам и ограничениям. Рейтинговые снимки включения при этом не заменяются. Формируются оба PDF в закрытом хранилище, записываются дата/администратор публикации и аудит. При сбое файла или БД публикация откатывается, новые файлы удаляются. Повторная публикация текущей опубликованной версии возвращает существующие данные без создания новых файлов.

Обычные PUT, добавление и исключение после публикации запрещены. Для официального изменения:

```http
POST /admin/regional-teams/1/versions
```

```json
{"reason":"Официальное уточнение состава федерацией"}
```

Ответ 201 содержит новый черновик с увеличенной `version`, `supersedes_team_id` и копией предыдущих участников. Изменения выполняются в этом черновике. При его публикации предыдущий состав становится `superseded`, но остаётся доступен по своему ID и со своим PDF. Публичный список `/regional-teams` показывает только актуальные `published` версии. Если другой проект уже заменил исходную версию, публикация конкурирующего проекта отклоняется. Все переходы аудируются.

## PDF

Два Blade-шаблона:

- `resources/views/regional-teams/team-list.blade.php` - список с ФИО, организациями, сохранёнными рейтинговыми баллами и местами.
- `resources/views/regional-teams/order-draft.blade.php` - проект приказа с фактическими основаниями включения, сезоном, дисциплиной, перечнем спортсменов и датой подготовки.

Документы создаёт `RegionalTeamDocumentService` через установленный Dompdf, с DejaVu Sans, кириллицей, повторяемыми заголовками и переносом таблиц. Внешние ресурсы, PHP и JavaScript отключены. Данные экранируются Blade. Черновые документы не сохраняются в публичный диск и выдаются администратору с `private, no-store`. Опубликованные версии хранятся на закрытом диске `team_documents` в `storage/app/private/team-documents` и скачиваются через контроллеры. `storage:link` не нужен.

Проект приказа всегда помечен «ПРОЕКТ. ДОКУМЕНТ НЕ УТВЕРЖДЁН», включая документ опубликованного состава. Публикация состава не означает утверждение приказа. Номер приказа, подписи и сведения об утверждении не выдумываются. Официальный шаблон в репозитории не найден; оформление требуется сверить с формой федерации. Публично доступен только список опубликованной/исторической опубликованной версии; проект приказа остаётся административным документом.

## Все маршруты

Административные строки требуют `auth`, `verified`, `role:admin`. Это активная роль в сессии плюс наличие роли у пользователя. Личный рейтинг требует `auth`, `verified`, `role:athlete`. Все пути используют web middleware и CSRF.

| Метод | Путь | Доступ |
| --- | --- | --- |
| GET | `/ratings` | Публичный рейтинг active/closed сезона |
| GET | `/ratings/{season}/{discipline}` | Публичный рейтинг active/closed сезона |
| GET | `/ratings/{season}/{discipline}/athletes/{athlete}` | Публичная расшифровка |
| GET | `/athlete/rating` | athlete |
| GET | `/admin/rating-seasons` | admin |
| POST | `/admin/rating-seasons` | admin |
| GET | `/admin/rating-seasons/{season}` | admin |
| PUT | `/admin/rating-seasons/{season}` | admin |
| POST | `/admin/rating-seasons/{season}/recalculate` | admin, закрытый сезон |
| GET | `/admin/rating-rules?season_id=1` | admin |
| POST | `/admin/rating-rules` | admin |
| PUT | `/admin/rating-rules/{rule}` | admin |
| PATCH | `/admin/competitions/{competition}/rating-category` | admin |
| PUT | `/admin/sport-ranks/{rank}/rating-order` | admin |
| GET | `/admin/athlete-restrictions?athlete_id=12` | admin |
| POST | `/admin/athlete-restrictions` | admin |
| PUT | `/admin/athlete-restrictions/{restriction}` | admin |
| POST | `/admin/athlete-restrictions/{restriction}/revoke` | admin |
| GET | `/admin/regional-teams` | admin |
| POST | `/admin/regional-teams` | admin |
| GET | `/admin/regional-teams/{team}` | admin |
| PUT | `/admin/regional-teams/{team}` | admin |
| GET | `/admin/regional-teams/{team}/candidates` | admin |
| POST | `/admin/regional-teams/{team}/members` | admin |
| DELETE | `/admin/regional-teams/{team}/members/{member}` | admin |
| POST | `/admin/regional-teams/{team}/publish` | admin |
| POST | `/admin/regional-teams/{team}/versions` | admin |
| GET | `/admin/regional-teams/{team}/document` | admin |
| GET | `/admin/regional-teams/{team}/order-draft` | admin |
| GET | `/regional-teams` | Публичные актуальные составы |
| GET | `/regional-teams/{team}` | Публичная published/superseded версия |
| GET | `/regional-teams/{team}/document` | Публичный список published/superseded версии |

Создающие операции возвращают 201, обычное получение/изменение/публикация - 200, исключение участника - 204. Отсутствие сессии - 401; неверная роль/неподтверждённый email - 403; отсутствующая сущность или закрытый публичный черновик - 404; ошибка CSRF - 419; валидация/состояние/классификация/допуск - 422; сбой PDF/БД - 500. Валидация возвращает стандартные `message` и `errors`; внутренние подробности других ошибок нового API скрыты даже при локальном `APP_DEBUG=true`.

Служебные поля публикации, снимка, версии и пользователя не принимаются из JSON, даже в виде null или пустого значения. ID действующего администратора берётся из сессии. Неизвестные поля не используются для массового присваивания.

## Различия показателей

- `performance_score` - результат выступления, например 95.50; он не входит напрямую в формулу рейтинга.
- `awarded_points` - рейтинговые баллы за официальное место и категорию соревнования.
- `place` - официальное место на конкретном соревновании.
- `rating_rank` - место в сезонном рейтинге выбранной дисциплины/фильтров.
- Позиции спортсмена «в сборной» нет: порядковый номер в PDF - только номер строки.

## Установка и безопасная проверка

В текущем рабочем окружении новая миграция и `RatingRuleSeeder` уже выполнены. Для другого экземпляра или повторной проверки в терминале OSPanel с PHP 8.3+:

```powershell
Set-Location C:\OSPanel\home\orenkod
composer install
php artisan migrate --path=database/migrations/2026_10_28_111207_extend_rating_and_regional_team_management.php --no-interaction
php artisan db:seed --class=RatingRuleSeeder --no-interaction
php artisan test --compact
php artisan route:list --path=rating
php artisan route:list --path=regional-teams
```

Быстрые тесты по умолчанию используют SQLite `:memory:`. `RefreshDatabase` против основной базы не применяется. `tests/RatingTestCase.php` принудительно проверяет тестовое окружение и изоляцию до создания таблиц; используются фабрики и фиктивные данные. Исторический конфликт старых users-миграций из предыдущего этапа обойдён выбором нужных миграций, старые миграции не переписаны. Это не проверка полного развёртывания проекта с нуля.

Для реальной проверки PostgreSQL доступен явный режим:

```powershell
$env:RATING_TEST_POSTGRES = '1'
php artisan test --compact tests/Feature/RatingAndRegionalTeamTest.php tests/Unit/RatingFormulaTest.php
Remove-Item Env:RATING_TEST_POSTGRES
```

Он использует настройки соединения PostgreSQL приложения, требует `pdo_pgsql` и права создания баз. Для каждого теста создаётся отдельная новая БД с именем `orenkod_rating_test_<случайный суффикс>`. Перед миграциями проверяется имя; после теста удаляется только созданная этим тестом БД. Основная `orenkod` не используется для тестовых таблиц/записей. При аварийном завершении процесса может остаться временная БД с этим префиксом; перед ручной очисткой обязательно убедитесь, что это тестовая база.

SQLite проверяет поведение API, расчёты и ограничения уникальности, но не моделирует PostgreSQL `FOR UPDATE` и блокировку таблицы. PostgreSQL-режим проверяет миграции, типы, SQL и выполнение транзакционных путей на PostgreSQL; отдельный конкурентный нагрузочный тест не проводился.

## Проверка в Postman

1. Используйте `http://orenkod.local`, cookie jar и `Accept: application/json`. Получите CSRF через существующий local endpoint `GET /_dev/csrf`, передавайте `X-CSRF-TOKEN` для POST/PUT/PATCH/DELETE.
2. Войдите подтверждённым администратором через `/login`, после входа обновите CSRF; переключите активную роль через `POST /switch-role` с `{"role":"admin"}`.
3. Создайте непересекающийся сезон. Проверьте 12 правил, измените одно правило и убедитесь, что повторный Seeder сохраняет новое значение.
4. Подготовьте официально опубликованные результаты прошедших соревнований по процедуре предыдущего этапа. Явно классифицируйте соревнования. Проверьте разные категории всероссийского уровня и 422 при отсутствующей классификации.
5. Проверьте рейтинг и расшифровку. Два региональных призовых места 1 и 3 должны дать 72.00 при исходных настройках. Проверьте возраст, действующий разряд, срок/отмену ограничения и равные места.
6. Создайте сборную, откройте кандидатов и вручную добавьте спортсменов с основаниями. Проверьте отказ для дубля/неподходящего кандидата, исключение с основанием, сохранение снимка после смены правил.
7. Откройте оба административных PDF. Без сессии проверьте недоступность чернового состава/документа. Опубликуйте состав, скачайте публичный список без сессии; проект приказа должен остаться административным.
8. Попробуйте обычное изменение опубликованного состава - 422. Создайте новую версию с основанием, измените черновик, опубликуйте. Предыдущая версия и её PDF должны сохраниться, публичный список должен показывать актуальную.
9. Для завершившегося сезона закройте его. Проверьте заморозку, запрет обычного изменения правил и явный пересчёт с увеличением версии и аудитом.
10. В отдельной сессии спортсмена вызовите `/athlete/rating?season_id=...&discipline_id=...`. Подстановка чужого `athlete_id` не должна менять собственную выборку. Проверьте 401/403/419 и отсутствие приватных полей в публичных ответах.

## Ограничения MVP

Федерация должна согласовать дату возрастного отбора, значения старшинства разрядов и форму документов. Рейтинг активного сезона возвращается целиком без серверного кэша; связи загружаются пакетно, число запросов не растёт на каждого спортсмена. Расшифровка показывает текущие официальные источники живого рейтинга либо сохранённые источники закрытого снимка. Исправления закрытого сезона не включают изменение его правил; для иной нормативной версии потребуется отдельный согласованный процесс. Публично доступны только необходимые спортивные данные. Утверждение приказа, электронная подпись и DOCX в этот этап не входят.

## Фактическая проверка этапа

28.10.2026 выполнены:

- Общий прогон: 72 теста, 513 проверок; в него входят все 24 теста предыдущего этапа и 48 новых тестов/наборов данных. Основной прогон использовал SQLite in-memory.
- Дополнительно весь новый модуль: 48 тестов, 318 проверок на изолированных базах PostgreSQL, успешно. Тестовые базы создавались и удалялись автоматически; реальные записи рабочей БД тестами не создавались.
- Новая миграция применена к локальной PostgreSQL, `RatingRuleSeeder` выполнен. SQL предварительно проверен через `--pretend`.
- Синтаксис 77 изменённых/новых PHP-файлов рабочего дерева проверен, ошибок нет; Pint и `composer validate --strict` прошли. Новых Composer-зависимостей этот этап не добавляет.
- PDF для 30 спортсменов с длинными русскими ФИО/организациями: список на 2 страницах, проект приказа на 3. Проверены извлечение кириллицы, реальные основания включения, все страницы отрисованы и визуально просмотрены.

Ручная проверка Postman с настоящими cookie/CSRF и конкурентная нагрузочная проверка остаются отдельными шагами. Встроенные Feature Tests Laravel обычно пропускают CSRF, поэтому их успешный прогон не заменяет проверку 419 в реальной сессии.

## Файлы этого этапа

Созданы классы:

```text
app/DTO/Rating/RatingSeasonDTO.php
app/DTO/Rating/RatingFilterDTO.php
app/DTO/RegionalTeam/RegionalTeamDTO.php
app/Services/Rating/RatingCalculationService.php
app/Services/Rating/RatingSeasonService.php
app/Services/Rating/RatingRuleService.php
app/Services/Rating/AthleteEligibilityService.php
app/Services/Rating/AthleteRestrictionService.php
app/Services/Rating/RatingCategory.php
app/Services/RegionalTeam/RegionalTeamService.php
app/Services/RegionalTeam/RegionalTeamDocumentService.php
app/Http/Controllers/RatingController.php
app/Http/Controllers/RatingSeasonController.php
app/Http/Controllers/RatingRuleController.php
app/Http/Controllers/AthleteRestrictionController.php
app/Http/Controllers/RegionalTeamController.php
app/Http/Requests/Rating/RatingFilterRequest.php
app/Http/Requests/Rating/StoreRatingSeasonRequest.php
app/Http/Requests/Rating/SaveRatingRuleRequest.php
app/Http/Requests/Rating/SaveAthleteRestrictionRequest.php
app/Http/Requests/Rating/ClassifyCompetitionRequest.php
app/Http/Requests/Rating/DecisionReasonRequest.php
app/Http/Requests/RegionalTeam/SaveRegionalTeamRequest.php
app/Http/Requests/RegionalTeam/AddRegionalTeamMemberRequest.php
app/Http/Resources/RatingResource.php
app/Http/Resources/RatingDetailResource.php
app/Http/Resources/RatingSeasonResource.php
app/Http/Resources/RatingRuleResource.php
app/Http/Resources/AthleteRestrictionResource.php
app/Http/Resources/RegionalTeamResource.php
app/Http/Resources/RegionalTeamMemberResource.php
```

Созданы поддерживающие файлы:

```text
database/migrations/2026_10_28_111207_extend_rating_and_regional_team_management.php
database/seeders/RatingRuleSeeder.php
database/factories/AthleteFactory.php
database/factories/RatingSeasonFactory.php
database/factories/CompetitionFactory.php
database/factories/CompetitionApplicationFactory.php
database/factories/CompetitionResultFactory.php
database/factories/AthleteRestrictionFactory.php
database/factories/DisciplineFactory.php
database/factories/CompetitionTypeFactory.php
database/factories/AgeGroupFactory.php
database/factories/OrganizationFactory.php
database/factories/SportRankFactory.php
database/factories/AthleteRankFactory.php
database/factories/RankApplicationFactory.php
routes/rating.php
resources/views/regional-teams/team-list.blade.php
resources/views/regional-teams/order-draft.blade.php
tests/RatingTestCase.php
tests/Feature/RatingAndRegionalTeamTest.php
tests/Unit/RatingFormulaTest.php
docs/rating-and-regional-team-api.md
```

Изменены существующие файлы:

- `app/Models/RatingSeason.php` - статус и приведения снимка/версии/даты расчёта.
- `app/Models/RegionalTeam.php` - имя, минимальный разряд, публикация, версия и связь `supersedes()`.
- `app/Models/RegionalTeamMember.php` - приведения снимка и даты исключения.
- `app/Models/SportRank.php` - целочисленное старшинство.
- `app/Models/AuditLog.php` - переиспользуемая запись разрешённых событий на основе существующей таблицы.
- `app/Http/Resources/CompetitionResource.php` - выдача явной рейтинговой категории.
- `bootstrap/app.php` - безопасные JSON-ошибки новых маршрутов.
- `config/filesystems.php` - закрытый диск `team_documents`.
- `database/seeders/DatabaseSeeder.php` - подключение идемпотентных правил.
- `routes/web.php` - подключение новых сессионных маршрутов.

Предыдущие незакоммиченные изменения модуля результатов сохранены. Регистрация, авторизация, соревнования и протоколы предыдущего этапа не переписаны.
