 - В зависимости от хостнга запустить скрипт в assets/install/хостинг.script.sh из консоли
 - Не забудьте дать права на выполенение скрипта
 - Особенности установки внутри скрипта в комменариях.
 - Это установит окружение node.js на хостинг.
 - Далее нужно создать проект сборки. Для этого запускаем assets/project/create.script.sh в параметрах указываем по порядку:
    - Имя проекта
    - Тип репозитория git, если нужно
        - local - локальный
        - clone - клонируем
        - remote - удалённый
    - Путь для клонирования или удалённого репозитрия
    - Почта git для коммита
    - Имя git для коммита
    - Базовая конфигурация проекта, default по умолчанию
 - Далее необходимо настроить пути для экспорта проекта в modx и компиляции файлов  
Для этого необходимо настроить пути в файле config.inc.php в корне проекта  
Пример файла можно найти в assets/project/project.default/config.inc.php 
 - Если вы хотите, чтобы файлы проекта обрабатывались перед сохранением с использованием 
шаблонизатора Fenom, то для этих файлов создайте копии с нижним подчёркиванием в начале имени  
Такие файлы при сохранении чанка будут генерировать исходные файлы с подстановкой плейсходеров из config.inc.php 
 - После этого запустить assets/project/export.script.php в параметры передать имя проекта  
Это создаст соотвествующие шаблоны и чанки в modx
 - Следующим шагом необходимо настроить пути для сборки в файлах генераторах(с нижним подчёркиванием в начале)
 - Собрать проект можно в ручную. Eсли используется gulp, то сборка начнётся после сохранения файла
