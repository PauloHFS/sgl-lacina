#!/bin/sh
# filepath: /home/paulo/sgl-lacina/docker/prod/entrypoint.sh

echo "🚀 Iniciando SGL-LaCInA..."

# Aguardar banco de dados estar pronto
echo "⏳ Aguardando PostgreSQL..."
until nc -z db 5432; do
  echo "Aguardando conexão com o banco..."
  sleep 2
done

echo "✅ PostgreSQL conectado!"

# Copiar assets para o volume compartilhado
echo "📁 Copiando assets para volume compartilhado..."
if [ -d "/var/www/html/public" ]; then
  cp -ru /var/www/html/public/* /var/www/html/public-shared/ 2>/dev/null || true
  chown -R www-data:www-data /var/www/html/public-shared
  echo "✅ Assets copiados com sucesso!"
fi

# Executar cache do Laravel (com .env disponível)
echo "🔧 Configurando cache do Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🚀 Iniciando PHP-FPM..."
exec php-fpm