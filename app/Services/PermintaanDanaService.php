<?php

namespace App\Services;

use App\Actions\ActivityLog\RecordActivity;
use App\Enums\PdDocumentKategori;
use App\Enums\PDStatus;
use App\Helpers\FileCompressionHelper;
use App\Models\PermintaanDana;
use App\Models\User;
use App\Notifications\PdApprovedNotification;
use App\Notifications\PdRejectedNotification;
use App\Notifications\PdSubmittedNotification;
use App\Support\NotificationHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PermintaanDanaService
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly PermintaanDanaPDFService $permintaanDanaPDFService,
        private readonly RecordActivity $recordActivity,
        private readonly NotificationHelper $notificationHelper,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'] ?? null, ['no_pd', 'tgl_pd', 'kategori', 'nominal', 'status', 'created_at'], true)
            ? $filters['sort']
            : 'created_at';

        return PermintaanDana::query()
            ->with(['createdBy:id,name'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('no_pd', 'like', "%{$search}%")
                        ->orWhere('referensi_dokumen', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%");
                });
            })
            ->when($filters['kategori'] ?? null, fn ($query, string $kategori) => $query->where('kategori', $kategori))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('tgl_pd', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('tgl_pd', '<=', $date))
            ->orderBy($sort, ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    public function create(array $data, User $user): PermintaanDana
    {
        $totalNominal = collect($data['items'] ?? [])->sum(fn ($item): float => ($item['qty'] ?? 0) * ($item['harga'] ?? 0));

        return DB::transaction(function () use ($data, $user): PermintaanDana {
            $date = Carbon::parse($data['tgl_pd']);
            $permintaanDana = PermintaanDana::create([
                'no_pd' => $this->documentNumberService->generatePermintaanDanaNumber($date),
                'tgl_pd' => $date,
                'tujuan' => $data['tujuan'],
                'rekening_tujuan' => $data['rekening_tujuan'],
                'bank_tujuan' => $data['bank_tujuan'] ?? null,
                'plan_pembayaran' => Carbon::parse($data['plan_pembayaran']),
                'nominal' => $totalNominal,
                'keterangan' => $data['keterangan'],
                'referensi_dokumen' => $data['referensi_dokumen'] ?? null,
                'status' => PDStatus::Draft,
                'created_by' => $user->id,
            ]);

            foreach ($data['items'] as $item) {
                $permintaanDana->items()->create([
                    'no_po' => $item['no_po'] ?? null,
                    'no_part' => $item['no_part'] ?? null,
                    'description' => $item['description'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                    'total' => ($item['qty'] * $item['harga']),
                ]);
            }

            if (! empty($data['attachments'])) {
                $tipeLabels = ['NOTA', 'FOTO_BARANG'];

                foreach ($data['attachments'] as $index => $file) {
                    if (! $file instanceof UploadedFile) {
                        continue;
                    }

                    $path = $file->store('pd-attachments', 'local');
                    FileCompressionHelper::compress(Storage::disk('local')->path($path));

                    $permintaanDana->documents()->create([
                        'tipe' => $tipeLabels[$index] ?? 'NOTA',
                        'kategori' => PdDocumentKategori::BuktiTransfer,
                        'file_path' => $path,
                        'nama_file' => str($file->getClientOriginalName())->limit(100, '')->toString(),
                    ]);
                }
            }

            $this->recordActivity->handle('created_pd', $permintaanDana, "{$user->name} membuat Permintaan Dana {$permintaanDana->no_pd}");

            if ($data['submit'] ?? false) {
                return $this->submit($permintaanDana, $user);
            }

            return $permintaanDana->refresh()->load(['createdBy', 'items', 'documents']);
        });
    }

    public function submit(PermintaanDana $permintaanDana, User $user): PermintaanDana
    {
        $this->ensureStatus($permintaanDana, PDStatus::Draft, 'Permintaan Dana hanya bisa disubmit dari status Draft.');

        $permintaanDana->update([
            'status' => PDStatus::PendingApproval,
            'submitted_at' => now(),
        ]);
        $this->recordActivity->handle('submitted_pd', $permintaanDana, "{$user->name} submit Permintaan Dana {$permintaanDana->no_pd}");
        $this->notificationHelper->getUsersByRole('Manager')->each->notify(new PdSubmittedNotification($permintaanDana));

        // Send approval email
        NotificationService::sendApprovalEmail($permintaanDana, 'permintaan_dana');

        return $permintaanDana->refresh();
    }

    public function approve(PermintaanDana $permintaanDana, User $user): PermintaanDana
    {
        $this->ensureStatus($permintaanDana, PDStatus::PendingApproval, 'Permintaan Dana hanya bisa diapprove dari status Pending Approval.');

        return DB::transaction(function () use ($permintaanDana, $user): PermintaanDana {
            $permintaanDana->update([
                'status' => PDStatus::Approved,
                'qr_token' => Str::random(64),
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            $this->permintaanDanaPDFService->generate($permintaanDana->refresh());
            $this->recordActivity->handle('approved_pd', $permintaanDana, "{$user->name} approve Permintaan Dana {$permintaanDana->no_pd}");
            $this->notificationHelper->getUsersByRole('Procurement')->each->notify(new PdApprovedNotification($permintaanDana));

            if ($permintaanDana->kategori === KategoriPD::BiayaPengiriman) {
                $this->notificationHelper->getUsersByRole('Sales')->each->notify(new PdApprovedNotification($permintaanDana));
            }

            if ($permintaanDana->kategori === KategoriPD::BayarRma) {
                $this->notificationHelper->getUsersByRole('Finance')->each->notify(new PdApprovedNotification($permintaanDana));
            }

            return $permintaanDana->refresh();
        });
    }

    public function reject(PermintaanDana $permintaanDana, string $catatan, User $user): PermintaanDana
    {
        $this->ensureStatus($permintaanDana, PDStatus::PendingApproval, 'Permintaan Dana hanya bisa direject dari status Pending Approval.');

        $permintaanDana->update([
            'status' => PDStatus::Rejected,
            'catatan_rejection' => $catatan,
        ]);
        $this->recordActivity->handle('rejected_pd', $permintaanDana, "{$user->name} reject Permintaan Dana {$permintaanDana->no_pd}");
        $permintaanDana->createdBy?->notify(new PdRejectedNotification($permintaanDana));

        return $permintaanDana->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function uploadBukti(PermintaanDana $permintaanDana, array $data, User $user): PermintaanDana
    {
        $this->ensureStatus($permintaanDana, PDStatus::Approved, 'Bukti hanya bisa diupload untuk Permintaan Dana yang sudah Approved.');

        return DB::transaction(function () use ($permintaanDana, $data, $user): PermintaanDana {
            foreach ($data['documents'] as $index => $document) {
                $file = $document['file'] ?? null;

                if (! $file instanceof UploadedFile) {
                    throw ValidationException::withMessages(["documents.{$index}.file" => 'File bukti tidak valid.']);
                }

                $path = $this->storeCompressedFile($file, 'pd-bukti');

                $permintaanDana->documents()->create([
                    'kategori' => $document['kategori'],
                    'file_path' => $path,
                    'nama_file' => str($file->getClientOriginalName())->limit(100, '')->toString(),
                ]);
            }

            $permintaanDana->update([
                'status' => PDStatus::Paid,
                'tgl_realisasi' => $data['tgl_realisasi'],
                'jumlah_realisasi' => $data['jumlah_realisasi'],
            ]);
            $this->recordActivity->handle('paid_pd', $permintaanDana, "{$user->name} upload bukti pembayaran Permintaan Dana {$permintaanDana->no_pd}");

            return $permintaanDana->refresh()->load('documents');
        });
    }

    public function void(PermintaanDana $permintaanDana, string $alasan, User $user): PermintaanDana
    {
        if (! $permintaanDana->isVoidable()) {
            throw ValidationException::withMessages(['status' => 'Permintaan Dana tidak bisa divoid karena sudah Paid atau Void.']);
        }

        $permintaanDana->update([
            'status' => PDStatus::Void,
            'voided_by' => $user->id,
            'voided_at' => now(),
            'alasan_void' => $alasan,
        ]);
        $this->recordActivity->handle('voided_pd', $permintaanDana, "{$user->name} void Permintaan Dana {$permintaanDana->no_pd}");

        return $permintaanDana->refresh();
    }

    private function ensureStatus(PermintaanDana $permintaanDana, PDStatus $status, string $message): void
    {
        if ($permintaanDana->status !== $status) {
            throw ValidationException::withMessages(['status' => $message]);
        }
    }

    private function storeCompressedFile(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'local');
        FileCompressionHelper::compress(Storage::disk('local')->path($path));

        return $path;
    }
}
