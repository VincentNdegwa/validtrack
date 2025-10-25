<?php

namespace App\Http\Controllers;

use App\Models\SlackIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class SlackController extends Controller
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;
    protected array $scopes = [
        'chat:write',
        'users:read',
        'incoming-webhook'
    ];

    public function __construct()
    {
        $this->clientId = config('services.slack.client_id');
        $this->clientSecret = config('services.slack.client_secret');
        $this->redirectUri = config('services.slack.redirect_uri');
    }

    public function redirect()
    {
        $state = Str::random(40);
        Session::put('slack_oauth_state', $state);

        $url = 'https://slack.com/oauth/v2/authorize?' . http_build_query([
            'client_id' => $this->clientId,
            'scope' => implode(',', $this->scopes),
            'redirect_uri' => $this->redirectUri,
            'state' => $state
        ]);

        return redirect($url);
    }

    /**
     * Handle the callback from Slack
     */
    public function callback(Request $request)
    {
        if ($request->state !== Session::get('slack_oauth_state')) {
            return redirect()->back()
                ->with('error', 'Invalid state parameter. Please try connecting to Slack again.');
        }

        Session::forget('slack_oauth_state');

        if ($request->has('error')) {
            return redirect()->back()
                ->with('error', 'Failed to connect to Slack: ' . $request->error);
        }

        $response = Http::asForm()->post('https://slack.com/api/oauth.v2.access', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $request->code,
            'redirect_uri' => $this->redirectUri
        ]);

        $data = $response->json();

        if (!$data['ok']) {
            return redirect()->back()
                ->with('error', 'Failed to connect to Slack: ' . ($data['error'] ?? 'Unknown error'));
        }

        try {
            $this->storeSlackCredentials($data);
            
            return redirect()->back()
                ->with('success', 'Successfully connected to Slack workspace ' . $data['team']['name']);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to save Slack integration: ' . $e->getMessage());
        }
    }

    /**
     * Store the Slack credentials in your database
     */
    protected function storeSlackCredentials(array $data)
    {
        $company = Auth::user()->company;
        
        $slackData = [
            'app_id' => $data['app_id'],
            'authed_user_id' => $data['authed_user']['id'],
            'scope' => $data['scope'],
            'token_type' => $data['token_type'],
            'access_token' => $data['access_token'],
            'bot_user_id' => $data['bot_user_id'],
            'team_id' => $data['team']['id'],
            'team_name' => $data['team']['name'],
            'is_enterprise_install' => $data['is_enterprise_install'] ?? false,
        ];

        if (isset($data['incoming_webhook'])) {
            $slackData += [
                'webhook_channel' => $data['incoming_webhook']['channel'],
                'webhook_channel_id' => $data['incoming_webhook']['channel_id'],
                'webhook_configuration_url' => $data['incoming_webhook']['configuration_url'],
                'webhook_url' => $data['incoming_webhook']['url'],
            ];
        }

        $company->slackIntegration()->updateOrCreate(
            ['company_id' => $company->id],
            $slackData
        );

        $company->update(['has_slack_integration' => true]);
    }
}
