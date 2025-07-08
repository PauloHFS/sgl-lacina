reset:
	php artisan migrate:fresh --seed
	php artisan db:seed --class=DevelopmentSeeder
	php artisan horarios:criar-usuarios
	php artisan optimize