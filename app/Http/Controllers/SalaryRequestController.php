<?php

namespace App\Http\Controllers;

use App\Models\SalaryRequest;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Notifications\SalaryRequestNotification;
use Illuminate\Support\Facades\Log;

class SalaryRequestController extends Controller {

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = SalaryRequest::with('user');

        if ($user->hasRole('finance')) {
            $title = 'Dashboard Finance';
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('manager')) {
            $title = 'Dashboard Manager';
            $query->where('approved_by', $user->id);
        } elseif ($user->hasRole('director')) {
            $title = 'Dashboard Director';
            $query->where('status', 'paid');
        } else {
            abort(403);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $salaryRequests = $query->latest()->paginate(5);

        $employees = Employee::whereHas('user', function ($q) {
            $q->whereDoesntHave('roles'); 
        })->get();

        $managers = User::role('manager')->get();

        return view('salary.dashboard', [
            'salaryRequests' => $salaryRequests,
            'title' => $title,
            'filterStatus' => $request->status,
            'employees' => $employees,
            'managers' => $managers,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'approver_by' => 'required|exists:users,id',
                'base_salary' => 'required|numeric',
                'bonus' => 'nullable|numeric',
            ]);

            $bonus = $request->bonus ?? 0;
            $total = $request->base_salary + $bonus;

            $pph = match(true) {
                $total <= 5000000 => $total * 0.05,
                $total <= 20000000 => $total * 0.10,
                default => $total * 0.15,
            };

            $totalSalary = $total - $pph;

            $salary = SalaryRequest::create([
                'user_id' => auth()->id(),
                'employee_id' => $request->employee_id,
                'approved_by' => $request->approver_by,
                'base_salary' => $request->base_salary,
                'bonus' => $bonus,
                'pph' => $pph,
                'total_salary' => $totalSalary,
                'status' => 'pending',
            ]);

            // Kirim notifikasi ke approver
            $approver = User::findOrFail($request->approver_by);
            $approver->notify(new SalaryRequestNotification("Ada permintaan gaji untuk karyawan ID {$request->employee_id}."));

            return redirect()->route('dashboard')->with('success', 'Pengajuan gaji berhasil!');
        } catch (\Exception $e) {
            return back()->withErrors(['store_error' => 'Terjadi kesalahan saat menyimpan. Silakan coba lagi.'])->withInput();
        }
    }

    public function approve(SalaryRequest $salaryRequest) {
        $salaryRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => Carbon::now(),
        ]);

        $financeUser = $salaryRequest->user;
        $financeUser->notify(new SalaryRequestNotification("Permintaan gaji kamu telah disetujui oleh Manager."));

        return redirect()->back();
    }

    public function reject(SalaryRequest $salaryRequest) {
        $salaryRequest->update(['status' => 'rejected']);
        
        $financeUser = $salaryRequest->user;
        $financeUser->notify(new SalaryRequestNotification("Permintaan gaji kamu telah ditolak oleh Manager."));

        return redirect()->back();
    }

    public function processPayment(Request $request, SalaryRequest $salaryRequest) {
        $request->validate([
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf'
        ]);

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');
        $salaryRequest->update([
            'status' => 'paid',
            'payment_proof' => $path
        ]);

        $directors = \App\Models\User::role('director')->get();
        foreach ($directors as $director) {
            $director->notify(new SalaryRequestNotification("Gaji untuk {$salaryRequest->user->name} telah dibayar dan bukti sudah diunggah."));
        }

        return redirect()->back();
    }
}

