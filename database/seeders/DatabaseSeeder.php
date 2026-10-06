<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Subject;
use App\Models\Classes;
use App\Models\Level;
use App\Models\Chapter;
use App\Models\Unit;
use App\Models\FinalTest;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Parents;
use App\Models\Student;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Orang Tua & Siswa
        $parent = Parents::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);
        
        $student = Student::factory()->create([
            'parent_id' => $parent->id,
            'name' => 'Andi',
        ]);

        // 2. Buat Pelajaran Matematika
        $math = Subject::factory()->create([
            'title' => 'Matematika',
            'description' => 'Pelajaran berhitung yang menyenangkan.',
            'thumbnail' => '/images/subjects/math.png',
        ]);

        // 3. Buat Kelas 1 untuk Matematika
        $class1 = Classes::factory()->create([
            'subject_id' => $math->id,
            'number' => 1,
            'title' => 'Kelas 1',
            'description' => 'Materi dasar untuk siswa SD kelas 1',
        ]);

        // 4. Buat Level 1 di Kelas 1
        $level1 = Level::factory()->create([
            'class_id' => $class1->id,
            'number' => 1,
            'title' => 'Tingkat 1: Pengenalan Angka',
            'description' => 'Mengenal angka 1 sampai 10 dan dasar penjumlahan',
        ]);

        // 5. Buat Chapter (Bab) di Level 1
        $chapter1 = Chapter::factory()->create([
            'level_id' => $level1->id,
            'number' => 1,
            'title' => 'Bab 1: Menghitung Angka 1-10',
            'description' => 'Ayo belajar menghitung dengan benda-benda di sekitar kita',
        ]);

        // 6. Buat Units untuk Bab 1
        // Unit 1: Video
        Unit::factory()->create([
            'chapter_id' => $chapter1->id,
            'order' => 1,
            'type' => 'video',
            'title' => 'Video: Mari Berhitung 1-10',
            'media_url' => 'https://www.youtube.com/watch?v=example',
            'duration' => '02:30',
        ]);

        // Unit 2: Text/Materi Bacaan
        Unit::factory()->create([
            'chapter_id' => $chapter1->id,
            'order' => 2,
            'type' => 'text',
            'title' => 'Membaca Angka',
            'content' => 'Angka 1 bentuknya seperti tiang. Angka 2 bentuknya seperti bebek. Angka 3 seperti telinga...',
        ]);

        // Unit 3: Quiz Kecil
        $unitQuiz = Unit::factory()->create([
            'chapter_id' => $chapter1->id,
            'order' => 3,
            'type' => 'quiz',
            'title' => 'Kuis Latihan Berhitung',
            'passing_score' => 100,
        ]);

        // 7. Buat Pertanyaan untuk Quiz
        $question1 = Question::factory()->create([
            'unit_id' => $unitQuiz->id,
            'type' => 'multiple_choice',
            'question' => 'Berapa jumlah mata kita?',
            'points' => 10,
        ]);

        $optA = QuestionOption::factory()->create(['question_id' => $question1->id, 'text' => 'Satu']);
        $optB = QuestionOption::factory()->create(['question_id' => $question1->id, 'text' => 'Dua']);
        $optC = QuestionOption::factory()->create(['question_id' => $question1->id, 'text' => 'Tiga']);
        
        // Update jawaban yang benar
        $question1->update(['answer' => [$optB->id]]);

        // 8. Buat Tes Akhir Bab
        FinalTest::factory()->create([
            'chapter_id' => $chapter1->id,
            'title' => 'Ujian Akhir Bab 1',
            'minimum_score' => 70,
            'question_count' => 1,
            'unlocks_next_level' => true,
        ]);

        // Generate sedikit data Bahasa Indonesia
        $bahasa = Subject::factory()->create([
            'title' => 'Bahasa Indonesia',
            'description' => 'Belajar membaca dan mengeja huruf.',
        ]);
        
        $class1Bahasa = Classes::factory()->create([
            'subject_id' => $bahasa->id,
            'number' => 1,
            'title' => 'Kelas 1',
        ]);
    }
}
