<?php

use Illuminate\Database\Seeder;
use App\Api\V1\Models\Variable;
use App\Api\V1\Models\MessageTemplate;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(MessageTemplate $messageTemplate, Variable $variable)
    {
        //delete old table and add new default settings
        DB::table('settings')->delete();
        DB::table('settings')->insert(
            [
                ['key' => 'default_email', 'value' => 'info@bookstore.com'],
                ['key' => 'reply_to_address', 'value' => 'info@bookstore.com'],
                ['key' => 'default_name', 'value' => 'Book STore Team'],
                ['key' => 'default_phone', 'value' => '08012345678'],
                ['key' => 'auto_delete_stale_event', 'value' => 'false'],
                ['key' => 'auto_delete_stale_event_duration', 'value' => '30']
            ]
        );

        //add variables
        $variables = $variable->defaultVariables();
        foreach ($variables as $v) {
            $variable->firstOrCreate($v);
        }
        $this->command->info('Default variables added.');

        //add message templates
        $messageTemplates = $messageTemplate->defaultMessageTemplates();
        foreach ($messageTemplates as $m) {
            $variables = array_pull($m, 'variables');
            $messageTemplate =  MessageTemplate::firstOrCreate($m);
            if (count($variables)) {
                $v = $variable->whereIn('name', $variables)->get();
                $messageTemplate->variables()->attach($v);
            }
        }
        $this->command->info('Default message templates added.');
    }
}
