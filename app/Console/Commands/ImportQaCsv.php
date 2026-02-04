<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ImportQaCsv extends Command
{
    protected $signature = 'qa:import {file=storage/app/import/qa.csv} {--dry-run}';
    protected $description = 'Import Q&A from CSV (Ques No, Content, Date, Permalink, Categories, phone, email, answer)';

    public function handle()
    {
        $path = base_path($this->argument('file'));
        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');

        $this->info("CSV: {$path}");
        if ($dry) $this->warn("DRY RUN enabled (DB তে কিছু insert হবে না)");

        $fh = fopen($path, 'r');
        if (!$fh) {
            $this->error("Cannot open CSV.");
            return self::FAILURE;
        }

        $header = fgetcsv($fh);
        if (!$header) {
            $this->error("Empty CSV.");
            return self::FAILURE;
        }

        // ✅ header normalize: case + extra spaces fix
        $header = array_map(function ($h) {
            $h = trim((string)$h);
            $h = preg_replace('/\s+/', ' ', $h);
            return strtolower($h);
        }, $header);

        $rowNum = 1;
        $imported = 0;
        $skipped = 0;

        // speed: cache categories by name_bn => id
        $catCache = DB::table('categories')->pluck('id', 'name_bn')->toArray();

        // admin user id (answers.answered_by) - fallback 1
        $adminId = DB::table('users')->orderBy('id')->value('id'); // might be null, that's OK

        if (!$dry) DB::beginTransaction();

        try {
            while (($row = fgetcsv($fh)) !== false) {
                $rowNum++;

                if (count($row) !== count($header)) {
                    $skipped++;
                    $this->warn("Row {$rowNum} skipped (column mismatch).");
                    continue;
                }

                $data = array_combine($header, $row);

                // ✅ CSV columns (new)
                $quesNo     = trim((string)($data['ques no'] ?? $data['ques_no'] ?? ''));
                $content    = (string)($data['content'] ?? '');
                $dateRaw    = trim((string)($data['date'] ?? ''));
                $permalink  = trim((string)($data['permalink'] ?? ''));
                $catName    = trim((string)($data['categories'] ?? ''));
                $phone      = trim((string)($data['phone'] ?? ''));
                $email      = trim((string)($data['email'] ?? ''));
                $answerText = (string)($data['answer'] ?? '');

                if (trim($content) === '') {
                    $skipped++;
                    $this->warn("Row {$rowNum} skipped (empty content).");
                    continue;
                }

                // ✅ serial: permalink -> number; fallback quesNo
                $serial = $this->extractSerialFromPermalink($permalink);
                if (!$serial && is_numeric($quesNo)) {
                    $serial = (int)$quesNo;
                }

                // ✅ slug
                $slug = $serial ? ('q-' . $serial) : ('q-' . Str::random(8));

                // ✅ date
                $askedAt = $this->parseDate($dateRaw) ?? now();
                if ($askedAt->year < 2000) {
                    $askedAt = now();
                }

                // ✅ content → title + body (same)
                $titleRaw = $this->makeTitleFromContent($content, $serial, $quesNo);

                // ✅ html (remove wrapping <p>...</p> when present)
                $qHtml = $this->toSafeHtml($content);
                $aHtml = $this->toSafeHtml($answerText);

                // ✅ status: answer থাকলে published, না থাকলে pending
                $status = trim($answerText) !== '' ? 'published' : 'pending';

                // ✅ asker info
                $askerEmail = $email !== '' ? $email : null;
                $askerPhone = $phone !== '' ? $phone : 'unknown';
                $askerName  = $this->nameFromEmailOrFallback($askerEmail);

                // ✅ category resolve/create (categories.name_bn)
                $categoryId = null;
                if ($catName !== '') {
                    if (isset($catCache[$catName])) {
                        $categoryId = (int) $catCache[$catName];
                    } else {
                        $baseSlug = Str::slug($catName);
                        $slugCat  = $baseSlug !== '' ? $baseSlug : ('cat-' . Str::random(6));
                        if (DB::table('categories')->where('slug', $slugCat)->exists()) {
                            $slugCat .= '-' . Str::random(4);
                        }

                        if (!$dry) {
                            $categoryId = DB::table('categories')->insertGetId([
                                'name_bn'     => $catName,
                                'slug'        => $slugCat,
                                'description' => null,
                                'sort_order'  => 0,
                                'is_active'   => 1,
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);
                        } else {
                            $categoryId = 999999;
                        }

                        $catCache[$catName] = $categoryId;
                    }
                }

                // ✅ existing check (re-run safe)
                $existingId = null;
                if ($serial) {
                    $existingId = DB::table('questions')->where('published_serial', $serial)->value('id');
                } else {
                    $existingId = DB::table('questions')->where('slug', $slug)->value('id');
                }

                $titleHash = hash('sha256', Str::of($titleRaw)->lower()->squish()->toString());

                if ($existingId) {
                    if (!$dry) {
                        DB::table('questions')->where('id', $existingId)->update([
                            'category_id'   => $categoryId,
                            'title'         => $titleRaw,
                            'body_html'     => $qHtml,
                            'title_bn'      => $titleRaw,
                            'body_html_bn'  => $qHtml,
                            'asker_name'    => $askerName,
                            'asker_phone'   => $askerPhone,
                            'asker_email'   => $askerEmail,
                            'status'        => $status,
                            'published_at'  => $status === 'published' ? $askedAt : null,
                            'title_hash'    => $titleHash,
                            'updated_at'    => now(),
                        ]);
                    }

                    // answer upsert
                    if (trim($answerText) !== '') {
                        $answerId = DB::table('answers')->where('question_id', $existingId)->value('id');

                        if ($answerId) {
                            if (!$dry) {
                                DB::table('answers')->where('id', $answerId)->update([
                                    'answer_html'    => $aHtml,
                                    'answer_html_bn' => $aHtml,
                                    'status'         => 'published',
                                    'answered_by'    => $adminId,
                                    'answered_at'    => $askedAt,
                                    'updated_at'     => now(),
                                ]);
                            }
                        } else {
                            if (!$dry) {
                                DB::table('answers')->insert([
                                    'question_id'    => $existingId,
                                    'answer_html'    => $aHtml,
                                    'answer_html_bn' => $aHtml,
                                    'status'         => 'published',
                                    'answered_by'    => $adminId,
                                    'answered_at'    => $askedAt,
                                    'created_at'     => $askedAt,
                                    'updated_at'     => $askedAt,
                                ]);
                            }
                        }
                    }

                    $imported++;
                    continue;
                }

                // ✅ Insert new question
                if (!$dry) {
                    $questionId = DB::table('questions')->insertGetId([
                        'published_serial' => $serial,
                        'category_id'      => $categoryId,
                        'slug'             => $slug,
                        'original_lang'    => 'bn',

                        'title'            => $titleRaw,
                        'body_html'        => $qHtml,

                        'title_bn'         => $titleRaw,
                        'body_html_bn'     => $qHtml,

                        'asker_name'       => $askerName,
                        'asker_phone'      => $askerPhone,
                        'asker_email'      => $askerEmail,

                        'status'           => $status,
                        'published_at'     => $status === 'published' ? $askedAt : null,
                        'view_count'       => 0,
                        'is_featured'      => 0,

                        'title_hash'       => $titleHash,

                        'created_at'       => $askedAt,
                        'updated_at'       => $askedAt,
                    ]);

                    if (trim($answerText) !== '') {
                        DB::table('answers')->insert([
                            'question_id'    => $questionId,
                            'answered_by'    => $adminId,
                            'answer_html'    => $aHtml,
                            'answer_html_bn' => $aHtml,
                            'status'         => 'published',
                            'answered_at'    => $askedAt,
                            'created_at'     => $askedAt,
                            'updated_at'     => $askedAt,
                        ]);
                    }
                }

                $imported++;
                if ($imported % 300 === 0) {
                    $this->info("Imported: {$imported} rows...");
                }
            }

            if (!$dry) DB::commit();
            fclose($fh);

            $this->info("✅ DONE. Imported={$imported}, Skipped={$skipped}");
            return self::SUCCESS;

        } catch (\Throwable $e) {
            if (!$dry) DB::rollBack();
            fclose($fh);
            $this->error("❌ Failed at row {$rowNum}: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function extractSerialFromPermalink(string $url): ?int
    {
        $url = trim($url);
        if ($url === '') return null;

        if (preg_match('~/(\\d+)/?$~', $url, $m)) {
            return (int) $m[1];
        }
        return null;
    }

    private function parseDate($raw): ?Carbon
    {
        $raw = trim((string)$raw);
        if ($raw === '') return null;

        // time only -> ignore
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?(\s?[APMapm]{2})?$/', $raw)) {
            return null;
        }

        // numeric (excel serial / unix timestamp)
        if (is_numeric($raw)) {
            $num = (float)$raw;

            // excel serial date
            if ($num > 20000 && $num < 60000) {
                return Carbon::create(1899, 12, 30, 0, 0, 0, config('app.timezone', 'Asia/Dhaka'))
                    ->addDays((int)$num);
            }

            // unix timestamp seconds
            if ($num >= 1000000000 && $num <= 2000000000) {
                return Carbon::createFromTimestamp((int)$num, config('app.timezone', 'Asia/Dhaka'));
            }

            return null;
        }

        $tz = config('app.timezone', 'Asia/Dhaka');
        $formats = ['Y-m-d', 'Y-m-d H:i:s', 'Y-m-d H:i', 'd/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y h:i A'];

        foreach ($formats as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $raw, $tz);
                if ($dt->year < 2000) return null;
                return $dt;
            } catch (\Throwable $e) {}
        }

        try {
            $dt = Carbon::parse($raw, $tz);
            if ($dt->year < 2000) return null;
            return $dt;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function makeTitleFromContent(string $content, ?int $serial, string $quesNo): string
    {
        $plain = trim(strip_tags($content));
        $plain = preg_replace('/\s+/', ' ', $plain);

        if ($plain === '') {
            $n = $serial ?: (is_numeric($quesNo) ? (int)$quesNo : null);
            return 'প্রশ্ন #' . ($n ?: 'N/A');
        }

        // title max 190 chars
        if (mb_strlen($plain) > 190) {
            $plain = mb_substr($plain, 0, 190) . '…';
        }

        return $plain;
    }

    private function nameFromEmailOrFallback(?string $email): string
    {
        if (!$email) return 'Anonymous';

        $email = trim($email);
        if ($email === '' || !str_contains($email, '@')) return 'Anonymous';

        $local = explode('@', $email)[0] ?? '';
        $local = trim($local);
        if ($local === '') return 'Anonymous';

        $local = str_replace(['.', '_', '-', '+'], ' ', $local);
        $local = preg_replace('/\s+/', ' ', $local);

        $local = ucwords(strtolower($local));
        if (mb_strlen($local) < 2) return 'Anonymous';

        return $local;
    }

    private function toSafeHtml(string $text): string
    {
        $text = (string)$text;
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = trim($text);

        if ($text === '') return '';

        // ✅ যদি Excel/CSV থেকে already HTML আসে, অনেক সময় পুরোটা <p>...</p> দিয়ে wrap থাকে।
        // সেই একদম বাইরের এক জোড়া <p>...</p> থাকলে remove করে দিচ্ছি।
        $text = $this->stripOuterPTags($text);

        // If looks like HTML already, keep it (but still strip outer <p> done above)
        if ($this->looksLikeHtml($text)) {
            return $text;
        }

        // Otherwise: escape + nl2br
        $escaped = e($text);
        return nl2br($escaped);
    }

    private function looksLikeHtml(string $text): bool
    {
        return $text !== strip_tags($text);
    }

    private function stripOuterPTags(string $html): string
    {
        $h = trim($html);

        // remove BOM / weird spaces
        $h = preg_replace('/^\xEF\xBB\xBF/', '', $h);
        $h = trim($h);

        // only remove ONE outer <p> wrapper if the entire string is wrapped
        // supports: <p>...</p> and <p class="...">...</p>
        if (preg_match('/^\s*<p\b[^>]*>(.*)<\/p>\s*$/is', $h, $m)) {
            $inside = trim($m[1]);

            // if inside contains multiple top-level <p> tags, we should NOT unwrap
            // but if it's just a wrapper, unwrap safely
            return $inside;
        }

        return $h;
    }
}
