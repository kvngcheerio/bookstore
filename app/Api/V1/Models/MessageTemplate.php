<?php

namespace App\Api\V1\Models;

use Yajra\Auditable\AuditableTrait;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use App\Api\V1\Traits\HandlesEventLogging;

class MessageTemplate extends Model
{
    use AuditableTrait, HandlesEventLogging;
    
    protected $table = 'message_templates';
        
    protected $fillable = ['slug', 'subject', 'description', 'template'];

    public function variables()
    {
        return $this->belongsToMany('App\Api\V1\Models\Variable');
    }

    //all message templates that need to be created should be added here
    public static function defaultMessageTemplates()
    {
        $view_path = Config::get('view.paths');
        $view_path = $view_path[0];
        $activation_email = File::get($view_path . '/activation_email_template.html');
        $password_reset_email = File::get($view_path . '/password_reset_email_template.html');

        return [
            [
                'slug'=>'activation_email',
                'description'=>'email sent to new user',
                'subject' => 'Welcome to Bookstore',
                'template' => $activation_email,
                'variables' => ['first_name']
            ],
            [
                'slug'=>'password_reset_email',
                'description'=>'email sent to user for password reset',
                'subject' => 'Password reset request',
                'template' => $password_reset_email,
                'variables' => []
            ],
        ];
    }
    /**
    * Automatically snake case and UpperCase the slug.
    *
    * @param  string  $value
    * @return void
    */
    
}
