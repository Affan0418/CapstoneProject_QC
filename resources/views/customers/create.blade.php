@extends('layouts.branch')

@section('title', 'Tambah Pelanggan')

@section('content')
<div class="branch-page">
    <div class="branch-page__header">
        <div>
            <h1 class="branch-page__title">Tambah Pelanggan</h1>
            <p class="branch-page__subtitle">
                Lengkapi data pelanggan untuk masuk ke antrean Quality Control.
            </p>
        </div>

        <a href="{{ route('branch.dashboard') }}" class="branch-btn branch-btn--secondary">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="branch-alert branch-alert--error">
            <strong>Data belum dapat disimpan.</strong>
            Periksa kembali kolom yang ditandai.
        </div>
    @endif

    <form action="{{ route('branch.customers.store') }}" method="POST" class="branch-form">
        @csrf

        <div class="branch-card">
            <div class="branch-card__header">
                <h2>Identitas Layanan</h2>
            </div>

            <div class="branch-form__grid">
                <div class="branch-field">
                    <label for="service_id">Service ID <span class="required">*</span></label>
                    <input
                        type="text"
                        id="service_id"
                        name="service_id"
                        value="{{ old('service_id') }}"
                        class="branch-input @error('service_id') branch-input--error @enderror"
                        required
                    >
                    @error('service_id')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="branch-field">
                    <label for="order_id">Order ID <span class="required">*</span></label>
                    <input
                        type="text"
                        id="order_id"
                        name="order_id"
                        value="{{ old('order_id') }}"
                        class="branch-input @error('order_id') branch-input--error @enderror"
                        required
                    >
                    @error('order_id')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="branch-card">
            <div class="branch-card__header">
                <h2>Data Pelanggan</h2>
            </div>

            <div class="branch-form__grid">
                <div class="branch-field">
                    <label for="nama_pelanggan">Nama Pelanggan <span class="required">*</span></label>
                    <input
                        type="text"
                        id="nama_pelanggan"
                        name="nama_pelanggan"
                        value="{{ old('nama_pelanggan') }}"
                        class="branch-input @error('nama_pelanggan') branch-input--error @enderror"
                        required
                    >
                    @error('nama_pelanggan')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="branch-field">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="branch-input @error('email') branch-input--error @enderror"
                    >
                    @error('email')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="branch-field">
                    <label for="no_hp_utama">No. HP Utama</label>
                    <input
                        type="text"
                        id="no_hp_utama"
                        name="no_hp_utama"
                        value="{{ old('no_hp_utama') }}"
                        class="branch-input @error('no_hp_utama') branch-input--error @enderror"
                    >
                    @error('no_hp_utama')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="branch-field branch-field--full">
                    <label for="alamat_pemasangan">Alamat Pemasangan</label>
                    <textarea
                        id="alamat_pemasangan"
                        name="alamat_pemasangan"
                        rows="3"
                        class="branch-input branch-textarea @error('alamat_pemasangan') branch-input--error @enderror"
                    >{{ old('alamat_pemasangan') }}</textarea>
                    @error('alamat_pemasangan')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="branch-card">
            <div class="branch-card__header">
                <h2>Wilayah dan Penugasan</h2>
            </div>

            <div class="branch-form__grid">
                <div class="branch-field">
                    <label for="agency_id">Agency</label>
                    <select
                        id="agency_id"
                        name="agency_id"
                        class="branch-input branch-select @error('agency_id') branch-input--error @enderror"
                    >
                        <option value="">Pilih Agency</option>

                        @foreach ($agencies as $agency)
                            <option
                                value="{{ $agency->id }}"
                                @selected(old('agency_id') == $agency->id)
                            >
                                {{ $agency->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('agency_id')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="branch-field">
                    <label for="cluster">Cluster</label>
                    <input
                        type="text"
                        id="cluster"
                        name="cluster"
                        value="{{ old('cluster') }}"
                        class="branch-input @error('cluster') branch-input--error @enderror"
                    >
                    @error('cluster')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="branch-field">
                    <label for="city">Kota</label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        value="{{ old('city') }}"
                        class="branch-input @error('city') branch-input--error @enderror"
                    >
                    @error('city')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="branch-field">
                    <label for="sto">STO</label>
                    <input
                        type="text"
                        id="sto"
                        name="sto"
                        value="{{ old('sto') }}"
                        class="branch-input @error('sto') branch-input--error @enderror"
                    >
                    @error('sto')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="branch-field">
                    <label for="team_leader">Team Leader</label>
                    <input
                        type="text"
                        id="team_leader"
                        name="team_leader"
                        value="{{ old('team_leader') }}"
                        class="branch-input @error('team_leader') branch-input--error @enderror"
                    >
                    @error('team_leader')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="branch-card">
            <div class="branch-card__header">
                <h2>Sales</h2>
            </div>

            <div class="branch-form__grid">
                <div class="branch-field">
                    <label for="kode_sf">Kode Sales</label>
                    <input
                        type="text"
                        id="kode_sf"
                        name="kode_sf"
                        value="{{ old('kode_sf') }}"
                        class="branch-input @error('kode_sf') branch-input--error @enderror"
                    >
                    @error('kode_sf')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="branch-field">
                    <label for="nama_sf">Nama Sales</label>
                    <input
                        type="text"
                        id="nama_sf"
                        class="branch-input"
                        value="Otomatis mengikuti Kode Sales"
                        disabled
                    >
                    <p class="branch-field__hint">
                        Nama Sales diisi otomatis oleh sistem berdasarkan Kode Sales.
                    </p>
                </div>
            </div>
        </div>

        <div class="branch-card">
            <div class="branch-card__header">
                <h2>Paket dan Biaya</h2>
            </div>

            <div class="branch-form__grid">
                <div class="branch-field">
                    <label for="paket">Paket</label>
                    <input
                        type="text"
                        id="paket"
                        name="paket"
                        value="{{ old('paket') }}"
                        class="branch-input @error('paket') branch-input--error @enderror"
                    >
                    @error('paket')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="branch-field">
                    <label for="harga_paket">Harga Paket</label>
                    <input
                        type="number"
                        id="harga_paket"
                        name="harga_paket"
                        min="0"
                        step="1"
                        value="{{ old('harga_paket') }}"
                        class="branch-input @error('harga_paket') branch-input--error @enderror"
                    >
                    @error('harga_paket')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="branch-field">
                    <label for="biaya_pasang">Biaya Pemasangan</label>
                    <input
                        type="number"
                        id="biaya_pasang"
                        name="biaya_pasang"
                        min="0"
                        step="1"
                        value="{{ old('biaya_pasang') }}"
                        class="branch-input @error('biaya_pasang') branch-input--error @enderror"
                    >
                    @error('biaya_pasang')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="branch-card">
            <div class="branch-card__header">
                <h2>Tanggal Layanan</h2>
            </div>

            <div class="branch-form__grid">
                <div class="branch-field">
                    <label for="tanggal_ps">Tanggal PS</label>
                    <input
                        type="date"
                        id="tanggal_ps"
                        name="tanggal_ps"
                        value="{{ old('tanggal_ps') }}"
                        class="branch-input @error('tanggal_ps') branch-input--error @enderror"
                    >
                    @error('tanggal_ps')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="branch-field">
                    <label for="tanggal_mulai_bayar_tagihan">Tanggal Mulai Bayar Tagihan</label>
                    <input
                        type="date"
                        id="tanggal_mulai_bayar_tagihan"
                        name="tanggal_mulai_bayar_tagihan"
                        value="{{ old('tanggal_mulai_bayar_tagihan') }}"
                        class="branch-input @error('tanggal_mulai_bayar_tagihan') branch-input--error @enderror"
                    >
                    @error('tanggal_mulai_bayar_tagihan')
                        <p class="branch-field__error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="branch-form__actions">
            <a href="{{ route('branch.dashboard') }}" class="branch-btn branch-btn--secondary">
                Batal
            </a>

            <button type="submit" class="branch-btn branch-btn--primary">
                Simpan Pelanggan
            </button>
        </div>
    </form>
</div>
@endsection