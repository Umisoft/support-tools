# Быстрое использование скриптов

Для быстрого исопльзования скриптов можно создать в браузере специальные вкладки с js кодом. 

Для создания таких вкалдок нужно в поле URL при создании вкладки добавить следующие записи:

Скрипт авторизации
```
javascript: location.href = location.origin + '/loginAsSV.php';
```
Скрипт авторизации с запросом Id пользователя, за которого нужно авторизоваться
```
javascript: id = prompt('Введите id пользователя:', 0); location.href = location.origin + '/loginAsSV.php' + (+id ? '?id=' + id : '');
```
Скрипт очистки БД
```
javascript: location.href = location.origin + "/cleanAdvAjax.php";
```

### По аналогии в закладки можно добавить и другие часто используемые пути или скрпиты:

Adminer

```
javascript: location.href = location.origin + "/adminer.php";
```

Просмотр мультидоменов сайта

```
javascript: location.href = location.origin + "/autoupdate/service/domains/";
```

Просмотр xml данных страницы:

```
javascript: location.href = location.href + ".xml";
```
