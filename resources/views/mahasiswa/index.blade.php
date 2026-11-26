<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD AJAX RESTful API - Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Manajemen Data Mahasiswa (AJAX + API)</h3>
        <button class="btn btn-primary" id="btnTambah">+ Tambah Data</button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Jurusan</th>
                            <th style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabelMahasiswaBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Create & Update -->
<div class="modal fade" id="modalMahasiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formMahasiswa" class="modal-content" novalidate>
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Mahasiswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mahasiswaId">

                <div class="mb-3">
                    <label for="nim" class="form-label">NIM</label>
                    <input type="text" class="form-control" id="nim" name="nim" placeholder="Contoh: 2310114001">
                    <div class="invalid-feedback" id="error-nim"></div>
                </div>

                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap">
                    <div class="invalid-feedback" id="error-nama"></div>
                </div>

                <div class="mb-3">
                    <label for="jurusan" class="form-label">Jurusan</label>
                    <input type="text" class="form-control" id="jurusan" name="jurusan" placeholder="Contoh: Sistem Informasi">
                    <div class="invalid-feedback" id="error-jurusan"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_URL = '/api/mahasiswa';
const modalEl = document.getElementById('modalMahasiswa');
const modalInstance = new bootstrap.Modal(modalEl);
const formEl = document.getElementById('formMahasiswa');

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function clearValidation() {
    ['nim', 'nama', 'jurusan'].forEach((field) => {
        const input = document.getElementById(field);
        const feedback = document.getElementById(`error-${field}`);
        input.classList.remove('is-invalid');
        feedback.textContent = '';
    });
}

function showValidationErrors(errors) {
    clearValidation();
    if (!errors) return;

    Object.entries(errors).forEach(([field, messages]) => {
        const input = document.getElementById(field);
        const feedback = document.getElementById(`error-${field}`);
        if (input && feedback) {
            input.classList.add('is-invalid');
            feedback.textContent = messages[0];
        }
    });
}

function validateClientSide(payload) {
    const errors = {};
    if (!payload.nim.trim()) {
        errors.nim = ['NIM wajib diisi (validasi klien).'];
    }
    if (!payload.nama.trim()) {
        errors.nama = ['Nama wajib diisi (validasi klien).'];
    }
    if (!payload.jurusan.trim()) {
        errors.jurusan = ['Jurusan wajib diisi (validasi klien).'];
    }
    return errors;
}

async function fetchData() {
    const tbody = document.getElementById('tabelMahasiswaBody');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center">Memuat data...</td></tr>';

    try {
        const res = await fetch(API_URL, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Gagal memuat data.');

        const rows = json.data || [];
        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Data kosong.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map((item, idx) => `
            <tr>
                <td>${idx + 1}</td>
                <td>${escapeHtml(item.nim)}</td>
                <td>${escapeHtml(item.nama)}</td>
                <td>${escapeHtml(item.jurusan)}</td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="openEdit(${item.id})">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(${item.id}, '${escapeHtml(item.nama)}')">Hapus</button>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">${escapeHtml(err.message)}</td></tr>`;
        Swal.fire({ icon: 'error', title: 'Gagal Memuat', text: err.message });
    }
}

document.getElementById('btnTambah').addEventListener('click', () => {
    clearValidation();
    formEl.reset();
    document.getElementById('mahasiswaId').value = '';
    document.getElementById('modalTitle').textContent = 'Tambah Mahasiswa';
    modalInstance.show();
});

async function openEdit(id) {
    clearValidation();
    try {
        const res = await fetch(`${API_URL}/${id}`, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Data tidak ditemukan.');

        document.getElementById('mahasiswaId').value = json.data.id;
        document.getElementById('nim').value = json.data.nim;
        document.getElementById('nama').value = json.data.nama;
        document.getElementById('jurusan').value = json.data.jurusan;
        document.getElementById('modalTitle').textContent = 'Edit Mahasiswa';
        modalInstance.show();
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error', text: err.message });
    }
}

formEl.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearValidation();

    const id = document.getElementById('mahasiswaId').value;
    const payload = {
        nim: document.getElementById('nim').value,
        nama: document.getElementById('nama').value,
        jurusan: document.getElementById('jurusan').value,
    };

    const clientErrors = validateClientSide(payload);
    if (Object.keys(clientErrors).length > 0) {
        showValidationErrors(clientErrors);
        Swal.fire({
            icon: 'warning',
            title: 'Validasi Gagal',
            text: 'Periksa kembali isian formulir Anda.'
        });
        return;
    }

    const url = id ? `${API_URL}/${id}` : API_URL;
    const method = id ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const json = await res.json();

        if (!res.ok) {
            if (res.status === 422 && json.errors) {
                showValidationErrors(json.errors);
            }
            throw new Error(json.message || 'Terjadi kesalahan saat menyimpan.');
        }

        modalInstance.hide();
        await fetchData();
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: json.message,
            timer: 1800,
            showConfirmButton: false
        });
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Menyimpan',
            text: err.message
        });
    }
});

async function confirmDelete(id, nama) {
    const result = await Swal.fire({
        title: 'Hapus Data?',
        text: `Data mahasiswa "${nama}" akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`${API_URL}/${id}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json' }
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Gagal menghapus data.');

        await fetchData();
        Swal.fire({
            icon: 'success',
            title: 'Terhapus',
            text: json.message,
            timer: 1600,
            showConfirmButton: false
        });
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Menghapus',
            text: err.message
        });
    }
}

document.addEventListener('DOMContentLoaded', fetchData);
</script>
</body>
</html>