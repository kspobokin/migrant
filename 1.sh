#!/bin/bash

# Исправление ошибки повторного объявления класса
echo "Исправление ошибки повторного объявления класса..."
MIGRATION_FILE="/home/migrant/web/my.migrant.top/public_html/database/migrations/2025_07_02_211552_add_placeholders_to_templates_table.php"

# Проверка существования файла
if [ -f "$MIGRATION_FILE" ]; then
  # Создание резервной копии
  cp "$MIGRATION_FILE" "${MIGRATION_FILE}.bak"
  
  # Переименование класса с новым уникальным суффиксом
  UNIQUE_SUFFIX=$(date +%s | sha256sum | head -c 8)
  sed -i "s/class AddPlaceholdersToTemplatesTable_20250702_211552_403dc757/class AddPlaceholdersToTemplatesTable_20250702_211552_${UNIQUE_SUFFIX}/" "$MIGRATION_FILE"
  
  echo "Класс переименован в $MIGRATION_FILE на AddPlaceholdersToTemplatesTable_20250702_211552_${UNIQUE_SUFFIX}"
else
  echo "Файл миграции не найден: $MIGRATION_FILE"
  exit 1
fi

# Проверка на дублирующиеся имена классов
echo "Проверка на дублирующиеся имена классов..."
FOUND_DUPLICATES=$(grep -r "AddPlaceholdersToTemplatesTable_20250702_211552" /home/migrant/web/my.migrant.top/public_html/database/migrations/ | grep -v "$MIGRATION_FILE")
if [ ! -z "$FOUND_DUPLICATES" ]; then
  echo "Ошибка: Найдены дублирующиеся имена классов в других файлах миграции:"
  echo "$FOUND_DUPLICATES"
  echo "Удалите или переименуйте дублирующиеся файлы миграции вручную."
  exit 1
fi

# Очистка кэша Laravel, обновление автозагрузки и выполнение миграций
cd /home/migrant/web/my.migrant.top/public_html
php artisan cache:clear
composer dump-autoload -o
php artisan migrate:reset
php artisan migrate

echo "Ошибка исправлена. Миграции обновлены. Запустите приложение снова."