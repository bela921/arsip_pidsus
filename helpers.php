<?php
// Mengubah nama tahap jadi nama class CSS badge yang sesuai
function kelas_badge_tahap($tahap) {
    $map = [
        'Pra-Penyelidikan'     => 'badge-pra-penyelidikan',
        'Penyelidikan'         => 'badge-penyelidikan',
        'Penyidikan'           => 'badge-penyidikan',
        'Penuntutan-Eksekusi'  => 'badge-penuntutan',
        'Selesai'              => 'badge-selesai',
    ];
    return $map[$tahap] ?? 'badge-pra-penyelidikan';
}
?>