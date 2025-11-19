<?php

namespace App\Notifications;

use App\Models\Professional;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProfessionalVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $professional;

    /**
     * Create a new notification instance.
     */
    public function __construct(Professional $professional)
    {
        $this->professional = $professional;
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
            ->subject('Parabéns! Você foi verificado! ✅')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Temos ótimas notícias! Seu perfil profissional "'.$this->professional->business_name.'" foi verificado com sucesso pela nossa equipe.')
            ->line('Agora você pode:')
            ->line('• Receber solicitações de orçamento de clientes')
            ->line('• Enviar propostas para projetos')
            ->line('• Aparecer no marketplace de profissionais')
            ->line('• Receber pedidos e gerar receita')
            ->action('Ver Meu Perfil', route('professionals.show', $this->professional->id))
            ->line('Continue mantendo a qualidade do seu trabalho e boas vendas!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'professional_id' => $this->professional->id,
            'business_name' => $this->professional->business_name,
            'verified_at' => $this->professional->verified_at,
        ];
    }
}
