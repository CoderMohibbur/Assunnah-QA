✅ Admin হিসেবে login করতে (সবচেয়ে সহজ)

Login করা ইউজারকে Admin role দিন:

php artisan tinker

$user = \App\Models\User::where('email','admin@example.com')->first();
$user->assignRole('Admin');




Defualt admin user name & password:-
test@example.com
password