until nc -z 127.0.0.1 3306; do
  echo "Waiting for MySQL on port 3306..."
  sleep 2
done

php artisan queue:work --sleep=3 --tries=3 --timeout=90
