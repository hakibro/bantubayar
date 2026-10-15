<?php

namespace App\Services;

use App\Exports\SiswaHistoryPembayaranExport;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Menulis file XLSX secara streaming (OpenSpout) agar hemat memori,
 * cocok untuk export berisi ratusan ribu baris.
 */
class XlsxStreamWriter
{
    public function __construct(private SiswaHistoryPembayaranExport $export)
    {
    }

    public static function make(SiswaHistoryPembayaranExport $export): self
    {
        return new self($export);
    }

    public function stream(string $filename): StreamedResponse
    {
        $options = new Options();
        $this->applyColumnWidths($options);

        $headerStyle = (new Style())
            ->withFontBold(true)
            ->withFontColor(Color::WHITE)
            ->withBackgroundColor('4F46E5')
            ->withCellAlignment(CellAlignment::CENTER);

        $writer = new Writer($options);
        $export = $this->export;

        return new StreamedResponse(function () use ($writer, $headerStyle, $export): void {
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValuesWithStyle($export->headings(), $headerStyle));

            foreach ($export->generator() as $row) {
                $writer->addRow(Row::fromValues($this->normalizeRow($row)));
            }

            $writer->close();
        }, 200, $this->headers($filename));
    }

    /**
     * OpenSpout menolak nilai non-skalar; ubah null menjadi string kosong.
     *
     * @param array<int, mixed> $row
     * @return array<int, mixed>
     */
    private function normalizeRow(array $row): array
    {
        return array_map(
            static fn($value) => $value === null ? '' : $value,
            $row
        );
    }

    private function applyColumnWidths(Options $options): void
    {
        // Kolom diindeks dari 1 (A = 1).
        $widths = [
            1 => 12,   // ID Person
            2 => 28,   // Nama
            3 => 18,   // Unit Formal
            4 => 16,   // Kelas Formal
            5 => 16,   // Asrama Pondok
            6 => 14,   // Kamar Pondok
            7 => 14,   // Tingkat Madin
            8 => 14,   // Kelas Madin
            9 => 10,   // Periode
            10 => 16,  // Unit
            11 => 45,  // Kategori
            12 => 16,  // Tagihan
            13 => 16,  // Dibayar
            14 => 16,  // Sisa
            15 => 14,  // Status
        ];

        foreach ($widths as $column => $width) {
            $options->setColumnWidth($width, $column);
        }
    }

    /**
     * @return array<string, string>
     */
    private function headers(string $filename): array
    {
        return [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
            'Pragma' => 'public',
        ];
    }
}
