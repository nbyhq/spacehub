{{-- Tombol ubah role. Akun sendiri tidak bisa diubah. --}}
@if ($user->is(auth()->user()))
    <span class="inline-flex h-9 items-center px-3 text-body-sm text-on-surface-variant">Akun Anda</span>
@else
    @php $toAdmin = $user->role !== 'admin'; @endphp
    <form method="POST" action="{{ route('admin.users.role', $user) }}"
          data-confirm-title="{{ $toAdmin ? 'Jadikan admin?' : 'Cabut akses admin?' }}"
          data-confirm="{{ $toAdmin ? $user->name.' akan bisa mengelola ruang, booking, dan pengguna lain.' : $user->name.' tidak akan bisa membuka panel admin lagi.' }}"
          data-confirm-ok="{{ $toAdmin ? 'Jadikan admin' : 'Cabut akses' }}" data-confirm-cancel="Kembali"
          data-confirm-tone="{{ $toAdmin ? 'primary' : 'danger' }}">
        @csrf @method('PATCH')
        <input type="hidden" name="role" value="{{ $toAdmin ? 'admin' : 'user' }}">
        <button class="btn-outline h-9 px-3 {{ $toAdmin ? '' : 'text-red-700' }}">
            <span class="material-symbols-outlined text-[18px]">{{ $toAdmin ? 'admin_panel_settings' : 'person_remove' }}</span>
            {{ $toAdmin ? 'Jadikan admin' : 'Cabut admin' }}
        </button>
    </form>
@endif
