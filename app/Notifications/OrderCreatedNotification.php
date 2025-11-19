<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;
    public $forProfessional;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, bool $forProfessional = false)
    {
        $this->order = $order;
        $this->forProfessional = $forProfessional;
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
        $amount = number_format($this->order->amount, 2, ',', '.');

        if ($this->forProfessional) {
            return (new MailMessage)
                ->subject('Novo pedido recebido! 🎉')
                ->greeting('Olá, '.$notifiable->name.'!')
                ->line('Parabéns! Sua proposta foi aceita e um novo pedido foi criado.')
                ->line('**Projeto:** '.$this->order->project->name)
                ->line('**Cliente:** '.$this->order->user->name)
                ->line('**Valor:** R$ '.$amount)
                ->action('Ver Pedido', route('orders.show', $this->order->id))
                ->line('Aguarde a confirmação de pagamento do cliente para iniciar o trabalho.');
        }

        return (new MailMessage)
            ->subject('Pedido criado com sucesso! ✅')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Seu pedido foi criado com sucesso!')
            ->line('**Projeto:** '.$this->order->project->name)
            ->line('**Profissional:** '.$this->order->professional->business_name)
            ->line('**Valor:** R$ '.$amount)
            ->action('Realizar Pagamento', route('orders.show', $this->order->id))
            ->line('Complete o pagamento para o profissional iniciar o trabalho.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'project_id' => $this->order->project_id,
            'amount' => $this->order->amount,
            'for_professional' => $this->forProfessional,
        ];
    }
}
