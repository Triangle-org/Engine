# Triangle Engine

Triangle Engine — слой HTTP и запуска приложений, используемый проектами Triangle Framework. Он предоставляет объекты запроса и ответа, конфигурацию и контекст выполнения, средства запуска приложения и адаптеры для Localzet Server.

Эта версия в разработке требует **PHP 8.1 или новее** и **Localzet Server, совместимый с `^7.1`**. Engine не является самостоятельным веб-сервером: сетевой listener, event loop, протоколы и жизненный цикл процессов обеспечивает Localzet Server.

## Требования

- PHP `^8.1`, расширения `ext-intl` и `ext-json`.
- `localzet/server:^7.1`.
- Monolog `^3.0` и PSR Container `^1.0` или `^2.0`.
- Symfony Cache и Translation `~6.4.0`.

Последняя опубликованная версия пакета — `v8.1.11`; она требует `localzet/server:^4`. В этой версии исходников выполняется переход на Server 7.1. Эти версии нельзя считать взаимозаменяемыми:

| Версия Engine | Требование к Server | Статус |
| --- | --- | --- |
| `v8.1.11` | `^4` | Опубликована в Packagist |
| Исходники в разработке | `^7.1` | Переход на Server 7; совместимый стабильный релиз ещё не выпущен |

Не устанавливайте опубликованную `v8.1.11` в приложение на Server 7.1. Перед обновлением проверьте фактически выбранные версии:

```sh
composer show triangle/engine
composer show localzet/server
```

Для проверки текущих исходников настройте репозиторий и подключите ветку разработки только в окружении разработки:

```sh
composer config repositories.triangle-engine vcs https://github.com/Triangle-org/Engine
composer require triangle/engine:dev-main@dev
```

После публикации совместимого основного релиза используйте его стабильное ограничение версии в production.

## Основные компоненты

- `Triangle\Engine\Config` загружает и читает конфигурацию приложения.
- `Triangle\Engine\Context` хранит значения для активного Fiber или coroutine; вызывайте `Context::destroy()` при завершении запроса или задачи.
- `Triangle\Engine\Request` и `Triangle\Engine\Response` адаптируют HTTP-сообщения Localzet Server для приложений Triangle.
- `support\App` собирает настроенные listeners и процессы приложения Triangle.
- `Triangle\Exception` содержит типы исключений и контракты их отображения.

Используйте bootstrap, который предоставляет ваш проект Triangle. Engine сам не создаёт listener и не выбирает схему развёртывания.

## Совместимость с Server 7.1

Server 7 определяет глобальные функции `response()`, `json()` и `redirect()`. Их имена пересекаются с прежними помощниками Engine, но значения и поведение отличаются. Для прежней семантики Engine доступны отдельные имена:

| Функция | Поведение Engine | При загрузке Server 7.1 |
| --- | --- | --- |
| `triangle_response()` | Обёртка ответа Engine и выбор JSON/HTML | Используйте для сохранения поведения Engine |
| `triangle_json()` | Кодирует значение PHP в строку JSON | Используйте, когда нужна именно строка |
| `triangle_redirect()` | Возвращает `Triangle\Engine\Response` и удаляет CR/LF из адреса | Используйте, когда нужен тип ответа Engine |
| `responseJson()` / `responseBlob()` | Создают ответ Engine | Можно продолжать использовать |

Если Server загружен первым, непомеченные префиксом функции относятся к Server. Исходники Engine защищают необязательные старые объявления от повторного определения при автозагрузке Composer. Если приложение рассчитывало на особенности `response()`, `json()` или `redirect()` из Engine, замените такие вызовы на соответствующие функции с префиксом `triangle_`.

### Переход с функций Engine 8

| Вызов в Engine 8 | Замена в Engine 9 | Тип результата |
| --- | --- | --- |
| `json($data)` | `triangle_json($data)` | Строка JSON |
| `response($data, $status)` | `triangle_response($data, $status)` | Обёртка ответа Engine и выбор JSON/HTML |
| `redirect($url, $status)` | `triangle_redirect($url, $status)` | `Triangle\Engine\Response` |

Если нужен JSON HTTP-ответ без обёртки Engine, используйте `responseJson($data, $status)`. Вызовы без префикса `triangle_` теперь следуют контракту Server 7. При обновлении выберите функцию по требуемому результату: строка JSON, ответ Engine или ответ Server.

Текущий `Context` выбирает ключ хранения по активному PHP Fiber независимо от выбранного event loop. Это важно, когда асинхронные обработчики Server 7 чередуются в стандартных циклах событий.

## Тесты

Установите зависимости для разработки и запустите объявленный набор PHPUnit:

```sh
composer install
composer test
```

Набор проверяет конфигурацию, изоляцию контекста Fiber, взаимодействие с помощниками Server 7 и реальный HTTP-запрос через loopback-сокет с классами запроса и ответа Engine. Он не заменяет тесты приложения для маршрутизации, middleware, хранилищ и поведения при развёртывании.

## Документация и поддержка

- [Исходный код проекта](https://github.com/Triangle-org/Engine)
- [Сообщить об уязвимости](SECURITY.md)
- [Правила участия](CONTRIBUTING.md)

Copyright © 2016-2026 [Localzet Group](https://www.localzet.com/). All rights reserved.
