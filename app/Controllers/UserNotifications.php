<?php

namespace App\Controllers;

use App\Models\NotificationConversationModel;
use App\Models\NotificationMessageModel;
use App\Models\SettingsModel;
use CodeIgniter\HTTP\ResponseInterface;

class UserNotifications extends BaseController
{
    private NotificationConversationModel $conversations;
    private NotificationMessageModel $messages;

    public function __construct()
    {
        $this->conversations = new NotificationConversationModel();
        $this->messages = new NotificationMessageModel();
    }

    public function index()
    {
        return view('admin/notifications', [
            'userName' => $this->userName(),
            'conversations' => $this->conversationRows(),
            'unreadCount' => $this->unreadCountValue(),
            'theme' => $this->themeSettings(),
            'endpointPrefix' => 'user/notifications',
            'backUrl' => base_url('user/dashboard'),
            'currentSenderType' => 'customer',
            'pageTitle' => 'My Notifications',
        ]);
    }

    public function conversations(): ResponseInterface
    {
        return $this->response->setJSON([
            'status' => true,
            'conversations' => $this->conversationRows(),
            'unread_count' => $this->unreadCountValue(),
        ]);
    }

    public function messages(int $conversationId): ResponseInterface
    {
        $conversation = $this->ownedConversation($conversationId);
        if (!$conversation) {
            return $this->notFound();
        }

        $afterId = max(0, (int) ($this->request->getGet('after_id') ?? 0));
        $builder = $this->messages
            ->where('nm_conversation_id', $conversationId)
            ->orderBy('id', 'ASC');
        if ($afterId > 0) {
            $builder->where('id >', $afterId);
        }

        return $this->response->setJSON([
            'status' => true,
            'conversation' => $this->mapConversation($conversation),
            'messages' => array_map(
                fn(array $row): array => $this->mapMessage($row),
                $builder->findAll(200)
            ),
        ]);
    }

