<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $offer;

    /**
     * Create a new notification instance.
     */
    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
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
        $professional = $this->offer->professional;
        $price = number_format($this->offer->price, 2, ',', '.');

        return (new MailMessage)
            ->subject('Nova proposta recebida! 💼')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Você recebeu uma nova proposta para o projeto "'.$this->offer->project->name.'".')
            ->line('**Profissional:** '.$professional->business_name)
            ->line('**Valor:** R$ '.$price)
            ->line('**Prazo de entrega:** '.$this->offer->delivery_days.' dias')
            ->line('**Mensagem:**')
            ->line($this->offer->message)
            ->action('Ver Proposta', route('offers.show', $this->offer->id))
            ->line('Você pode aceitar ou rejeitar esta proposta a qualquer momento.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'offer_id' => $this->offer->id,
            'project_id' => $this->offer->project_id,
            'professional_id' => $this->offer->professional_id,
            'price' => $this->offer->price,
        ];
    }
}
