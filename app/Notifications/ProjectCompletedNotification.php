<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $project;

    /**
     * Create a new notification instance.
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Seu projeto foi concluído! 🎉')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Temos ótimas notícias! Seu projeto "'.$this->project->name.'" foi processado com sucesso.')
            ->line('Agora você pode fazer o download dos arquivos gerados:')
            ->line('• Especificação técnica (PDF)')
            ->line('• Plano de corte (PDF)')
            ->line('• Lista de materiais (PDF)')
            ->line('• Arquivo DXF para CNC')
            ->action('Ver Projeto', route('projects.show', $this->project->id))
            ->line('Se você precisar de ajuda com a montagem ou quiser contratar um profissional, visite nosso marketplace!')
            ->salutation('Obrigado por usar o FitFab!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'status' => $this->project->status,
        ];
    }
}
