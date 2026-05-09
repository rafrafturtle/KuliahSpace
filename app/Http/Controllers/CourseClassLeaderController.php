<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseClassLeader;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Silber\Bouncer\BouncerFacade as Bouncer;

class CourseClassLeaderController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $classLeaders = CourseClassLeader::with(['student', 'lecturer', 'course', 'semester', 'academicYear'])
            ->when($search, function ($query) use ($search): void {
                $query->whereHas('student', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('lecturer', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('course', fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            })
            ->latest('assigned_at')
            ->paginate(10)
            ->withQueryString();

        return view('class-leaders.index', compact('classLeaders', 'search'));
    }

    public function create(): View
    {
        return view('class-leaders.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->currentUserIsAdmin()) {
            $request->merge(['lecturer_id' => $request->user()->id]);
        }

        $validated = $request->validate([
            'student_id' => ['required', 'uuid', 'exists:users,id'],
            'lecturer_id' => ['required', 'uuid', 'exists:users,id'],
            'course_id' => [
                'required',
                'uuid',
                'exists:courses,id',
                Rule::unique('course_class_leaders', 'course_id')
                    ->where('lecturer_id', $request->input('lecturer_id'))
                    ->where('semester_id', $request->input('semester_id'))
                    ->where('academic_year_id', $request->input('academic_year_id')),
            ],
            'semester_id' => ['required', 'uuid', 'exists:semesters,id'],
            'academic_year_id' => ['required', 'uuid', 'exists:academic_years,id'],
        ]);

        $student = User::with('roles')->findOrFail($validated['student_id']);
        $lecturer = User::with('roles')->findOrFail($validated['lecturer_id']);

        if (! $student->roles->contains('name', 'mahasiswa')) {
            throw ValidationException::withMessages(['student_id' => 'Mahasiswa yang dipilih harus memiliki role mahasiswa.']);
        }

        if (! $lecturer->roles->contains('name', 'dosen')) {
            throw ValidationException::withMessages(['lecturer_id' => 'Dosen yang dipilih harus memiliki role dosen.']);
        }

        DB::transaction(function () use ($validated, $student): void {
            CourseClassLeader::create($validated + ['assigned_at' => now()]);

            Bouncer::assign('ketua_kelas')->to($student);
            Bouncer::refreshFor($student);
        });

        return redirect()->route('class-leaders.index')->with('success', 'Ketua kelas mata kuliah berhasil ditetapkan.');
    }

    public function destroy(CourseClassLeader $classLeader): RedirectResponse
    {
        DB::transaction(function () use ($classLeader): void {
            $student = $classLeader->student;
            $classLeader->delete();

            if ($student && ! CourseClassLeader::where('student_id', $student->id)->exists()) {
                Bouncer::retract('ketua_kelas')->from($student);
                Bouncer::refreshFor($student);
            }
        });

        return redirect()->route('class-leaders.index')->with('success', 'Penetapan ketua kelas berhasil dihapus.');
    }

    private function formData(): array
    {
        $users = User::with('roles')->orderBy('name')->get();

        return [
            'students' => $users->filter(fn (User $user) => $user->roles->contains('name', 'mahasiswa')),
            'lecturers' => $users->filter(fn (User $user) => $user->roles->contains('name', 'dosen')),
            'courses' => Course::orderBy('code')->get(),
            'semesters' => Semester::orderByDesc('is_active')->orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderByDesc('name')->get(),
            'canChooseLecturer' => $this->currentUserIsAdmin(),
        ];
    }

    private function currentUserIsAdmin(): bool
    {
        return (bool) request()->user()?->isAn('admin');
    }
}
