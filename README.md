✅ Admin হিসেবে login করতে (সবচেয়ে সহজ)

Login করা ইউজারকে Admin role দিন:

php artisan tinker

$user = \App\Models\User::where('email','admin@example.com')->first();
$user->assignRole('Admin');




Defualt admin user name & password:-
test@example.com
password





✅  Fresh DB (শুধু migrate, seed না)
php artisan migrate:fresh

✅ শুধু CategorySeeder
php artisan db:seed --class=Database\\Seeders\\CategorySeeder

✅ শুধু Permission Seeder (QaPermissionSeeder)
php artisan db:seed --class=Database\\Seeders\\QaPermissionSeeder

✅ শুধু DummyQaSeeder (Questions/Answers ডামি ডাটা)
php artisan db:seed --class=Database\\Seeders\\DummyQaSeeder

✅ শুধু DatabaseSeeder (সব একসাথে – যেটা আপনি এখন চাইছেন না, তাও দিলাম)
php artisan db:seed

✅ Fresh + শুধুমাত্র একটা seeder (কম্বো)

Fresh migrate করে তারপর শুধু categories:

php artisan migrate:fresh --seed --seeder=Database\\Seeders\\CategorySeeder


Fresh migrate করে তারপর শুধু dummy:

php artisan migrate:fresh --seed --seeder=Database\\Seeders\\DummyQaSeeder

🔥 Quick “একটার পর একটা” রান (copy-paste)

Windows Git Bash/Terminal এ:

php artisan migrate:fresh
php artisan db:seed --class=Database\\Seeders\\CategorySeeder
php artisan db:seed --class=Database\\Seeders\\QaPermissionSeeder
php artisan db:seed --class=Database\\Seeders\\DummyQaSeeder
