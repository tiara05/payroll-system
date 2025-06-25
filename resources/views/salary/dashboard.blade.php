@extends('layouts.app')

@section('header')
    <div class="d-flex justify-content-between align-items-center ">
        <h5 class="mb-0">{{ $title ?? 'Dashboard' }}</h5>
            {{-- Notifikasi --}}
            @php
                $notifCount = Auth::user()->unreadNotifications->count();
            @endphp
            <div class="position-relative">
                <button class="btn btn-link text-decoration-none fs-4" onclick="toggleNotif()">🔔</button>
                @if($notifCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $notifCount }}
                    </span>
                @endif

                {{-- Dropdown Notifikasi --}}
                <div id="notif-list" class="card position-absolute end-0 mt-2 d-none" style="width: 300px; z-index: 999;">
                    <ul class="list-group list-group-flush">
                        @forelse(Auth::user()->unreadNotifications as $notification)
                            <li class="list-group-item small">
                                {{ $notification->data['message'] }}<br>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Tidak ada notifikasi baru</li>
                        @endforelse
                    </ul>
                    @if($notifCount > 0)
                        <form method="POST" action="{{ route('notifications.read') }}" class="p-2 text-end">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">Tandai sudah dibaca</button>
                        </form>
                    @endif
                </div>
            </div>
    </div>
@endsection

@section('content')
<div class="container py-4">
    {{-- Header --}}
    
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        {{-- Filter Status di Kiri --}}
        <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center">
            <label for="status" class="form-label me-2 mb-0">Filter Status:</label>
            <select name="status" id="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="">-- Semua --</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
            </select>
        </form>

        {{-- Tombol Buat Pengajuan di Kanan --}}
        @if(Auth::user()->hasRole('finance'))
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSalaryModal">
                <i class="feather_icon text-primary" data-feather="plus-circle" style="width: 16px; height: 16px;"></i>
                Buat Pengajuan Gaji
            </button>
        @endif
    </div>


    {{-- Tabel --}}
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nama</th>
                    <th>Gaji Pokok</th>
                    <th>Bonus</th>
                    <th>PPH</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salaryRequests as $salary)
                    <tr>
                        <td>{{ $salary->employee->user->name ?? '-' }}</td>
                        <td>{{ number_format($salary->base_salary) }}</td>
                        <td>{{ number_format($salary->bonus) }}</td>
                        <td>{{ number_format($salary->pph) }}</td>
                        <td>{{ number_format($salary->total_salary) }}</td>
                        <td>{{ ucfirst($salary->status) }}</td>
                        <td>
                            @if(Auth::user()->hasRole('manager') && $salary->status == 'pending')
                                <form action="/salary/{{ $salary->id }}/approve" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Setujui</button>
                                </form>
                                <form action="/salary/{{ $salary->id }}/reject" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm">Tolak</button>
                                </form>
                            @endif

                            @if(Auth::user()->hasRole('finance') && $salary->status == 'approved')
                                <form action="/salary/{{ $salary->id }}/pay" method="POST" enctype="multipart/form-data" class="d-inline">
                                    @csrf
                                    <input type="file" name="payment_proof" required class="form-control form-control-sm mb-1">
                                    <button type="submit" class="btn btn-primary btn-sm">Bayar</button>
                                </form>
                            @endif

                            @if($salary->status == 'paid')
                                <a href="{{ asset('storage/' . $salary->payment_proof) }}" target="_blank" class="btn btn-link btn-sm">Lihat Bukti</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada data pengajuan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $salaryRequests->withQueryString()->links() }}
    </div>
</div>

<!-- Modal Form Pengajuan Gaji -->
<div class="modal fade" id="createSalaryModal" tabindex="-1" aria-labelledby="createSalaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
        <form action="{{ route('salary.store') }}" method="POST">
            @csrf
            <div class="modal-header">
            <h5 class="modal-title" id="createSalaryModalLabel">Form Pengajuan Gaji</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="employee_id" class="form-label">Karyawan</label>
                    <select name="employee_id" class="form-select" required>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                    @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="approver_by" class="form-label">Manager</label>
                    <select name="approver_by" class="form-select" required>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                    @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="base_salary" class="form-label">Gaji Pokok</label>
                    <input type="number" name="base_salary" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="bonus" class="form-label">Bonus</label>
                    <input type="number" name="bonus" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Ajukan</button>
            </div>
        </form>
        </div>
    </div>
</div>


<script>
    function toggleNotif() {
        const notif = document.getElementById('notif-list');
        notif.classList.toggle('d-none');
    }
</script>
@endsection
