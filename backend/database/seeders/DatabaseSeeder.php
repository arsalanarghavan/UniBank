<?php

namespace Database\Seeders;

use App\Models\BotText;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['student', 'admin', 'owner'] as $role) {
            Role::findOrCreate($role);
        }

        $owner = User::query()->updateOrCreate(
            ['email' => env('OWNER_EMAIL', 'owner@ostadbank.local')],
            [
                'name' => env('OWNER_NAME', 'Owner'),
                'password' => Hash::make(env('OWNER_PASSWORD', 'ChangeMeNow!123')),
                'locale' => 'fa',
                'is_active' => true,
                'telegram_id' => env('OWNER_TELEGRAM_ID') ? (int) env('OWNER_TELEGRAM_ID') : null,
            ]
        );
        $owner->syncRoles(['owner']);

        Setting::setValue('force_subscribe', '0');
        Setting::setValue('channel_id', (string) env('TELEGRAM_CHANNEL_ID', ''));
        Setting::setValue('backup_channel_id', (string) env('TELEGRAM_BACKUP_CHANNEL_ID', ''));

        $this->call(UniversityDomainSeeder::class);

        $texts = [
            'welcome' => "به استادبانک خوش آمدید!\nWelcome to OstadBank!",
            'rules' => "قوانین ثبت تجربه:\n۱. احترام به اساتید\n۲. بیان صادقانه و مستند\n۳. عدم توهین\n\nRules:\n1. Respect professors\n2. Be honest\n3. No insults",
        ];
        foreach ($texts as $key => $value) {
            BotText::query()->updateOrCreate(
                ['bot_id' => null, 'key' => $key, 'locale' => 'fa'],
                ['value' => $value]
            );
        }
    }
}
