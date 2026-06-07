<?php

namespace App\Controllers;

use App\Models\NotificationConversationModel;
use App\Models\NotificationMessageModel;
use CodeIgniter\HTTP\ResponseInterface;

class Notifications extends BaseController
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
        $this->seedWelcomeConversation();

        return view('admin/notifications', [
            'userName' => (string) (session()->get('us_name') ?: 'Administrator'),
            'conversations' => $this->conversationRows(),
            'unreadCount' => $this->unreadCountValue(),
            'theme' => $this->themeSettings(),
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
        $conversation = $this->conversations
            ->where('id', $conversationId)
            ->where('nc_status', 1)
            ->first();
        if (!$conversation) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => false,
                'message' => 'Conversation not found.',
            ]);
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
            'participant' => 'required|min_length[2]|max_length[120]',
            'title' => 'permit_empty|max_length[120]',
            'type' => 'required|in_list[customer,staff,system]',
            'message' => 'permit_empty|max_length[2000]',
        ])) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $participant = trim((string) $this->request->getPost('participant'));
        $title = trim((string) $this->request->getPost('title'));
        $type = trim((string) $this->request->getPost('type'));
        $message = trim((string) $this->request->getPost('message'));
        if ($title === '') {
            $title = $participant;
        }

        $id = (int) $this->conversations->insert([
            'nc_title' => $title,
            'nc_participant' => $participant,
            'nc_type' => $type,
            'nc_avatar' => '',
            'nc_last_message' => $message !== '' ? $message : 'Conversation created',
            'nc_unread_count' => 0,
            'nc_status' => 1,
        ]);

        if ($id <= 0) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Unable to create conversation.',
            ]);
        }

        if ($message !== '') {
            $this->messages->insert([
                'nm_conversation_id' => $id,
                'nm_sender_type' => 'admin',
                'nm_sender_name' => (string) (session()->get('us_name') ?: 'Administrator'),
                'nm_message' => $message,
                'nm_is_read' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Conversation created.',
            'conversation' => $this->mapConversation($this->conversations->find($id) ?? []),
        ]);
    }

    public function send(int $conversationId): ResponseInterface
    {
        if (!$this->validate([
            'message' => 'required|min_length[1]|max_length[2000]',
        ])) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $conversation = $this->conversations
            ->where('id', $conversationId)
            ->where('nc_status', 1)
            ->first();
        if (!$conversation) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => false,
                'message' => 'Conversation not found.',
            ]);
        }

        $message = trim((string) $this->request->getPost('message'));
        $db = db_connect();
        $db->transBegin();

        $messageId = $this->messages->insert([
            'nm_conversation_id' => $conversationId,
            'nm_sender_type' => 'admin',
            'nm_sender_name' => (string) (session()->get('us_name') ?: 'Administrator'),
            'nm_message' => $message,
            'nm_is_read' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $conversationUpdate = [
            'nc_last_message' => mb_substr($message, 0, 255),
            'nc_unread_count' => 0,
        ];
        if ((int) ($conversation['nc_user_id'] ?? 0) > 0) {
            $conversationUpdate['nc_user_unread_count'] = (int) ($conversation['nc_user_unread_count'] ?? 0) + 1;
        }
        $this->conversations->update($conversationId, $conversationUpdate);

        if (!$messageId || $db->transStatus() === false) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Unable to send message.',
            ]);
        }
        $db->transCommit();

        return $this->response->setJSON([
            'status' => true,
            'message' => $this->mapMessage($this->messages->find($messageId) ?? []),
        ]);
    }

    public function markRead(int $conversationId): ResponseInterface
    {
        $conversation = $this->conversations->find($conversationId);
        if (!$conversation) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => false,
                'message' => 'Conversation not found.',
            ]);
        }

        $this->messages
            ->where('nm_conversation_id', $conversationId)
            ->where('nm_sender_type !=', 'admin')
            ->set(['nm_is_read' => 1])
            ->update();
        $this->conversations->update($conversationId, ['nc_unread_count' => 0]);

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

    private function conversationRows(): array
    {
        $rows = $this->conversations
            ->where('nc_status', 1)
            ->orderBy('updated_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll(200);

        return array_map(fn(array $row): array => $this->mapConversation($row), $rows);
    }

    private function mapConversation(array $row): array
    {
        $participant = trim((string) ($row['nc_participant'] ?? 'Notification'));
        $updated = trim((string) ($row['updated_at'] ?? $row['created_at'] ?? ''));

        return [
            'id' => (int) ($row['id'] ?? 0),
            'title' => (string) ($row['nc_title'] ?? $participant),
            'participant' => $participant,
            'type' => (string) ($row['nc_type'] ?? 'customer'),
            'avatar' => (string) ($row['nc_avatar'] ?? ''),
            'initials' => $this->initials($participant),
            'last_message' => (string) ($row['nc_last_message'] ?? ''),
            'unread_count' => (int) ($row['nc_unread_count'] ?? 0),
            'updated_at' => $updated,
            'time_label' => $this->timeLabel($updated),
        ];
    }

    private function mapMessage(array $row): array
    {
        $created = trim((string) ($row['created_at'] ?? ''));

        return [
            'id' => (int) ($row['id'] ?? 0),
            'conversation_id' => (int) ($row['nm_conversation_id'] ?? 0),
            'sender_type' => (string) ($row['nm_sender_type'] ?? 'system'),
            'sender_name' => (string) ($row['nm_sender_name'] ?? 'System'),
            'message' => (string) ($row['nm_message'] ?? ''),
            'is_read' => (int) ($row['nm_is_read'] ?? 0) === 1,
            'created_at' => $created,
            'time_label' => $created !== '' ? date('g:i A', strtotime($created)) : '',
            'date_label' => $created !== '' ? date('d M Y', strtotime($created)) : '',
        ];
    }

    private function unreadCountValue(): int
    {
        try {
            $row = $this->conversations
                ->selectSum('nc_unread_count', 'total')
                ->where('nc_status', 1)
                ->first();

            return (int) ($row['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function seedWelcomeConversation(): void
    {
        try {
            $db = db_connect();
            if (!$db->tableExists('notification_conversations') || $this->conversations->countAllResults() > 0) {
                return;
            }

            $id = (int) $this->conversations->insert([
                'nc_title' => 'Welcome to Notifications',
                'nc_participant' => 'System',
                'nc_type' => 'system',
                'nc_avatar' => '',
                'nc_last_message' => 'Your notification inbox is ready.',
                'nc_unread_count' => 1,
                'nc_status' => 1,
            ]);
            if ($id > 0) {
                $this->messages->insert([
                    'nm_conversation_id' => $id,
                    'nm_sender_type' => 'system',
                    'nm_sender_name' => 'System',
                    'nm_message' => 'Your notification inbox is ready. New order, payment, customer, and support conversations can appear here.',
                    'nm_is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {
            // The page will show its empty state if migrations are pending.
        }
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $letters = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        return $letters !== '' ? $letters : 'N';
    }

    private function timeLabel(string $value): string
    {
        if ($value === '') {
            return '';
        }
        $timestamp = strtotime($value);
        if (date('Y-m-d', $timestamp) === date('Y-m-d')) {
            return date('g:i A', $timestamp);
        }
        if (date('Y-m-d', $timestamp) === date('Y-m-d', strtotime('-1 day'))) {
            return 'Yesterday';
        }
        return date('d M', $timestamp);
    }

    private function themeSettings(): array
    {
        $theme = [
            'website_name' => 'Ebolt',
            'logo_url' => '',
            'theme_color' => '#0f6cad',
        ];

        try {
            $db = db_connect();
            if ($db->tableExists('settings') || $db->tableExists('system_settings')) {
                $general = (new \App\Models\SettingsModel())->getGroupSettings('general');
                $name = trim((string) ($general['website_name'] ?? ''));
                $logo = trim((string) ($general['website_logo'] ?? ''));
                $color = trim((string) ($general['theme_color'] ?? ''));
                if ($name !== '') {
                    $theme['website_name'] = $name;
                }
                if ($logo !== '') {
                    $theme['logo_url'] = base_url('uploads/settings/' . $logo);
                }
                if (preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                    $theme['theme_color'] = strtolower($color);
                }
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        return $theme;
    }
}
