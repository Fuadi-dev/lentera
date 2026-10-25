<?php

namespace Tests\Feature;

use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\Chapter;
use App\Models\Classes;
use App\Models\Level;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Unit;
use App\Models\User;
use App\Services\AiChatService;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class AiChatTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Student $student;

    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'child', 'email_verified_at' => now()]);
        $this->student = Student::factory()->create(['user_id' => $this->user->id]);

        $subject = Subject::factory()->create();
        $class = Classes::factory()->create(['subject_id' => $subject->id]);
        $level = Level::factory()->create(['class_id' => $class->id]);
        $chapter = Chapter::factory()->create(['level_id' => $level->id]);

        $this->unit = Unit::factory()->create([
            'chapter_id' => $chapter->id,
            'type' => 'text',
            'title' => 'Pengenalan Fotosintesis',
            'content' => 'Tumbuhan memerlukan cahaya matahari, air, dan karbon dioksida untuk membuat makanannya sendiri.',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_ai_chat(): void
    {
        $response = $this->postJson('/ai/chat/sessions', ['unit_id' => $this->unit->id]);
        $response->assertStatus(401);
    }

    public function test_user_without_student_profile_cannot_access_ai_chat(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent', 'email_verified_at' => now()]);

        $response = $this->actingAs($parentUser)->postJson('/ai/chat/sessions', [
            'unit_id' => $this->unit->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_authenticated_student_can_create_or_get_session(): void
    {
        $response = $this->actingAs($this->user)->postJson('/ai/chat/sessions', [
            'unit_id' => $this->unit->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'session_id',
            'messages',
            'remaining',
        ]);
        $response->assertJson([
            'remaining' => 10,
            'messages' => [],
        ]);

        $this->assertDatabaseHas('ai_chat_sessions', [
            'student_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'message_count' => 0,
        ]);
    }

    public function test_student_can_send_message_and_receive_ai_response(): void
    {
        $session = AiChatSession::factory()->create([
            'student_id' => $this->student->id,
            'unit_id' => $this->unit->id,
        ]);

        $this->mock(GeminiService::class, function (MockInterface $mock) {
            $mock->shouldReceive('generateResponse')
                ->once()
                ->andReturn('Fotosintesis adalah cara tumbuhan membuat makanan dengan cahaya matahari.');
        });

        $response = $this->actingAs($this->user)->postJson("/ai/chat/sessions/{$session->id}/messages", [
            'content' => 'Bisa jelaskan lebih mudah?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Fotosintesis adalah cara tumbuhan membuat makanan dengan cahaya matahari.',
            'source' => 'ai',
            'remaining' => [
                'session' => 9,
                'today' => 29,
            ],
        ]);

        $this->assertDatabaseHas('ai_chat_messages', [
            'session_id' => $session->id,
            'role' => 'user',
            'content' => 'Bisa jelaskan lebih mudah?',
            'source' => 'user',
        ]);

        $this->assertDatabaseHas('ai_chat_messages', [
            'session_id' => $session->id,
            'role' => 'model',
            'source' => 'ai',
        ]);
    }

    public function test_pii_filter_blocks_email(): void
    {
        $session = AiChatSession::factory()->create([
            'student_id' => $this->student->id,
            'unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->user)->postJson("/ai/chat/sessions/{$session->id}/messages", [
            'content' => 'Kirim materi ke emailku budi@sekolah.com ya',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Jangan bagikan data pribadi ya. Yuk balik ke materi!',
            'source' => 'blocked_pii',
        ]);

        $this->assertDatabaseHas('ai_chat_messages', [
            'session_id' => $session->id,
            'source' => 'blocked_pii',
            'is_flagged' => true,
        ]);
    }

    public function test_pii_filter_blocks_phone_number(): void
    {
        $session = AiChatSession::factory()->create([
            'student_id' => $this->student->id,
            'unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->user)->postJson("/ai/chat/sessions/{$session->id}/messages", [
            'content' => 'Nomorku 081234567890 hubungi aku ya',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Jangan bagikan data pribadi ya. Yuk balik ke materi!',
            'source' => 'blocked_pii',
        ]);
    }

    public function test_pii_filter_blocks_address(): void
    {
        $session = AiChatSession::factory()->create([
            'student_id' => $this->student->id,
            'unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->user)->postJson("/ai/chat/sessions/{$session->id}/messages", [
            'content' => 'Rumah saya di jl Melati nomor 12',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Jangan bagikan data pribadi ya. Yuk balik ke materi!',
            'source' => 'blocked_pii',
        ]);
    }

    public function test_pii_filter_blocks_school(): void
    {
        $session = AiChatSession::factory()->create([
            'student_id' => $this->student->id,
            'unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->user)->postJson("/ai/chat/sessions/{$session->id}/messages", [
            'content' => 'Nama sekolah saya SDN 01 Pagi',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Jangan bagikan data pribadi ya. Yuk balik ke materi!',
            'source' => 'blocked_pii',
        ]);
    }

    public function test_input_greater_than_200_chars_is_rejected(): void
    {
        $session = AiChatSession::factory()->create([
            'student_id' => $this->student->id,
            'unit_id' => $this->unit->id,
        ]);

        $longContent = str_repeat('a', 201);

        $response = $this->actingAs($this->user)->postJson("/ai/chat/sessions/{$session->id}/messages", [
            'content' => $longContent,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['content']);
    }

    public function test_gemini_failure_returns_fallback_message(): void
    {
        $session = AiChatSession::factory()->create([
            'student_id' => $this->student->id,
            'unit_id' => $this->unit->id,
        ]);

        $this->mock(GeminiService::class, function (MockInterface $mock) {
            $mock->shouldReceive('generateResponse')
                ->once()
                ->andReturn(null);
        });

        $response = $this->actingAs($this->user)->postJson("/ai/chat/sessions/{$session->id}/messages", [
            'content' => 'Beri aku contoh tumbuhan pemakan serangga',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Si Lentera lagi sibuk. Coba baca penjelasan di atas dulu ya.',
            'source' => 'fallback',
        ]);

        $this->assertDatabaseHas('ai_chat_messages', [
            'session_id' => $session->id,
            'role' => 'model',
            'source' => 'fallback',
        ]);
    }

    public function test_session_message_limit_triggers_exhaustion_message(): void
    {
        $session = AiChatSession::factory()->create([
            'student_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'message_count' => 10,
        ]);

        $response = $this->actingAs($this->user)->postJson("/ai/chat/sessions/{$session->id}/messages", [
            'content' => 'Halo apa kabar?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Hari ini Si Lentera istirahat dulu ya, lanjut belajar besok!',
            'source' => 'fallback',
            'remaining' => [
                'session' => 0,
            ],
        ]);
    }

    public function test_daily_message_limit_triggers_exhaustion_message(): void
    {
        $session = AiChatSession::factory()->create([
            'student_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'message_count' => 0,
        ]);

        // Pre-create 30 user messages today
        for ($i = 0; $i < 30; $i++) {
            AiChatMessage::factory()->create([
                'session_id' => $session->id,
                'role' => 'user',
                'created_at' => now(),
            ]);
        }

        $response = $this->actingAs($this->user)->postJson("/ai/chat/sessions/{$session->id}/messages", [
            'content' => 'Halo apa kabar?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Hari ini Si Lentera istirahat dulu ya, lanjut belajar besok!',
            'source' => 'fallback',
            'remaining' => [
                'today' => 0,
            ],
        ]);
    }

    public function test_student_cannot_send_message_to_another_students_session(): void
    {
        $otherUser = User::factory()->create(['role' => 'child', 'email_verified_at' => now()]);
        $otherStudent = Student::factory()->create(['user_id' => $otherUser->id]);

        $otherSession = AiChatSession::factory()->create([
            'student_id' => $otherStudent->id,
            'unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->user)->postJson("/ai/chat/sessions/{$otherSession->id}/messages", [
            'content' => 'Coba buka sesi teman',
        ]);

        $response->assertStatus(403);
    }

    public function test_quiz_context_does_not_contain_questions_options_or_answers(): void
    {
        $quizUnit = Unit::factory()->create([
            'chapter_id' => $this->unit->chapter_id,
            'type' => 'quiz',
            'title' => 'Kuis Harian Fotosintesis',
        ]);

        $question = Question::factory()->create([
            'unit_id' => $quizUnit->id,
            'question' => 'SECRET_QUESTION_PROMPT_ABC123',
            'explanation' => 'SECRET_EXPLANATION_XYZ789',
        ]);

        QuestionOption::factory()->create([
            'question_id' => $question->id,
            'text' => 'SECRET_CORRECT_OPTION_KLM456',
        ]);

        /** @var AiChatService $service */
        $service = app(AiChatService::class);
        $prompt = $service->buildSystemInstruction($quizUnit);

        $this->assertStringNotContainsString('SECRET_QUESTION_PROMPT_ABC123', $prompt);
        $this->assertStringNotContainsString('SECRET_EXPLANATION_XYZ789', $prompt);
        $this->assertStringNotContainsString('SECRET_CORRECT_OPTION_KLM456', $prompt);
        $this->assertStringContainsString('TUTOR BUKAN SOLVER', $prompt);
        $this->assertStringContainsString('Kuis Harian Fotosintesis', $prompt);
    }
}
