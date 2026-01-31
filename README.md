✅ Admin হিসেবে login করতে (সবচেয়ে সহজ)

Login করা ইউজারকে Admin role দিন:

/usr/local/lsws/lsphp82/bin/php artisan tinker

$user = \App\Models\User::where('email','admin@example.com')->first();
$user->assignRole('Admin');




Defualt admin user name & password:-
test@example.com
password





✅  Fresh DB (শুধু migrate, seed না)
/usr/local/lsws/lsphp82/bin/php artisan migrate:fresh

✅ শুধু CategorySeeder
/usr/local/lsws/lsphp82/bin/php artisan db:seed --class=Database\\Seeders\\CategorySeeder

✅ শুধু Permission Seeder (QaPermissionSeeder)
/usr/local/lsws/lsphp82/bin/php artisan db:seed --class=Database\\Seeders\\QaPermissionSeeder

✅ শুধু DummyQaSeeder (Questions/Answers ডামি ডাটা)
/usr/local/lsws/lsphp82/bin/php artisan db:seed --class=Database\\Seeders\\DummyQaSeeder

✅ শুধু DatabaseSeeder (সব একসাথে – যেটা আপনি এখন চাইছেন না, তাও দিলাম)
/usr/local/lsws/lsphp82/bin/php artisan db:seed

✅ Fresh + শুধুমাত্র একটা seeder (কম্বো)

Fresh migrate করে তারপর শুধু categories:

/usr/local/lsws/lsphp82/bin/php artisan migrate:fresh --seed --seeder=Database\\Seeders\\CategorySeeder


Fresh migrate করে তারপর শুধু dummy:

/usr/local/lsws/lsphp82/bin/php artisan migrate:fresh --seed --seeder=Database\\Seeders\\DummyQaSeeder

🔥 Quick “একটার পর একটা” রান (copy-paste)

Windows Git Bash/Terminal এ:

/usr/local/lsws/lsphp82/bin/php artisan migrate:fresh
/usr/local/lsws/lsphp82/bin/php artisan db:seed --class=Database\\Seeders\\CategorySeeder
/usr/local/lsws/lsphp82/bin/php artisan db:seed --class=Database\\Seeders\\QaPermissionSeeder
/usr/local/lsws/lsphp82/bin/php artisan db:seed --class=Database\\Seeders\\DummyQaSeeder
