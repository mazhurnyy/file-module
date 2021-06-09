<?php

namespace Modules\File\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\File\Models\Extension;
use Modules\Setting\Models\TempVal;

class ExtensionsSeeder extends Seeder
{
    /**
     *
     */
    public function run()
    {
        foreach ($this->getDataSeeder() as $item) {
            Extension::updateOrCreate([
                'name' => $item['name']
            ],
                [
                    'note' => $item['note'],
                    'mime' => $item['mime']
                ]);
        }
    }

    /**
     * заполняем таблицу данными
     *
     * @return array
     */
    private function getDataSeeder(): array
    {
        return [
            [
                'name' => 'jpg',
                'note' => 'изображения jpg',
                'mime' => 'image/jpeg',
            ],
            [
                'name' => 'webp',
                'note' => 'изображения webp',
                'mime' => 'image/webp',
            ],
            [
                'name' => 'fb2',
                'note' => 'Тексты книг в формате FB2',
                'mime' => 'application/xml',
            ],
            [
                'name' => 'mp3',
                'note' => 'Книги в формате mp3',
                'mime' => 'audio/mpeg3',
            ],
            [
                'name' => 'pdf',
                'note' => 'Книги в формате PDF',
                'mime' => 'application/pdf',
            ],
            [
                'name' => 'fb2.zip',
                'note' => 'Полный тексты произведения в формате fb2+',
                'mime' => 'application/zip',
            ],
            [
                'name' => 'trial.fb2.zip',
                'note' => 'Ознакомительный фрагмент текста произведения в формате fb2',
                'mime' => 'application/zip',
            ],
            [
                'name' => 'html.zip',
                'note' => 'зипованный html',
                'mime' => 'application/zip',
            ],
            [
                'name' => 'txt.zip',
                'note' => 'зипованный txt',
                'mime' => 'application/zip',
            ],
            [
                'name' => 'rtf.zip',
                'note' => 'зипованный rtf',
                'mime' => 'application/zip',
            ],
            [
                'name' => 'isilo3.pdb',
                'note' => 'iSilo (мультиплатформенная старенькая читалка)',
                'mime' => 'application/xml',
            ],
            [
                'name' => 'doc.prc.zip',
                'note' => 'файл palm doc. Формат читает множество старых и не очень программ',
                'mime' => 'application/zip',
            ],
            [
                'name' => 'lit',
                'note' => 'формат файлов читалки Microsoft Reader',
                'mime' => 'application/xml',
            ],
            [
                'name' => 'rb',
                'note' => 'формат для устройства Rocket eBook и REB1100',
                'mime' => 'application/xml',
            ],
            [
                'name' => 'epub',
                'note' => 'epub, новый перспективный формат электронных книг, разработанный Adobe',
                'mime' => 'application/xml',
            ],
            [
                'name' => 'lrf',
                'note' => 'формат, который понимают Sony Reader-ы',
                'mime' => 'application/xml',
            ],

            [
                'name' => 'mobi.prc',
                'note' => 'файлы для моби-ридера',
                'mime' => 'application/xml',
            ],

            [
                'name' => 'trial.pdf',
                'note' => 'Ознакомительный фрагмент текста произведения в формате pdf',
                'mime' => 'application/pdf',
            ],

            [
                'name' => 'a4.pdf',
                'note' => 'PDF, оптимизированный для печати на A4',
                'mime' => 'application/pdf',
            ],

            [
                'name' => 'a6.pdf',
                'note' => 'PDF, оптимизированный для чтения на eBook',
                'mime' => 'application/pdf',
            ],
            [
                'name' => '128.mp3',
                'note' => 'Стандартное качество. MP3, 128 Kbps.',
                'mime' => 'audio/mpeg3',
            ],
            [
                'name' => 'trial.mp3',
                'note' => 'Ознакомительный фрагмент. Стандартное качество. MP3, 128 Kbps.',
                'mime' => 'audio/mpeg3',
            ],
            [
                'name' => '16.mp3',
                'note' => 'Мобильная версия. MP3, 16 Kbps.',
                'mime' => 'audio/mpeg3',
            ],
            [
                'name' => '16.mp4',
                'note' => 'Мобильная версия. MP4, 16 Kbps.',
                'mime' => 'video/mp4',
            ],
            [
                'name' => '32.mp4',
                'note' => 'Мобильная версия. MP4, 32 Kbps',
                'mime' => 'video/mp4',
            ],
            [
                'name' => '64.mp3',
                'note' => 'Стандартное качество. MP3, 64 Kbps.',
                'mime' => 'audio/mpeg3',
            ],
            [
                'name' => '64.mp4',
                'note' => 'Мобильная версия. MP4, 64 Kbps.',
                'mime' => 'video/mp4',
            ],
            [
                'name' => '192.mp3',
                'note' => 'Стандартное качество. MP3, 192kbps.',
                'mime' => 'audio/mpeg3',
            ],
            [
                'name' => 'mp3.exe',
                'note' => 'Копия оригинального диска. MP3-файлы в самораспаковывающемся RAR-архиве.',
                'mime' => 'application/octet-stream',
            ],
            [
                'name' => '16.mp3.zip',
                'note' => 'Мобильная версия. MP3, 16 Kbps. В архиве для скачивания',
                'mime' => 'application/zip',
            ],
            [
                'name' => '64.mp3.zip',
                'note' => 'Мобильная версия. MP3, 64 Kbps. В архиве для',
                'mime' => 'application/zip',
            ],
            [
                'name' => '128.mp3.zip',
                'note' => 'Мобильная версия. MP3, 128 Kbps. В архиве для скачивания',
                'mime' => 'application/zip',
            ],
            [
                'name' => 'paper',
                'note' => 'бумажные книги, для ссылки',
                'mime' => null,
            ],
            [
                'name' => 'mp3.zip',
                'note' => 'MP3 файлы в zip архиве',
                'mime' => 'application/zip',
            ],
            [
                'name' => 'zip',
                'note' => 'Дополнительные материалы',
                'mime' => 'application/zip',
            ],
            [
                'name' => 'xls',
                'note' => 'Дополнительные материалы',
                'mime' => 'application/excel',
            ],
            [
                'name' => 'content.zip',
                'note' => 'Дополнительные материалы',
                'mime' => 'application/zip',
            ],
            [
                'name' => 'm4b',
                'note' => 'Мобильная версия. MP4, 64 Kbps',
                'mime' => 'application/xml',
            ],
            [
                'name' => 'drm.epub',
                'note' => 'drm защищенный epub',
                'mime' => 'application/xml',
            ],
            [
                'name' => 'html',
                'note' => 'html документ',
                'mime' => 'text/html',
            ],
            [
                'name' => 'ios.epub',
                'note' => 'документы в формате ePub, адаптированные для просмотра на iOS устройствах',
                'mime' => 'application/xml',
            ],
            [
                'name' => 'txt',
                'note' => 'стандартный текстовый файл',
                'mime' => 'application/octet-stream',
            ],
            [
                'name' => 'fb3',
                'note' => 'тексты произведения в формате fb3',
                'mime' => 'application/xml',
            ],
            [
                'name' => 'webp',
                'note' => 'Изображения в формате webp',
                'mime' => 'image/webp',
            ],
        ];
    }

}