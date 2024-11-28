<?php

namespace App\Http\Controllers;

use App\Http\Requests\DirectMessageSendRequest;
use App\Http\Resources\DirectMessageResource;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DirectMessageController extends Controller
{
    public function index($userId)
    {
        $user = Auth::user();
        $receiver = User::findOrFail($userId);

        if (!$this->canSendMessage($user, $receiver)) {
            abort(403, 'You are not allowed to list direct messages with this user.');
        }

        $messages = $user->directMessages()
            ->where('receiver_id', $receiver->id)
            ->orWhere('sender_id', $receiver->id)
            ->get();

        return DirectMessageResource::collection($messages);
    }

    public function store(DirectMessageSendRequest $request, $userId)
    {
        $user = Auth::user();
        $receiver = User::findOrFail($userId);

        if (!$this->canSendMessage($user, $receiver)) {
            abort(403, 'You are not allowed to send a direct message to this user.');
        }

        $message = $user->directMessages()->create([
            'content' => $request->get('content'),
            'receiver_id' => $receiver->id,
        ]);

        $client = new Client();
        $chatSecret = config('agilis.chat.secret');

        $response = $client->post('http://localhost:8003/chat/messages', [
            'json' => [
                'message' => [
                    'from' => $user->id,
                    'to' => $receiver->id,
                    'content' => $request->get('content'),
                    'timestamp' => $message->created_at->timestamp,
                ]
            ],
            'headers' => [
                'Authorization' => "Api $chatSecret"
            ]
        ]);

        return (new DirectMessageResource($message))
            ->response()
            ->setStatusCode(201)
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    private function canSendMessage(User $sender, User $receiver): bool
    {
        $senderOrganizations = $sender->organizations->pluck('id');
        $receiverOrganizations = $receiver->organizations->pluck('id');

        return $senderOrganizations->intersect($receiverOrganizations)
                ->isNotEmpty() && $sender->id !== $receiver->id;
    }
}
