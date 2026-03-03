<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Surat Tugas Home Visit</title>
    <style>
        body {
            font-family: sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header h3 {
            font-size: 18px;
            color: #555;
        }

        .content {
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 8px;
            vertical-align: top;
        }

        .label {
            width: 30%;
            font-weight: bold;
        }

        .signature {
            margin-top: 50px;
            text-align: right;
        }

        .signature div {
            margin-top: 60px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>SURAT TUGAS HOME VISIT</h1>
        <h3>YAYASAN PONDOK PESANTREN DARUT TAQWA</h3>
        <p>Nomor: ............./ST/HV/{{ date('Y') }}</p>
    </div>

    <div class="content">
        <table>
            <tr>
                <td class="label">Nama Siswa</td>
                <td>: {{ $homeVisit->siswa->nama }}</td>
            </tr>
            <tr>
                <td>ID Person</td>
                <td>: {{ $homeVisit->siswa->idperson }}</td>
            </tr>
            <tr>
                <td>Lembaga</td>
                <td>: {{ $homeVisit->siswa->UnitFormal ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td>: {{ $homeVisit->siswa->KelasFormal ?? '-' }}</td>
            </tr>
            <tr>
                <td>Asrama</td>
                <td>: {{ $homeVisit->siswa->AsramaPondok ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kamar</td>
                <td>: {{ $homeVisit->siswa->KamarPondok ?? '-' }}</td>
            </tr>
            <tr>
                <td>No. HP Wali</td>
                <td>: {{ $homeVisit->siswa->phone ?? '-' }}</td>
            </tr>
            <tr>
                <td>Total Tunggakan</td>
                <td>: Rp {{ number_format($homeVisit->siswa->getTotalTunggakan(), 0, ',', '.') }}</td>
            </tr>
        </table>

        <hr style="margin: 30px 0;">

        <table>
            <tr>
                <td class="label">Nama Petugas Visit</td>
                <td>: {{ $homeVisit->petugas_nama }}</td>
            </tr>
            <tr>
                <td>No. HP Petugas</td>
                <td>: {{ $homeVisit->petugas_hp }}</td>
            </tr>
            <tr>
                <td>Tanggal Visit</td>
                <td>:
                    {{ $homeVisit->tanggal_visit ? \Carbon\Carbon::parse($homeVisit->tanggal_visit)->format('d/m/Y') : '-' }}
                </td>
            </tr>
        </table>

        <p style="margin-top: 30px;">Dengan ini menugaskan petugas tersebut untuk melakukan kunjungan (home visit) kepada
            wali siswa dalam rangka penanganan tunggakan. Petugas diharapkan membuat laporan hasil kunjungan melalui
            tautan yang telah diberikan.</p>
    </div>

    <div class="signature">
        <p>..............., {{ date('d F Y') }}</p>
        <p>Kepala Bagian Keuangan</p>
        <div>_________________________</div>
    </div>
</body>

</html>
