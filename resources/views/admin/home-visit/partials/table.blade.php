@foreach ($siswa as $item)
    <tr>
        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $item->idperson }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $item->nama }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $item->UnitFormal ?? '-' }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $item->KelasFormal ?? '-' }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $item->AsramaPondok ?? '-' }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $item->KamarPondok ?? '-' }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">

            @if ($item->homeVisitsActive())
                <a href="{{ route('admin.home-visit.show', $item->homeVisitsActive()->id) }}"
                    class="bg-orange-500 text-white px-3 py-1 rounded-lg text-xs hover:bg-blue-700">
                    View Home Visit
                </a>
            @else
                <a href="{{ route('admin.home-visit.create', ['siswa_id' => $item->id]) }}"
                    class="bg-primary text-white px-3 py-1 rounded-lg text-xs hover:bg-blue-700">
                    Buat Home Visit
                </a>
            @endif
        </td>
    </tr>
@endforeach
