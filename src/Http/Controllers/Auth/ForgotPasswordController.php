<?php

namespace Binaryk\LaravelRestify\Http\Controllers\Auth;

use Binaryk\LaravelRestify\Notifications\ForgotPasswordNotification;
use Binaryk\LaravelRestify\Tests\Fixtures\User\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'url' => ['sometimes', 'string'],
        ]);

        /** @var User $user */
        $user = config('restify.auth.user_model')::query()->where($request->only('email'))->firstOrFail();

        $token = Password::createToken($user);

        $url = str_replace(
            ['{token}', '{email}'],
            [$token, $user->email],
            $request->input('url') ?? config('restify.auth.password_reset_url')
        );

        (new AnonymousNotifiable)->route('mail', $user->email)->notify(new ForgotPasswordNotification($url));

        return ok(__('Reset password link sent to your email.'));
    }
}
