<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\Chat;

final class SupportController extends Controller
{
    public function index(): string
    {
        $user = Auth::user();
        $chats = $user ? Chat::forUser((int) $user['id']) : [];
        $activeChat = null;
        if ($user) {
            foreach ($chats as $chat) {
                if (in_array($chat['status'], ['open', 'in_progress'], true)) {
                    $activeChat = $chat;
                    break;
                }
            }
        }

        return $this->render('support/index', [
            'title' => t('support.title') . ' · ' . config('app.name'),
            'user' => $user,
            'chats' => $chats,
            'activeChat' => $activeChat,
        ]);
    }

    public function start(): never
    {
        $subject = trim((string) request_input('subject', ''));
        $body = trim((string) request_input('message', ''));
        if ($subject === '' || $body === '') {
            flash('error', t('company.required_fields'));
            redirect('/suporte');
        }

        $chatId = Chat::start((int) Auth::id(), $subject, $body);
        flash('success', t('support.chat_created'));
        redirect('/suporte/chat/' . $chatId);
    }

    public function show(string $id): string
    {
        $chat = Chat::findForUser((int) $id, (int) Auth::id());
        if (!$chat) {
            abort(404);
        }

        return $this->render('support/chat', [
            'title' => $chat['subject'] . ' · ' . config('app.name'),
            'chat' => $chat,
            'messages' => Chat::messages((int) $chat['id']),
            'chats' => Chat::forUser((int) Auth::id()),
            'currentUserId' => (int) Auth::id(),
        ]);
    }

    public function messages(string $id): never
    {
        $chat = Chat::findForUser((int) $id, (int) Auth::id());
        if (!$chat) {
            json_response(['error' => 'not_found'], 404);
        }
        $after = (int) ($_GET['after'] ?? 0);
        $messages = Chat::messages((int) $chat['id'], $after);

        json_response([
            'ok' => true,
            'html' => View::raw('support/_messages', [
                'messages' => $messages,
                'chat' => $chat,
                'currentUserId' => (int) Auth::id(),
            ]),
            'count' => count($messages),
            'last_id' => $messages ? (int) end($messages)['id'] : $after,
            'status' => $chat['status'],
            'status_label' => chat_status_label($chat['status']),
            'moderator' => $chat['moderator_name'] ?? null,
            'moderator_text' => $chat['status'] === 'in_progress'
                ? t('support.chat_in_progress', ['name' => $chat['moderator_name'] ?: t('support.team')])
                : ($chat['status'] === 'open' ? t('chat.waiting') : ''),
        ]);
    }

    public function postMessage(string $id): never
    {
        $chat = Chat::findForUser((int) $id, (int) Auth::id());
        if (!$chat) {
            json_response(['error' => 'not_found'], 404);
        }
        if (!in_array($chat['status'], ['open', 'in_progress'], true)) {
            json_response(['error' => t('support.chat_closed')], 422);
        }
        $body = trim((string) request_input('message', ''));
        if ($body === '') {
            json_response(['error' => t('company.required_fields')], 422);
        }
        if (!Chat::postUserMessage((int) $chat['id'], (int) Auth::id(), mb_substr($body, 0, 2000))) {
            json_response(['error' => t('support.chat_closed')], 422);
        }

        $messages = Chat::messages((int) $chat['id'], (int) ($_POST['after'] ?? 0));
        json_response([
            'ok' => true,
            'html' => View::raw('support/_messages', [
                'messages' => $messages,
                'chat' => $chat,
                'currentUserId' => (int) Auth::id(),
            ]),
            'last_id' => $messages ? (int) end($messages)['id'] : 0,
        ]);
    }
}
