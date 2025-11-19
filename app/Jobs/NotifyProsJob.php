<?php

namespace App\Jobs;

use App\Models\Professional;
use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class NotifyProsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Project $project,
        public float $lat,
        public float $lng,
        public int $radiusKm = 50
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Notifying professionals about project {$this->project->id}");

        try {
            // Find professionals within radius
            $professionals = $this->findProfessionalsInRadius();

            if ($professionals->isEmpty()) {
                Log::info("No professionals found within {$this->radiusKm}km radius");
                return;
            }

            Log::info("Found {$professionals->count()} professionals to notify");

            // Notify each professional
            $notifiedCount = 0;
            foreach ($professionals as $professional) {
                try {
                    $this->notifyProfessional($professional);
                    $notifiedCount++;
                } catch (\Exception $e) {
                    Log::error("Error notifying professional {$professional->id}: {$e->getMessage()}");
                }
            }

            Log::info("Notified {$notifiedCount} professionals");

            // Update project metrics
            $this->updateProjectMetrics($notifiedCount);
        } catch (\Exception $e) {
            Log::error("Error in NotifyProsJob for project {$this->project->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Find professionals within radius.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function findProfessionalsInRadius()
    {
        return Professional::approved()
            ->withinRadius($this->lat, $this->lng, $this->radiusKm)
            ->with('user')
            ->get();
    }

    /**
     * Notify a professional about the project.
     *
     * @param Professional $professional
     * @return void
     */
    protected function notifyProfessional(Professional $professional): void
    {
        $user = $professional->user;

        // Prepare notification data
        $data = [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'estimated_price' => $this->project->estimated_price,
            'url' => route('projects.show', $this->project->id),
        ];

        // Send email notification
        // In production, use Mail facade with Mailable class
        $this->sendEmail($user, $data);

        // TODO: Send SMS notification (optional)
        // $this->sendSms($professional, $data);

        // TODO: Send push notification (optional)
        // $this->sendPush($user, $data);

        Log::info("Notified professional {$professional->id} ({$user->email})");
    }

    /**
     * Send email notification.
     *
     * @param User $user
     * @param array $data
     * @return void
     */
    protected function sendEmail(User $user, array $data): void
    {
        // Placeholder for email sending
        // In production, use:
        // Mail::to($user->email)->send(new ProjectNotification($this->project));

        Log::info("Email sent to {$user->email} about project {$this->project->id}");

        // Example implementation:
        /*
        Mail::send('emails.project-notification', $data, function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Novo Projeto Disponível - FitFab');
        });
        */
    }

    /**
     * Send SMS notification (optional).
     *
     * @param Professional $professional
     * @param array $data
     * @return void
     */
    protected function sendSms(Professional $professional, array $data): void
    {
        // Placeholder for SMS sending
        // In production, integrate with Twilio, AWS SNS, or similar
        /*
        $phone = $professional->user->profile['phone'] ?? null;
        if ($phone) {
            // Use Twilio or similar service
            Log::info("SMS sent to {$phone}");
        }
        */
    }

    /**
     * Send push notification (optional).
     *
     * @param User $user
     * @param array $data
     * @return void
     */
    protected function sendPush(User $user, array $data): void
    {
        // Placeholder for push notification
        // In production, use Firebase Cloud Messaging, OneSignal, or similar
        /*
        Notification::send($user, new NewProjectNotification($this->project));
        */
    }

    /**
     * Update project metrics with notification data.
     *
     * @param int $notifiedCount
     * @return void
     */
    protected function updateProjectMetrics(int $notifiedCount): void
    {
        $metrics = $this->project->metrics ?? [];

        $metrics['notifications'] = [
            'professionals_notified' => $notifiedCount,
            'radius_km' => $this->radiusKm,
            'notified_at' => now()->toIso8601String(),
        ];

        $this->project->update(['metrics' => $metrics]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("NotifyProsJob failed for project {$this->project->id}: {$exception->getMessage()}");
    }
}
