<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terima Kasih</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg text-center">
        <div class="text-green-500 text-6xl mb-4">✓</div>
        <h1 class="text-2xl font-bold mb-2">Laporan Terkirim!</h1>
        <p class="text-gray-600">Terima kasih, laporan home visit Anda sudah kami terima.</p>
        <a href="{{ url('/') }}" class="mt-6 inline-block bg-primary text-white px-6 py-2 rounded-lg">Kembali</a>
    </div>
</body>

</html>
