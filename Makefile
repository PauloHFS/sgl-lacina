reset:
	php artisan migrate:fresh --seed
	php artisan db:seed --class=DevelopmentSeeder