![PHP Composer](https://github.com/jeyroik/extas-fields-defaults/workflows/PHP%20Composer/badge.svg?branch=master&event=push)
![codecov.io](https://codecov.io/gh/jeyroik/extas-fields-defaults/coverage.svg?branch=master)
<a href="https://github.com/phpstan/phpstan"><img src="https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat" alt="PHPStan Enabled"></a> 
<a href="https://codeclimate.com/github/jeyroik/extas-fields-defaults/maintainability"><img src="https://api.codeclimate.com/v1/badges/f29272ecb291adc742fb/maintainability" /></a>

# Описание

Пакет позволяет определить значения по умолчанию для полей `IItem`-совместимого класса.

# Применение

## Определение обязательных полей

Для определения значений по умолчанию используется пакет `extas-fields`. 

`extas.json`

```json
{
  "fields": [
    {
      "name": "my_field",
      "title": "My field",
      "description": "Example of usage",
      "type": "string",
      "value": "my value",
      "parameters": {
        "subject": {
          "name": "subject",
          "value": "my.subject"
        }
      }
    }
  ]
}
```

Значение из поля `value` как раз и есть значенем по умолчанию для поля.

Далее необходимо подключить плагин текущего пакета для нужных вам сущностей.

`Примечание: если у вас используются другие плагины, например, проверяющие значения полей и т.п., то рекомендуется для текущего плагина указывать более высокий приоритет, чтобы он выполнился раньше остальных.`

`extas.json`

```json
{
  "plugins": [
    {
      "class": "extas\\components\\plugins\\PluginFieldsDefaults",
      "stage": ["my.subject.init"],
      "priority": 1
    }
  ]
}
```

Установите поля и плагин

`# vendor/bin/extas i`

## Использование

```php
$my = new class extends Item {
    protected function getSubjectForExtension(): string
    {
        return 'my.subject';
    }
};

echo $my['my_field']; // "my value"
```