    public function create(): ResponseInterface
    {
        if (!$this->validate([
            'title' => 'permit_empty|max_length[120]',
            'message' => 'required|min_length[1]|max_length[2000]',
        ])) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => implode(' ', $this->validator->getErrors()),
            ]);
        }

        $title = trim((string) $this->request->getPost('title')) ?: 'Support';
        $message = trim((string) $this->request->getPost('message'));
        $id = (int) $this->conversations->insert([
            'nc_user_id' => $this->userId(),
            'nc_title' => $title,
            'nc_participant' => $this->userName(),
            'nc_type' => 'customer',
            'nc_avatar' => '',
            'nc_last_message' => mb_substr($message, 0, 255),
            'nc_unread_count' => 1,
            'nc_user_unread_count' => 0,
            'nc_status' => 1,
        ]);

        if ($id <= 0) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Unable to create conversation.',
            ]);
        }

        $this->messages->insert([
            'nm_conversation_id' => $id,
            'nm_sender_type' => 'customer',
            'nm_sender_name' => $this->userName(),
            'nm_message' => $message,
            'nm_is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status' => true,
            'conversation' => $this->mapConversation($this->conversations->find($id) ?? []),
        ]);
    }

    public function send(int $conversationId): ResponseInterface
    {
        if (!$this->validate(['message' => 'required|min_length[1]|max_length[2000]'])) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => implode(' ', $this->validator->getErrors()),
            ]);
        }

        $conversation = $this->ownedConversation($conversationId);
        if (!$conversation) {
            return $this->notFound();
        }

        $message = trim((string) $this->request->getPost('message'));
        $messageId = $this->messages->insert([
            'nm_conversation_id' => $conversationId,
            'nm_sender_type' => 'customer',
            'nm_sender_name' => $this->userName(),
            'nm_message' => $message,
            'nm_is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->conversations->update($conversationId, [
            'nc_last_message' => mb_substr($message, 0, 255),
            'nc_unread_count' => (int) ($conversation['nc_unread_count'] ?? 0) + 1,
            'nc_user_unread_count' => 0,
        ]);

        if (!$messageId) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Unable to send message.',
            ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => $this->mapMessage($this->messages->find($messageId) ?? []),
        ]);
    }

    public function markRead(int $conversationId): ResponseInterface
    {
        if (!$this->ownedConversation($conversationId)) {
            return $this->notFound();
        }

        $this->messages
            ->where('nm_conversation_id', $conversationId)
            ->where('nm_sender_type !=', 'customer')
            ->set(['nm_is_read' => 1])
            ->update();
        $this->conversations->update($conversationId, ['nc_user_unread_count' => 0]);

        return $this->response->setJSON([
            'status' => true,
            'unread_count' => $this->unreadCountValue(),
        ]);
    }

    public function unreadCount(): ResponseInterface
    {
        return $this->response->setJSON([
            'status' => true,
            'unread_count' => $this->unreadCountValue(),
        ]);
    }

    private function ownedConversation(int $id): ?array
    {
        return $this->conversations
            ->where('id', $id)
            ->where('nc_user_id', $this->userId())
            ->where('nc_status', 1)
            ->first();
    }

    private function conversationRows(): array
    {
        $rows = $this->conversations
            ->where('nc_user_id', $this->userId())
            ->where('nc_status', 1)
            ->orderBy('updated_at', 'DESC')
            ->findAll(200);

        return array_map(fn(array $row): array => $this->mapConversation($row), $rows);
    }

    private function unreadCountValue(): int
    {
        $row = $this->conversations
            ->selectSum('nc_user_unread_count', 'total')
            ->where('nc_user_id', $this->userId())
            ->where('nc_status', 1)
            ->first();

        return (int) ($row['total'] ?? 0);
    }

    private function mapConversation(array $row): array
    {
        $updated = trim((string) ($row['updated_at'] ?? $row['created_at'] ?? ''));
        return [
            'id' => (int) ($row['id'] ?? 0),
            'title' => (string) ($row['nc_title'] ?? 'Support'),
            'participant' => 'Support Team',
            'type' => 'support',
            'initials' => 'ST',
            'last_message' => (string) ($row['nc_last_message'] ?? ''),
            'unread_count' => (int) ($row['nc_user_unread_count'] ?? 0),
            'time_label' => $this->timeLabel($updated),
        ];
    }

    private function mapMessage(array $row): array
    {
        $created = trim((string) ($row['created_at'] ?? ''));
        return [
            'id' => (int) ($row['id'] ?? 0),
            'sender_type' => (string) ($row['nm_sender_type'] ?? 'system'),
            'sender_name' => (string) ($row['nm_sender_name'] ?? 'Support'),
            'message' => (string) ($row['nm_message'] ?? ''),
            'time_label' => $created !== '' ? date('g:i A', strtotime($created)) : '',
        ];
    }

    private function notFound(): ResponseInterface
    {
        return $this->response->setStatusCode(404)->setJSON([
            'status' => false,
            'message' => 'Conversation not found.',
        ]);
    }

    private function userId(): int
    {
        return (int) session()->get('user_id');
    }

    private function userName(): string
    {
        return (string) (session()->get('us_name') ?: 'Customer');
    }

    private function timeLabel(string $value): string
    {
        if ($value === '') {
            return '';
        }
        $timestamp = strtotime($value);
        return date('Y-m-d', $timestamp) === date('Y-m-d') ? date('g:i A', $timestamp) : date('d M', $timestamp);
    }

    private function themeSettings(): array
    {
        $theme = ['website_name' => 'Ebolt', 'logo_url' => '', 'theme_color' => '#0f6cad'];
        try {
            $general = (new SettingsModel())->getGroupSettings('general');
            $theme['website_name'] = trim((string) ($general['website_name'] ?? '')) ?: $theme['website_name'];
            $logo = trim((string) ($general['website_logo'] ?? ''));
            $color = trim((string) ($general['theme_color'] ?? ''));
            if ($logo !== '') {
                $theme['logo_url'] = base_url('uploads/settings/' . $logo);
            }
            if (preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                $theme['theme_color'] = strtolower($color);
            }
        } catch (\Throwable $e) {
            // Keep defaults.
        }
        return $theme;
    }
}
