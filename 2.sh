#!/bin/bash

# Подготовка ключевых файлов и загрузка на GitHub
echo "Подготовка файлов для загрузки на GitHub..."

PROJECT_DIR="/home/migrant/web/my.migrant.top/public_html"
TEMP_DIR="/tmp/project_files"
REPO_URL="https://github.com/kspobokin/migrant.git" # Замените на URL вашего репозитория

# Создание временной папки и копирование ключевых файлов
mkdir -p "$TEMP_DIR"
cp "$PROJECT_DIR/app/Http/Controllers/Admin/TemplateController.php" "$TEMP_DIR/" 2>/dev/null || echo "TemplateController.php не найден"
cp "$PROJECT_DIR/resources/views/admin/templates/create.blade.php" "$TEMP_DIR/" 2>/dev/null || echo "create.blade.php не найден"
cp "$PROJECT_DIR/routes/web.php" "$TEMP_DIR/" 2>/dev/null || echo "web.php не найден"
cp "$PROJECT_DIR/app/Models/Template.php" "$TEMP_DIR/" 2>/dev/null || echo "Template.php не найден"
cp -r "$PROJECT_DIR/database/migrations/" "$TEMP_DIR/migrations/" 2>/dev/null || echo "Папка миграций не найдена"
tail -n 100 "$PROJECT_DIR/storage/logs/laravel.log" > "$TEMP_DIR/laravel.log" 2>/dev/null || echo "laravel.log не найден"

# Создание .gitignore
echo -e ".env\nstorage/*\nvendor/*\nnode_modules/*" > "$TEMP_DIR/.gitignore"

# Инициализация Git и загрузка
cd "$TEMP_DIR"
git init
git add .
git commit -m "Key project files for debugging"
git remote add origin "$REPO_URL"
git push -u origin main || {
  echo "Ошибка при загрузке на GitHub. Проверьте URL репозитория и права доступа."
  exit 1
}

# Проверка прав доступа
echo "Проверка прав доступа..."
chmod -R 775 "$PROJECT_DIR/storage" "$PROJECT_DIR/bootstrap/cache"
chown -R www-data:www-data "$PROJECT_DIR/storage" "$PROJECT_DIR/bootstrap/cache"
echo "Права доступа для storage и bootstrap/cache исправлены."

# Очистка кэша Laravel
cd "$PROJECT_DIR"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload -o
echo "Кэш очищен, автозагрузка обновлена."

# Проверка и пересоздание ссылки storage
if [ -L public/storage ]; then
  rm public/storage
  echo "Существующая ссылка public/storage удалена."
fi
php artisan storage:link
echo "Ссылка storage пересоздана."

echo "Файлы загружены на GitHub. Отправьте ссылку на репозиторий и проверьте загрузку шаблонов."