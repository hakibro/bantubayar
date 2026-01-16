<button id="btnTest" class="px-4 py-2 bg-blue-600 text-white rounded">
    Test API
</button>

<script>
    document.getElementById('btnTest').addEventListener('click', async () => {
        const res = await fetch('{{ route('api.phone') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await res.json();
        console.log(data);
        alert('Selesai, cek console');
    });
</script>
